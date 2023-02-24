<?php

namespace Drupal\Component\Plugin;

/**
 * Base class for plugins wishing to support metadata inspection.
 */
abstract class PluginBase implements PluginInspectionInterface, DerivativeInspectionInterface {

  /**
   * A string which is used to separate base plugin IDs from the derivative ID.
   */
  const DERIVATIVE_SEPARATOR = ':';

  /**
   * Configuration information passed into the plugin.
   *
   * When using an interface like
   * \Drupal\Component\Plugin\ConfigurableInterface, this is where the
   * configuration should be stored.
   *
   * Plugin configuration is optional, so plugin implementations must provide
   * their own setters and getters.
   *
   * @var array
   */
  protected $configuration;

  /**
   * Constructs a \Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param mixed $pluginDefinition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, protected $pluginId, protected $pluginDefinition) {
    $this->configuration = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginId() {
    return $this->pluginId;
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseId() {
    $plugin_id = $this->getPluginId();
    if (strpos($plugin_id, static::DERIVATIVE_SEPARATOR)) {
      [$plugin_id] = explode(static::DERIVATIVE_SEPARATOR, $plugin_id, 2);
    }
    return $plugin_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeId() {
    $plugin_id = $this->getPluginId();
    $derivative_id = NULL;
    if (strpos($plugin_id, static::DERIVATIVE_SEPARATOR)) {
      [, $derivative_id] = explode(static::DERIVATIVE_SEPARATOR, $plugin_id, 2);
    }
    return $derivative_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginDefinition() {
    return $this->pluginDefinition;
  }

  /**
   * Determines if the plugin is configurable.
   *
   * @return bool
   *   A boolean indicating whether the plugin is configurable.
   */
  public function isConfigurable() {
    return $this instanceof ConfigurableInterface;
  }

}
