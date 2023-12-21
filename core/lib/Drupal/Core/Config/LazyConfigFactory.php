<?php

namespace Drupal\Core\Config;

/**
 * Lazy config factory.
 */
class LazyConfigFactory implements ConfigFactoryInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(protected ConfigFactoryInterface $configFactory) {
  }

  /**
   * {@inheritdoc}
   */
  public function get($name) {
    return $this->configFactory->get($name);
  }

  /**
   * {@inheritdoc}
   */
  public function getEditable($name) {
    return $this->configFactory->getEditable($name);
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultiple(array $names) {
    return $this->configFactory->loadMultiple($names);
  }

  /**
   * {@inheritdoc}
   */
  public function reset($name = NULL) {
    return $this->configFactory->reset($name);
  }

  /**
   * {@inheritdoc}
   */
  public function rename($old_name, $new_name) {
    return $this->configFactory->rename($old_name, $new_name);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheKeys() {
    return $this->configFactory->getCacheKeys();
  }

  /**
   * {@inheritdoc}
   */
  public function clearStaticCache() {
    return $this->configFactory->clearStaticCache();
  }

  /**
   * {@inheritdoc}
   */
  public function listAll($prefix = '') {
    return $this->configFactory->listAll($prefix);
  }

  /**
   * {@inheritdoc}
   */
  public function addOverride(ConfigFactoryOverrideInterface $config_factory_override) {
    return $this->configFactory->addOverride($config_factory_override);
  }

}
