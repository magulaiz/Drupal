<?php

namespace Drupal\autowire_test\Plugin\Block;

use Drupal\autowire_test\TestTrait;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Provides a block for testing autowired plugins with trait factory method.
 *
 * @Block(
 *   id = "container_factory_trait"
 * )
 */
class ContainerFactoryTrait extends BlockBase implements ContainerFactoryPluginInterface {

  use TestTrait;

  /**
   * Constructs a new instance.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The route match.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    public RouteMatchInterface $routeMatch,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [];
  }

}
