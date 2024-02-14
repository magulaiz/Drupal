<?php

namespace Drupal\layout_builder\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\layout_builder\SectionStorage\SectionStorageDefinition;

/**
 * Defines a SectionStorage attribute.
 *
 * Plugin Namespace: Plugin\SectionStorage
 *
 * @see \Drupal\layout_builder\SectionStorage\SectionStorageManager
 * @see plugin_api
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class SectionStorage extends Plugin {

  /**
   * Constructs a SectionStorage attribute.
   */
  public function __construct(
    public readonly string $id,
    public readonly int $weight = 0,
    public readonly array $context_definitions = [],
    public readonly bool $handles_permission_check = FALSE,
  ) {}

  public function get(): array|object {
    return new SectionStorageDefinition([
      'id' => $this->id,
      'class' => $this->class,
      'weight' => $this->weight,
      'context_definitions' => $this->context_definitions,
      'handles_permission_check' => $this->handles_permission_check,
    ]);
  }

}
