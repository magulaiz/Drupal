<?php

declare(strict_types = 1);

namespace Drupal\ckeditor5\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Core\Config\Entity\ConfigEntityTypeInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for entity link suggestions autocomplete route.
 *
 * @internal
 */
class EntityLinkSuggestionsController implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * Suggestions are made for the host entity type + common reference targets.
   *
   * Not every entity type that has `common_reference_target = TRUE` makes sense
   * in a "text field" context though.
   *
   * @var string[]
   */
  const IGNORED_COMMON_REFERENCE_TARGETS = [
    // Media should not be linked, but embedded.
    // @see `media_media` CKEditor 5 plugin
    'media',
    // Content very rarely needs to link to users.
    'user',
  ];

  /**
   * The default limit for matches.
   */
  const DEFAULT_LIMIT = 100;

  /**
   * Constructs a EntityLinkSuggestionsController.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entityTypeBundleInfo
   *   The entity type bundle info.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   The entity repository.
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The current user.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   *   The date formatter service.
   */
  public function __construct(
    protected readonly Connection $database,
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly EntityTypeBundleInfoInterface $entityTypeBundleInfo,
    protected readonly EntityRepositoryInterface $entityRepository,
    protected readonly AccountInterface $currentUser,
    protected readonly DateFormatterInterface $dateFormatter,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('entity.repository'),
      $container->get('current_user'),
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
      $host_entity_type_is_linkable = $this->entityTypeManager->getDefinition($host_entity_type_id)
        ->hasLinkTemplate('canonical');
      if ($host_entity_type_is_linkable) {
        $suggestions = $this->getSuggestions($host_entity_type_id, $input);
      }

      // Second, find suggestions for all other entity types that are common
      // reference targets (that aren't ignored).
      foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
        if ($host_entity_type_id === $entity_type_id) {
          continue;
        }
        if (in_array($entity_type_id, self::IGNORED_COMMON_REFERENCE_TARGETS, TRUE)) {
          continue;
        }
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
    $suggestions = [];
    $query = $this->buildEntityQuery($target_entity_type_id, $string);
    $query->accessCheck(TRUE);
    $query_result = $query->execute();
    $url_results = self::findEntityIdByUrl($target_entity_type_id, $string);
    $result = array_merge($query_result, $url_results);

    // If no results, return an empty suggestion collection.
    if (empty($result)) {
      return $suggestions;
    }

    $entities = $this->entityTypeManager->getStorage($target_entity_type_id)->loadMultiple($result);

    foreach ($entities as $entity) {
      // Check the access against the defined entity access handler.
      /** @var \Drupal\Core\Access\AccessResultInterface $access */
      $access = $entity->access('view', $this->currentUser, TRUE);

      if (!$access->isAllowed()) {
        continue;
      }

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
      'path' => $entity->toUrl('canonical', ['path_processing' => FALSE])
        ->toString(TRUE)
        ->getGeneratedUrl(),
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
   * Builds an EntityQuery to get entities.
   *
   * @param string $target_entity_type_id
   *   An entity type to get suggestions for.
   * @param string $search_string
   *   Text to match the label against.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   The EntityQuery object with the basic conditions and sorting applied to
   *   it.
   */
  protected function buildEntityQuery(string $target_entity_type_id, string $search_string) {
    $search_string = $this->database->escapeLike($search_string);

    $entity_type = $this->entityTypeManager->getDefinition($target_entity_type_id);
    $query = $this->entityTypeManager->getStorage($target_entity_type_id)->getQuery();
    $query->accessCheck(TRUE);
    $label_key = $entity_type->getKey('label');

    if ($label_key) {
      // For configuration entities, the condition needs to be CONTAINS as
      // the matcher does not support LIKE.
      if ($entity_type instanceof ConfigEntityTypeInterface) {
        $query->condition($label_key, $search_string, 'CONTAINS');
      }
      else {
        $query->condition($label_key, '%' . $search_string . '%', 'LIKE');
      }

      $query->sort($label_key, 'ASC');
    }

    $query->range(0, self::DEFAULT_LIMIT);

    // Add tags to let other modules alter the query.
    $query->addTag('entity_link_suggestions_autocomplete');
    $query->addTag('entity_link_suggestions_autocomplete_' . $target_entity_type_id . '_autocomplete');

    // Add access tag for the query.
    $query->addTag('entity_access');
    $query->addTag($target_entity_type_id . '_access');

    return $query;
  }

  /**
   * Finds entity ID from the given input.
   *
   * @param string $target_entity_type_id
   *   An entity type to get suggestions for.
   * @param string $user_input
   *   The string to url parse.
   *
   * @return array
   *   An array with an entity ID if the input can be parsed as an internal url
   *   and a match is found, otherwise an empty array.
   */
  protected static function findEntityIdByUrl(string $target_entity_type_id, string $user_input): array {
    $result = [];

    try {
      $params = Url::fromUserInput($user_input)->getRouteParameters();
      if (key($params) === $target_entity_type_id) {
        $result = [end($params)];
      }
    }
    catch (\Exception $e) {
      // Do nothing.
    }

    return $result;
  }

}
