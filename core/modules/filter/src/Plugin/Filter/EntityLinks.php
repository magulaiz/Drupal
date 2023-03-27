<?php

declare(strict_types = 1);

namespace Drupal\filter\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\GeneratedUrl;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\TypedData\TranslatableInterface;
use Drupal\file\FileInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\media\MediaInterface;
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
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected readonly EntityRepositoryInterface $entityRepository,
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
      $container->get('entity.repository')
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

            $url = $this->getUrl($entity);

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
          watchdog_exception('filter', $e);
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
   *
   * @return \Drupal\Core\GeneratedUrl
   *   The generated URL plus cacheability metadata.
   */
  protected static function getUrl(EntityInterface $entity): GeneratedUrl {
    // The first special case: File entities. They are the exception because
    // they are not served by Drupal, but by the web server.
    // @see \Drupal\file\FileInterface::createFileUrl()
    // @see \Drupal\Core\File\FileUrlGeneratorInterface
    if ($entity instanceof FileInterface) {
      $url = $entity->createFileUrl(TRUE);
      // The $url is a string, which provides no cacheability metadata.
      assert(is_string($url));
      return (new GeneratedUrl())
        ->setGeneratedUrl($url)
        // No path & route processing means permanent cacheability.
        ->setCacheMaxAge(Cache::PERMANENT);
    }

    // The second special case: Media entities. They are the exception because
    // by default they do not have their own stand-alone URL, which means only
    // a subset of Media entities is actually linkable.
    // @see https://www.drupal.org/i/3017935
    if ($entity instanceof MediaInterface) {
      // Media entities using the "file" media source plugin.
      // @see \Drupal\media\Plugin\media\Source\File
      $source_field = $entity->getSource()->getSourceFieldDefinition($entity->get('bundle')->entity);
      if ($source_field && $entity->hasField($source_field->getName()) && $entity->get($source_field->getName())->entity instanceof FileInterface) {
        $file = $entity->get($source_field->getName())->entity;
        // Similar to the File entities special case, but subtly different.
        $url = $file->createFileUrl(TRUE);
        assert(is_string($url));
        return (new GeneratedUrl())
          ->setGeneratedUrl($url)
          ->setCacheMaxAge(Cache::PERMANENT)
          // The subtle but crucial difference compared to File entity.
          ->addCacheableDependency($file);
      }
      else {
        // Media entities using a media source plugin other than "file" are only
        // linkable if and only if standalone URLs are enabled: linking to their
        // edit forms is meaningless.
        // @see media_entity_type_alter()
        // @see \Drupal\media\Routing\MediaRouteProvider::getCanonicalRoute
        if ($entity->getEntityType()->getLinkTemplate('canonical') == $entity->getEntityType()->getLinkTemplate('edit-form')) {
          // @todo Ensure that in the entity selection plugin logic only file
          // media entities are returned unless standalone URLs are enabled, to
          // avoid meaningless links like this one.
          return (new GeneratedUrl())
            ->setGeneratedUrl('')
            // No path & route processing means permanent cacheability.
            ->setCacheMaxAge(Cache::PERMANENT);
        }
      }
    }

    return $entity->toUrl()->toString(TRUE);
  }

}
