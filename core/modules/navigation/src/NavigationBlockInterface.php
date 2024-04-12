<?php

declare(strict_types=1);

namespace Drupal\navigation;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Defines the required interface for all navigation block plugins.
 */
interface NavigationBlockInterface extends ConfigEntityInterface {

  /**
   * Returns the plugin instance.
   *
   * @return \Drupal\navigation\NavigationBlockPluginInterface
   *   The plugin instance for this navigation block.
   */
  public function getPlugin();

  /**
   * Returns the plugin ID.
   *
   * @return string
   *   The plugin ID for this navigation block.
   */
  public function getPluginId();

  /**
   * Returns the region this navigation block is placed in.
   *
   * @return string
   *   The region this navigation block is placed in.
   */
  public function getRegion();

  /**
   * Returns an array of visibility condition configurations.
   *
   * @return array
   *   An array of visibility condition configuration keyed by the condition ID.
   */
  public function getVisibility();

  /**
   * Gets conditions for this navigation block.
   *
   * @return \Drupal\Core\Condition\ConditionInterface[]|\Drupal\Core\Condition\ConditionPluginCollection
   *   An array or collection of configured condition plugins.
   */
  public function getVisibilityConditions();

  /**
   * Gets a visibility condition plugin instance.
   *
   * @param string $instance_id
   *   The condition plugin instance ID.
   *
   * @return \Drupal\Core\Condition\ConditionInterface
   *   A condition plugin.
   */
  public function getVisibilityCondition($instance_id);

  /**
   * Sets the visibility condition configuration.
   *
   * @param string $instance_id
   *   The condition instance ID.
   * @param array $configuration
   *   The condition configuration.
   *
   * @return $this
   */
  public function setVisibilityConfig($instance_id, array $configuration);

  /**
   * Returns the weight of this navigation block (used for sorting).
   *
   * @return int
   *   The block weight.
   */
  public function getWeight();

  /**
   * Sets the region this navigation block is placed in.
   *
   * @param string $region
   *   The region to place this block in.
   *
   * @return $this
   */
  public function setRegion($region);

  /**
   * Sets the navigation block weight.
   *
   * @param int $weight
   *   The desired weight.
   *
   * @return $this
   */
  public function setWeight($weight);

}
