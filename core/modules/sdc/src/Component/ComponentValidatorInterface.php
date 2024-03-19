<?php

namespace Drupal\sdc\Component;

use Drupal\sdc\ComponentInterface;
use JsonSchema\Validator;


interface ComponentValidatorInterface {

  /**
   * Sets the validator service if available.
   */
  public function setValidator(Validator $validator = NULL): void;

  /**
   * Validates the component metadata file.
   *
   * A valid component metadata file can be validated against the
   * metadata-author.schema.json, plus the ability of classes and interfaces
   * in the `type` property.
   *
   * @param array $definition
   *   The definition to validate.
   * @param bool $enforce_schemas
   *   TRUE if schema definitions are mandatory.
   *
   * @return bool
   *   TRUE if the component is valid.
   *
   * @throws \Drupal\sdc\Exception\InvalidComponentException
   */
  public function validateDefinition(array $definition, bool $enforce_schemas): bool;

  /**
   * Validates that the props provided to the component.
   *
   * Valid props are compliant with the schema definition in the component
   * metadata file.
   *
   * @param array $context
   *   The Twig context that contains the prop data.
   * @param \Drupal\sdc\ComponentInterface $component
   *   The component to validate the props against.
   *
   * @return bool
   *   TRUE if the props adhere to the component definition.
   *
   * @throws \Drupal\sdc\Exception\InvalidComponentException
   */
  public function validateProps(array $context, ComponentInterface $component): bool;

}
