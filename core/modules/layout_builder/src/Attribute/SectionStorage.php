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
   *
   * @param string $id
   *   The plugin ID.
   * @param int $weight
   *   The plugin weight, optional.
   *   When an entity with layout is rendered, section storage plugins are
   *   checked, in order of their weight, to determine which one should be used
   *   to render the layout.
   * @param array $context_definitions
   *   Any required context definitions, optional.
   *   When an entity with layout is rendered, all section storage plugins which
   *   match a particular set of contexts are checked, in order of their weight,
   *   to determine which plugin should be used to render the layout.
   *   @see \Drupal\layout_builder\SectionStorage\SectionStorageManagerInterface::findByContext()
   * @param bool $handles_permission_check
   *   Indicates that this section storage handles its own permission checking.
   *   If FALSE, the 'configure any layout' permission will be required during
   *   routing access. If TRUE, Layout Builder will not enforce any access
   *   restrictions for the storage, so the section storage's implementation of
   *   access() must perform the access checking itself.
   */
  public function __construct(
    public readonly string $id,
    public readonly int $weight = 0,
    public readonly array $context_definitions = [],
    public readonly bool $handles_permission_check = FALSE,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function get(): SectionStorageDefinition {
    return new SectionStorageDefinition([
      'id' => $this->id,
      'class' => $this->class,
      'weight' => $this->weight,
      'context_definitions' => $this->context_definitions,
      'handles_permission_check' => $this->handles_permission_check,
    ]);
  }

}
