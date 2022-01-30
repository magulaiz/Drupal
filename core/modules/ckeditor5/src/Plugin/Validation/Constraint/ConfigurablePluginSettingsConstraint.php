<?php

declare(strict_types = 1);

namespace Drupal\ckeditor5\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks if configurable CKEditor 5 plugins are enabled for existing settings.
 *
 * @Constraint(
 *   id = "CKEditor5ConfigurablePluginSettings",
 *   label = @Translation("CKEditor 5 configurable plugin settings", context = "Validation"),
 * )
 *
 * @internal
 */
class ConfigurablePluginSettingsConstraint extends Constraint {

  /**
   * The default violation message.
   *
   * @var string
   */
  public $message = 'Configuration for the plugin "%plugin_label" (%plugin_id) exists, but the plugin is not enabled.';

}
