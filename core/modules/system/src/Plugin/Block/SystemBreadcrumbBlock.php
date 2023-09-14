<?php

namespace Drupal\system\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block to display the breadcrumbs.
 *
 * @Block(
 *   id = "system_breadcrumb_block",
 *   admin_label = @Translation("Breadcrumbs")
 * )
 */
class SystemBreadcrumbBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs a new SystemBreadcrumbBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface $breadcrumbManager
   *   The breadcrumb manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The current route match.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, protected BreadcrumbBuilderInterface $breadcrumbManager, protected RouteMatchInterface $routeMatch) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('breadcrumb'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return $this->breadcrumbManager->build($this->routeMatch)->toRenderable();
  }

}
