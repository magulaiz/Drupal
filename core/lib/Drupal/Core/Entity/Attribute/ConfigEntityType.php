<?php

namespace Drupal\Core\Entity\Attribute;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines a config entity type attribute object.
 *
 * Config entity type plugins use an object-based annotation method, rather than an
 * array-type (as commonly used on other plugin types).
 * The attribute properties of config entity types are found on
 * \Drupal\Core\Config\Entity\ConfigEntityType and are accessed using get/set
 * methods defined in \Drupal\Core\Entity\EntityTypeInterface.
 *
 * @ingroup entity_api
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class ConfigEntityType extends EntityType {

  public function __construct(
    public readonly string $config_prefix,
    public readonly array $lookup_keys,
    public readonly array $config_export,
    ...$base
  ) {

    $base += [
      'entity_type_class' => 'Drupal\Core\Config\Entity\ConfigEntityType',
      'group' => 'configuration',
      'group_label' => new TranslatableMarkup('Configuration', [], ['context' => 'Entity type group']),
      'static_cache' => FALSE,
    ];
    parent::__construct(...$base);
  }

}
