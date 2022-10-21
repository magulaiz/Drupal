<?php

declare(strict_types = 1);

namespace Drupal\jsonapi\Events;

/**
 * Contains all events emitted that allow changing metadata for JSON:API
 * resources and relationships.
 *
 * @see \Drupal\jsonapi\Events\CollectRelationshipMetaEvent
 * @see \Drupal\jsonapi\Events\CollectResourceObjectMetaEvent
 * @see \Drupal\jsonapi\Controller\EntityResource
 * @see \Drupal\jsonapi\Normalizer\ResourceObjectNormalizer
 */
final class MetaDataEvents {

  /**
   * Emitted when normalizing a ResourceObject.
   */
  const COLLECT_RESOURCE_OBJECT_META = 'jsonapi.collect_resource_object_meta';

  /**
   * Emitted when building the relationship of a resource.
   */
  const COLLECT_RELATIONSHIP_META = 'jsonapi.collect_relationship_meta';

}
