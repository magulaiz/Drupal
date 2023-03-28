<?php

declare(strict_types=1);

namespace Drupal\media\Entity;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityLinkTargetInterface;
use Drupal\Core\GeneratedUrl;
use Drupal\file\FileInterface;

/**
 * Provides a Media link target handler.
 *
 * Media entities are atypical because by default they do not have their own
 * stand-alone URL, which means only a subset of Media entities is actually
 * linkable.
 *
 * @see media_entity_type_alter()
 * @see \Drupal\media\Routing\MediaRouteProvider::getCanonicalRoute()
 * @see https://www.drupal.org/i/3017935
 *
 * On the other hand, media entities that use the "file" media source plugin can
 * be linked regardless of that setting because the referenced files are still
 * linkable.
 *
 * @see \Drupal\file\Entity\FileLinkTarget
 */
class MediaLinkTarget implements EntityLinkTargetInterface {

  /**
   * {@inheritdoc}
   */
  public function getLinkTarget(EntityInterface $entity): GeneratedUrl {
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

    // Media entities using a media source plugin other than "file" are only
    // linkable if and only if standalone URLs are enabled: linking to their
    // edit forms is meaningless.
    if ($entity->getEntityType()->getLinkTemplate('canonical') !== $entity->getEntityType()->getLinkTemplate('edit-form')) {
      return $entity->toUrl()->toString(TRUE);
    }

    // @todo Ensure that in the entity selection plugin logic only file media
    // entities are returned unless standalone URLs are enabled, to avoid
    // meaningless links like this one
    // @todo Also evaluate tightening the interface then!
    return (new GeneratedUrl())
      ->setGeneratedUrl('')
      // No path & route processing means permanent cacheability.
      ->setCacheMaxAge(Cache::PERMANENT);
  }

}
