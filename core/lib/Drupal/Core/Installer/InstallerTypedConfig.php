<?php

namespace Drupal\Core\Installer;

use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\Core\Validation\ConstraintManager;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Decorates the 'config.typed' service during the installer.
 */
class InstallerTypedConfig implements TypedConfigManagerInterface {

  /**
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $decoratedService
   *   The inner 'config.typed' service.
   */
  public function __construct(readonly private TypedConfigManagerInterface $decoratedService) {
  }

  /**
   * {@inheritdoc}
   */
  public function clearCachedDefinitions() {
    $this->decoratedService->clearCachedDefinitions();
  }

  /**
   * {@inheritdoc}
   */
  public function useCaches($use_caches = FALSE) {
    $this->decoratedService->useCaches($use_caches);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions() {
    return $this->decoratedService->getDefinitions();
  }

  /**
   * {@inheritdoc}
   */
  public function hasDefinition($plugin_id) {
    return $this->decoratedService->hasDefinition($plugin_id);
  }

  /**
   * {@inheritdoc}
   */
  public function get($name) {
    return $this->decoratedService->get($name);
  }

  /**
   * {@inheritdoc}
   */
  public function buildDataDefinition(array $definition, $value, $name = NULL, $parent = NULL) {
    return $this->decoratedService->buildDataDefinition($definition, $value, $name, $parent);
  }

  /**
   * {@inheritdoc}
   */
  public function hasConfigSchema($name) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinition($plugin_id, $exception_on_invalid = TRUE) {
    return $this->decoratedService->getDefinition($plugin_id, $exception_on_invalid);
  }

  /**
   * {@inheritdoc}
   */
  public function createFromNameAndData($config_name, array $config_data) {
    return $this->decoratedService->createFromNameAndData($config_name, $config_data);
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($data_type, array $configuration = []) {
    return $this->decoratedService->createInstance($data_type, $configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function create(DataDefinitionInterface $definition, $value = NULL, $name = NULL, $parent = NULL) {
    return $this->decoratedService->create($definition, $value, $name, $parent);
  }

  /**
   * {@inheritdoc}
   */
  public function createDataDefinition($data_type) {
    return $this->decoratedService->createDataDefinition($data_type);
  }

  /**
   * {@inheritdoc}
   */
  public function createListDataDefinition($item_type) {
    return $this->decoratedService->createListDataDefinition($item_type);
  }

  /**
   * {@inheritdoc}
   */
  public function getInstance(array $options) {
    return $this->decoratedService->getInstance($options);
  }

  /**
   * {@inheritdoc}
   */
  public function getPropertyInstance(TypedDataInterface $object, $property_name, $value = NULL) {
    return $this->decoratedService->getPropertyInstance($object, $property_name, $value);
  }

  /**
   * {@inheritdoc}
   */
  public function getValidator() {
    return $this->decoratedService->getValidator();
  }

  /**
   * {@inheritdoc}
   */
  public function setValidator(ValidatorInterface $validator) {
    $this->decoratedService->setValidator($validator);
  }

  /**
   * {@inheritdoc}
   */
  public function getValidationConstraintManager() {
    return $this->decoratedService->getValidationConstraintManager();
  }

  /**
   * {@inheritdoc}
   */
  public function setValidationConstraintManager(ConstraintManager $constraintManager) {
    return $this->decoratedService->setValidationConstraintManager($constraintManager);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultConstraints(DataDefinitionInterface $definition) {
    return $this->decoratedService->getDefaultConstraints($definition);
  }

  /**
   * {@inheritdoc}
   */
  public function getCanonicalRepresentation(TypedDataInterface $data) {
    return $this->decoratedService->getCanonicalRepresentation($data);
  }

}
