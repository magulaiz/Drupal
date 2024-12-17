<?php

namespace Drupal\Core\Field;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Defines an interface for field type category managers.
 *
 * @template-extends \Drupal\Component\Plugin\PluginManagerInterface<\Drupal\Core\Field\FieldTypeCategoryInterface>
 */
interface FieldTypeCategoryManagerInterface extends PluginManagerInterface {

  /**
   * Fallback category for field types.
   */
  const FALLBACK_CATEGORY = 'general';

}
