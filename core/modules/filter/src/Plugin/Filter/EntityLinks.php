<?php

declare(strict_types = 1);

namespace Drupal\filter\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\GeneratedUrl;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\TypedData\TranslatableInterface;
use Drupal\Core\Utility\Error;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the Entity Links filter.
 *
 * @Filter(
 *   id = "entity_links",
 *   title = @Translation("Entity Links"),
 *   description = @Translation("Updates entity links with <code>data-entity-type</code> and <code>data-entity-type-uuid</code> attributes to point to the latest entity URL aliases."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE
 * )
 */
class EntityLinks extends FilterBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs a EntityLinks object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   The entity repository service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The filter logger.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected readonly EntityRepositoryInterface $entityRepository,
    protected readonly LoggerInterface $logger
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.repository'),
      $container->get('logger.channel.filter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);

    if (strpos($text, 'data-entity-type') !== FALSE && strpos($text, 'data-entity-uuid') !== FALSE) {
      $dom = Html::load($text);
      $xpath = new \DOMXPath($dom);

      // Note: this filter only processes links (<a href>) to Files, not
      // tags with a File as a source (e.g. <img src>).
      // @see \Drupal\editor\Plugin\Filter\EditorFileReference
      foreach ($xpath->query('//a[@data-entity-type and @data-entity-uuid]') as $element) {
        /** @var \DOMElement $element */
        try {
          // Load the appropriate translation of the linked entity.
          $entity_type = $element->getAttribute('data-entity-type');
          $uuid = $element->getAttribute('data-entity-uuid');

          // Skip empty attributes to prevent loading of non-existing
          // content type.
          if ($entity_type === '' || $uuid === '') {
            continue;
          }

          $entity = $this->entityRepository->loadEntityByUuid($entity_type, $uuid);
          if ($entity) {
            // @todo Consider using \Drupal\Core\Entity\EntityRepositoryInterface::getTranslationFromContext() after https://drupal.org/i/3061761 is fixed.
            if ($entity instanceof TranslatableInterface && $entity->hasTranslation($langcode)) {
              $entity = $entity->getTranslation($langcode);
            }

            $url = $this->getUrl($entity, $element->hasAttribute('download'));

            // Parse link href as URL, extract query and fragment from it.
            $href_url = parse_url($element->getAttribute('href'));
            $anchor = empty($href_url["fragment"]) ? '' : '#' . $href_url["fragment"];
            $query = empty($href_url["query"]) ? '' : '?' . $href_url["query"];

            $element->setAttribute('href', $url->getGeneratedUrl() . $query . $anchor);

            // The processed text now depends on:
            $result
              // - the generated URL (which has undergone path & route processing)
              ->addCacheableDependency($url)
              // - the linked entity (whose URL and title may change)
              ->addCacheableDependency($entity);
          }
        }
        catch (\Exception $e) {
          Error::logException('filter', $e);
        }
      }

      $result->setProcessedText(Html::serialize($dom));
    }

    return $result;
  }

  /**
   * Gets the generated URL object for a linked entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   A linked entity.
   * @param bool $download
   *   Whether the `download` attribute is present and hence a download link
   *   should be generated instead of a view link.
   *
   * @return \Drupal\Core\GeneratedUrl
   *   The generated URL plus cacheability metadata.
   */
  protected static function getUrl(EntityInterface $entity, bool $download): GeneratedUrl {
    $link_target_type = !$download ? 'view' : 'download';
    if ($link_target_handler = $entity->getEntityType()->getHandlerClass('link_target', $link_target_type)) {
      return (new $link_target_handler())->getLinkTarget($entity);
    }

    return $entity->toUrl()->toString(TRUE);
  }

}
