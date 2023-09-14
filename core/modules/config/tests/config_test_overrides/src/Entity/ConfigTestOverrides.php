<?php

namespace Drupal\config_test_overrides\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\config_test\ConfigTestInterface;

/**
 * Defines the ConfigTest configuration entity.
 *
 * @ConfigEntityType(
 *   id = "config_test_overrides",
 *   label = @Translation("Test configuration overrides"),
 *   config_prefix = "dynamic",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *   },
 * )
 */
class ConfigTestOverrides extends ConfigEntityBase implements ConfigTestInterface {

  /**
   * The machine name for the configuration entity.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of the configuration entity.
   *
   * @var string
   */
  public $label;

}
