<?php

declare(strict_types = 1);

namespace Drupal\Core\Plugin\Plugin\Validation\Constraint;

use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Component\Plugin\FallbackPluginManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the PluginExists constraint.
 */
class PluginExistsConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $plugin_id, Constraint $constraint) {
    assert($constraint instanceof PluginExistsConstraint);

    // If the plugin ID should be derived from the key of the value being
    // validated, it should be the final part of the property path.
    if ($constraint->pluginIdFromKey) {
      $property_path = explode('.', $this->context->getPropertyPath());
      $plugin_id = end($property_path);
    }

    $definition = $constraint->pluginManager->getDefinition($plugin_id, FALSE);
    // Some plugin managers provide fallbacks.
    if ($constraint->pluginManager instanceof FallbackPluginManagerInterface) {
      $fallback_plugin_id = $constraint->pluginManager->getFallbackPluginId($plugin_id);
      $definition = $constraint->pluginManager->getDefinition($fallback_plugin_id, FALSE);
    }

    if (empty($definition)) {
      $this->context->addViolation($constraint->unknownPluginMessage, [
        '@plugin_id' => $plugin_id,
      ]);
      return;
    }

    // If we don't need to validate the plugin class's interface, we're done.
    if (empty($constraint->interface)) {
      return;
    }

    if (!is_a(DefaultFactory::getPluginClass($plugin_id, $definition), $constraint->interface, TRUE)) {
      $this->context->addViolation($constraint->invalidInterfaceMessage, [
        '@plugin_id' => $plugin_id,
        '@interface' => $constraint->interface,
      ]);
    }
  }

}
