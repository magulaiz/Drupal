<?php

namespace Drupal\Core\Entity\Attribute;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines a content entity type attribute object.
 *
 * Content entity type plugins use an object-based annotation method, rather than an
 * array-type (as commonly used on other plugin types).
 * The attribute properties of content entity types are found on
 * \Drupal\Core\Entity\ContentEntityType and are accessed using get/set methods defined
 * in \Drupal\Core\Entity\ContentEntityTypeInterface.
 *
 * @ingroup entity_api
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class ContentEntityType extends EntityType {

  public function __construct(
    public readonly array $revision_metadata_keys,
    ...$base,
  ) {

    $base += [
      'entity_type_class' => 'Drupal\Core\Entity\ContentEntityType',
      'group' => 'content',
      'group_label' => new TranslatableMarkup('Content', [], ['context' => 'Entity type group']),
    ];
    parent::__construct(...$base);
  }

}
