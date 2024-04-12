<?php

declare(strict_types=1);

namespace Drupal\navigation;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Plugin\DefaultSingleLazyPluginCollection;

/**
 * Provides a collection of navigation_block plugins.
 */
class NavigationBlockPluginCollection extends DefaultSingleLazyPluginCollection {

  /**
   * Constructs a new NavigationBlockPluginCollection.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $manager
   *   The manager to be used for instantiating plugins.
   * @param string $instance_id
   *   The ID of the plugin instance.
   * @param array $configuration
   *   An array of configuration.
   * @param string|null $navigationBlockId
   *   The unique ID of the navigation_block entity using this plugin.
   */
  public function __construct(PluginManagerInterface $manager, $instance_id, array $configuration, protected ?string $navigationBlockId) {
    parent::__construct($manager, $instance_id, $configuration);
  }

  /**
   * {@inheritdoc}
   */
  protected function initializePlugin($instance_id) {
    if (!$instance_id) {
      throw new PluginException("The navigation_block '$this->navigationBlockId' did not specify a plugin.");
    }

    try {
      parent::initializePlugin($instance_id);
    }
    catch (PluginException $e) {
      $module = $this->configuration['provider'];
      // Ignore navigation_blocks belonging to uninstalled modules, but
      // re-throw valid exceptions when the module is installed and the plugin
      // is misconfigured.
      if (!$module || \Drupal::moduleHandler()->moduleExists($module)) {
        throw $e;
      }
    }
  }

}
