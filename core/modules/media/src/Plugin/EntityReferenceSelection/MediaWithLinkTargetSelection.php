<?php

namespace Drupal\media\Plugin\EntityReferenceSelection;

/**
 * Limits selection of media entities to those that have a link target.
 *
 * When standalone URLs are:
 * - enabled, all media entities have a link target and will be returned
 * - disabled, only media entities using a media source plugin whose
 *   ::getMetadata() method computes a METADATA_ATTRIBUTE_LINK_TARGET
 *   should be returned, because only they can have link targets.
 *
 * @see \Drupal\media\MediaSourceInterface::METADATA_ATTRIBUTE_LINK_TARGET
 * @see \Drupal\media\Plugin\media\Source\OEmbed::getMetadata()
 * @see \Drupal\media\Entity\MediaLinkTarget
 *
 * @EntityReferenceSelection(
 *   id = "default:media_link_target",
 *   label = @Translation("Media with link target selection"),
 *   entity_types = {"media"},
 *   group = "default",
 * )
 */
class MediaWithLinkTargetSelection extends MediaSelection {

  /**
   * {@inheritdoc}
   */
  protected function buildEntityQuery($match = NULL, $match_operator = 'CONTAINS') {
    $query = parent::buildEntityQuery($match, $match_operator);

    // phpcs:disable
    // @see \Drupal\media\MediaSourceBase::getMetadata()
    if (!\Drupal::config('media.settings')->get('standalone_url')) {
      // @todo add logic to avoid finding media entities that are not linkable: any media bundle whose media source does not compute a link target should be omitted
//      $query->condition('bundle', 'document', '<>');
    }
    // phpcs:enable

    return $query;
  }

}
