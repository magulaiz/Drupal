<?php

declare(strict_types=1);

namespace Drupal\media\Entity;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityLinkTargetInterface;
use Drupal\Core\GeneratedUrl;
use Drupal\media\MediaInterface;
use Drupal\media\MediaSourceInterface;

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
 * Every media source plugin is free to implement the base implementation of
 * :getMetadata() because a source plugin is guaranteed to know how the media is
 * stored and linked.
 *
 * @see \Drupal\media\MediaSourceInterface::METADATA_ATTRIBUTE_LINK_TARGET
 * @see \Drupal\media\MediaSourceBase::getMetadata()
 * @see \Drupal\media\Plugin\media\Source\File::getMetadata()
 * @see \Drupal\media\Plugin\media\Source\OEmbed::getMetadata()
 * @see \Drupal\file\Entity\FileLinkTarget
 * @see \Drupal\media\Plugin\EntityReferenceSelection\MediaWithLinkTargetSelection
 */
class MediaLinkTarget implements EntityLinkTargetInterface {

  /**
   * {@inheritdoc}
   */
  public function getLinkTarget(EntityInterface $entity): GeneratedUrl {
    assert($entity instanceof MediaInterface);
    if ($link_target = $entity->getSource()->getMetadata($entity, MediaSourceInterface::METADATA_ATTRIBUTE_LINK_TARGET)) {
      return $link_target;
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
