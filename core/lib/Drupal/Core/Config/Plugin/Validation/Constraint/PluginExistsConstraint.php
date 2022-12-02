<?php

declare(strict_types = 1);

namespace Drupal\Core\Config\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks if a plugin exists and optionally implements a particular interface.
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
  public string $unknownPluginMessage = "The '@plugin_id' plugin does not exist.";

  /**
   * The error message if a plugin does not implement the expected interface.
   *
   * @var string
   */
  public string $invalidInterfaceMessage = "The '@plugin_id' plugin must implement or extend @interface.";

  /**
   * The name of the plugin manager service.
   *
   * @var string
   */
  public string $manager;

  /**
   * Optional name of the interface that the plugin must implement.
   *
   * @var string|null
   */
  public ?string $interface = NULL;

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
