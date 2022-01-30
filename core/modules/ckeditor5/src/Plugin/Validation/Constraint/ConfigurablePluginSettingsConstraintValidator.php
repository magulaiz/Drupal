<?php

declare(strict_types = 1);

namespace Drupal\ckeditor5\Plugin\Validation\Constraint;

use Drupal\ckeditor5\Plugin\CKEditor5PluginDefinition;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Configurable plugin settings validator.
 *
 * @internal
 */
class ConfigurablePluginSettingsConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  use PluginManagerDependentValidatorTrait;
  use TextEditorObjectDependentValidatorTrait;

  /**
   * {@inheritdoc}
   *
   * @throws \Symfony\Component\Validator\Exception\UnexpectedTypeException
   *   Thrown when the given constraint is not supported by this validator.
   */
  public function validate($settings, Constraint $constraint) {
    if (!$constraint instanceof ConfigurablePluginSettingsConstraint) {
      throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\ConfigurablePluginSettingsConstraint');
    }

    try {
      $plugin_settings = $this->context->getRoot()->get('settings.plugins')->getValue();
    }
    catch (\InvalidArgumentException $e) {
      $plugin_settings = [];
    }

    $text_editor = $this->createTextEditorObjectFromContext();
    $enabled_definitions = $this->pluginManager->getEnabledDefinitions($text_editor);

    foreach ($plugin_settings as $id => $plugin_setting) {
      if (empty($enabled_definitions[$id])) {
        $plugin = $this->pluginManager->getPlugin($id, $text_editor);
        $this->context->buildViolation($constraint->message)
          ->setParameter('%plugin_label', (string) $plugin->getPluginDefinition()->label())
          ->setParameter('%plugin_id', $id)
          ->atPath("plugins.$id")
          ->addViolation();
      }
    }
  }

}
