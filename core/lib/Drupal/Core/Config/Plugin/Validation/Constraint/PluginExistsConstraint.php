<?php

declare(strict_types = 1);

namespace Drupal\Core\Config\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks if a plugin exists.
 *
 * @Constraint(
 *   id = "PluginExists",
 *   label = @Translation("Plugin exists", context = "Validation"),
 *   type = { "entity" }
 * )
 */
class PluginExistsConstraint extends Constraint {

  /**
   * The error message if a plugin does not exist.
   *
   * @var string
   */
  public string $message = "The '@plugin_id' plugin does not exist.";

  /**
   * The name of the plugin manager service.
   *
   * @var string
   */
  public string $manager;

  /**
   * {@inheritdoc}
   */
  public function getDefaultOption() {
    return 'manager';
  }

  /**
   * {@inheritdoc}
   */
  public function getRequiredOptions() {
    return ['manager'];
  }

}
