<?php

namespace Drupal\node\Plugin\views\argument_default;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Drupal\views\Plugin\views\argument_default\ArgumentDefaultPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the content type of the current node as default argument value.
 *
 * @ingroup views_argument_default_plugins
 *
 * @ViewsArgumentDefault(
 *   id = "node_type",
 *   title = @Translation("Current node type")
 * )
 */
class NodeType extends ArgumentDefaultPluginBase implements CacheableDependencyInterface {

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs a new NodeType instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getArgument(): string|bool {
    // Get the node object from current route.
    $node = $this->routeMatch->getParameter('node') ?? $this->routeMatch->getParameter('node_preview');
    if ($node instanceof NodeInterface) {
      return $node->bundle();
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts(): string {
    return ['url'];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge(): int {
    return Cache::PERMANENT;
  }

}
