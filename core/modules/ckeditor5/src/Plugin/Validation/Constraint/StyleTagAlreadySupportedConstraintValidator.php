<?php

declare(strict_types = 1);

namespace Drupal\ckeditor5\Plugin\Validation\Constraint;

use Drupal\ckeditor5\HTMLRestrictions;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Styles can only be specified for already supported tags.
 *
 * @internal
 */
class StyleTagAlreadySupportedConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  use PluginManagerDependentValidatorTrait;
  use TextEditorObjectDependentValidatorTrait;

  /**
   * {@inheritdoc}
   *
   * @throws \Symfony\Component\Validator\Exception\UnexpectedTypeException
   *   Thrown when the given constraint is not supported by this validator.
   */
  public function validate($element, Constraint $constraint) {
    if (!$constraint instanceof StyleTagAlreadySupportedConstraint) {
      throw new UnexpectedTypeException($constraint, StyleTagAlreadySupportedConstraint::class);
    }

    $text_editor = $this->createTextEditorObjectFromContext();

    // Get the list of tags enabled by every plugin other than Style.
    $other_enabled_plugins = $this->pluginManager->getEnabledDefinitions($text_editor);
    unset($other_enabled_plugins['ckeditor5_style']);
    $other_enabled_plugin_elements = new HTMLRestrictions($this->pluginManager->getProvidedElements(array_keys($other_enabled_plugins), $text_editor, FALSE));

    // The single tag for which a style is specified, which we are checking now.
    $style_element = HTMLRestrictions::fromString($element);
    assert(count($style_element->getAllowedElements()) === 1);

    // If the intersection between the validated style element and the elements
    // supported by all other enabled CKEditor 5 plugins is empty, that means
    // that the tag used in the style element is not actually supported yet.
    // Hence the Style plugin cannot support setting >1 class on it.
    if ($style_element->intersect($other_enabled_plugin_elements)->allowsNothing()) {
      $tag = array_keys($style_element->getAllowedElements())[0];
      $this->context->buildViolation($constraint->message)
        ->setParameter('%tag', sprintf("<%s>", $tag))
        ->addViolation();
    }
  }

}
