<?php

declare(strict_types = 1);

namespace Drupal\ckeditor5\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for entity link suggestions autocomplete route.
 *
 * @see \Drupal\Core\Entity\EntityReferenceSelection\SelectionInterface
 * @see \Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection
 *
 * @internal
 */
class EntityLinkSuggestionsController implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The default limit for matches.
   */
  const DEFAULT_LIMIT = 100;

  /**
   * Constructs a EntityLinkSuggestionsController.
   *
   * @param \Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface $selectionPluginManager
   *   The entity reference selection plugin manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entityTypeBundleInfo
   *   The entity type bundle info.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   The entity repository.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   *   The date formatter service.
   */
  public function __construct(
    protected readonly SelectionPluginManagerInterface $selectionPluginManager,
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly EntityTypeBundleInfoInterface $entityTypeBundleInfo,
    protected readonly EntityRepositoryInterface $entityRepository,
    protected readonly DateFormatterInterface $dateFormatter,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get(SelectionPluginManagerInterface::class),
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('entity.repository'),
      $container->get('date.formatter')
    );
  }

  /**
   * Generates entity link suggestions for use by an autocomplete.
   *
   * Like other autocomplete functions, this function inspects the 'q' query
   * parameter for the string to use to search for suggestions.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param string $host_entity_type_id
   *   The host entity type ID.
   * @param string $host_entity_langcode
   *   The host entity's langcode.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing the autocomplete suggestions.
   */
  public function suggestions(Request $request, string $host_entity_type_id, string $host_entity_langcode) {
    $input = mb_strtolower($request->query->get('q'));
    $suggestions = [];

    if ($input) {
      // First, find suggestions for the host entity type.
      $entity_type = $this->entityTypeManager->getDefinition($host_entity_type_id);
      $host_entity_type_is_linkable = $entity_type->hasLinkTemplate('canonical') || $entity_type->hasHandlerClass('link_target');
      if ($host_entity_type_is_linkable) {
        $suggestions = $this->getSuggestions($host_entity_type_id, $input);
      }

      // Second, find suggestions for all other entity types that are common
      // reference targets (that aren't ignored).
      foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
        if ($host_entity_type_id === $entity_type_id) {
          continue;
        }
        // @todo Move this to filter configuration?
        if ($entity_type->isCommonReferenceTarget()) {
          $suggestions = array_merge($suggestions, $this->getSuggestions($entity_type_id, $input));
        }
      }

      // If no suggestions were found, add a special suggestion that has the
      // same path as the given string so users can select it and use it anyway.
      // This typically occurs when entering external links.
      if (empty($suggestions) && mb_strlen($input) > 0) {
        $suggestions = [
          [
            'description' => $this->t('No content suggestions found. This URL will be used as is.'),
            'group' => $this->t('No results'),
            'label' => Html::escape($input),
            // @todo rename `path` to `href`?
            'path' => $input,
          ],
        ];
      }
    }

    // Note that we intentionally:
    // - do not use \Drupal\Core\Cache\CacheableJsonResponse because caching it
    //   on the server side is wasteful, hence there is no need for cacheability
    //   metadata.
    // - mark the response as private, because the suggestions include only the
    //   ones accessible by the current user
    return (new JsonResponse(['suggestions' => $suggestions]))
      // Do not allow any intermediary to cache the response, only the end user.
      ->setPrivate()
      // Allow the end user to cache it for up to 5 minutes.
      ->setMaxAge(300);
  }

  /**
   * Gets the suggestions.
   *
   * @param string $target_entity_type_id
   *   An entity type to get suggestions for.
   * @param string $string
   *   The string to search.
   */
  public function getSuggestions(string $target_entity_type_id, string $string) {
    // If the user input is a current entity URL, don't get more suggestions.
    if ($entity_id = static::findEntityIdByUrl($target_entity_type_id, $string)) {
      $entity = $this->entityTypeManager->getStorage($target_entity_type_id)->load($entity_id);
      return [$this->createSuggestion($entity)];
    }

    // Do not call ::getPluginId() or ::getInstance() because this favors a
    // "link_target" variant of the default selection plugin for the given
    // entity type, if it exists.
    $selection_handler_groups = $this->selectionPluginManager->getSelectionGroups($target_entity_type_id);
    if (!array_key_exists('default', $selection_handler_groups)) {
      return [];
    }
    // Sort the selection plugins by weight and select the best match.
    uasort($selection_handler_groups['default'], ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
    end($selection_handler_groups['default']);
    // Select the link_target variant of the default selection plugin for the
    // entity type, if it exists. Otherwise, select the next best match.
    $link_target_selection_plugin_id = "default:${target_entity_type_id}_link_target";
    $plugin_id = array_key_exists($link_target_selection_plugin_id, $selection_handler_groups['default'])
      ? $link_target_selection_plugin_id
      : key($selection_handler_groups['default']);
    $selection = $this->selectionPluginManager->createInstance($plugin_id, ['target_type' => $target_entity_type_id]);

    $entities_by_bundle = $selection->getReferenceableEntities($string, 'CONTAINS', static::DEFAULT_LIMIT);
    // DefaultSelection::getReferenceableEntities() loads entities and even
    // their translation but then only keeps bundle, entity ID and label. Reload
    // them to generate rich results. Note that performance overhead of this is
    // minimal because all this data is statically cached already anyway.
    $entity_ids = array_reduce($entities_by_bundle, function ($flattened, $bundle_entities) {
      return array_merge($flattened, array_keys($bundle_entities));
    }, []);
    $entities = $this->entityTypeManager->getStorage($target_entity_type_id)->loadMultiple($entity_ids);

    $suggestions = [];
    foreach ($entities as $entity) {
      $entity = $this->entityRepository->getTranslationFromContext($entity);
      $suggestions[] = $this->createSuggestion($entity);
    }

    return $suggestions;
  }

  /**
   * Creates a suggestion.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The matched entity.
   *
   * @return array
   *   A suggestion object with populated entity data.
   */
  protected function createSuggestion(EntityInterface $entity): array {
    return [
      'description' => $this->computeDescription($entity) ?? '',
      'entity_type_id' => $entity->getEntityTypeId(),
      'entity_uuid' => $entity->uuid(),
      'group' => $this->computeGroup($entity),
      'label' => Html::escape($entity->label()),
      // Generate an entity URI because the link target may very well not be the
      // canonical URI anyway. The filter will transform this to the final URL.
      // @see \Drupal\Core\Url::fromEntityUri()
      // @see \Drupal\Core\Entity\EntityLinkTargetInterface()
      // @see \Drupal\filter\Plugin\Filter\EntityLinks
      'path' => sprintf('entity:%s/%s', $entity->getEntityTypeId(), $entity->id()),
    ];
  }

  /**
   * Computes a suggestion description.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The suggested entity for which to compute a description.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|null
   *   A suggestion description.
   */
  protected function computeDescription(EntityInterface $entity): ?TranslatableMarkup {
    $entity_type = $entity->getEntityType();
    $owner = $entity_type->hasKey('owner')
      ? $entity->getOwner()->getDisplayName()
      : NULL;
    $creation_datetime = method_exists($entity, 'getCreatedTime')
      ? $this->dateFormatter->format($entity->getCreatedTime(), 'medium')
      : NULL;

    $args = [
      ':owner' => $owner,
      ':creation-datetime' => $creation_datetime,
    ];

    if ($owner && $creation_datetime) {
      return $this->t('by :owner on :creation-datetime', $args);
    }
    elseif ($owner) {
      return $this->t('by :owner', $args);
    }
    elseif ($creation_datetime) {
      return $this->t('on :creation-datetime', $args);
    }
    else {
      return NULL;
    }
  }

  /**
   * Computers a suggestion group.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The suggested entity for which to compute the group.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   A suggestion group.
   */
  protected function computeGroup(EntityInterface $entity): TranslatableMarkup {
    $args = [
      ':entity-type-label' => $entity->getEntityType()->getLabel(),
    ];

    // If the entity type does not have bundles, the group is very simple.
    if ($entity->getEntityType()->getBundleEntityType() === NULL) {
      return $this->t(':entity-type-label', $args);
    }

    $bundles = $this->entityTypeBundleInfo->getBundleInfo($entity->getEntityTypeId());
    $args[':bundle-label'] = $bundles[$entity->bundle()]['label'];

    return $this->t(':entity-type-label - :bundle-label', $args);
  }

  /**
   * Finds entity ID from the given input.
   *
   * @param string $target_entity_type_id
   *   An entity type to get suggestions for.
   * @param string $user_input
   *   The string to url parse.
   *
   * @return string|null
   *   An entity ID parsed from the user input, otherwise NULL.
   */
  protected static function findEntityIdByUrl(string $target_entity_type_id, string $user_input): ?string {
    $expected_url_prefix = "entity:$target_entity_type_id/";
    if (str_starts_with($user_input, $expected_url_prefix)) {
      return substr($user_input, strlen($expected_url_prefix));
    }
    return NULL;
  }

}
