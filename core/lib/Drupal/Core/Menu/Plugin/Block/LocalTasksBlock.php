<?php

namespace Drupal\Core\Menu\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\LocalTaskManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

/**
 * Provides a "Tabs" block to display the local tasks.
 */
#[Block(
  id: "local_tasks_block",
  admin_label: new TranslatableMarkup("Tabs")
)]
class LocalTasksBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The local task manager.
   *
   * @var \Drupal\Core\Menu\LocalTaskManagerInterface
   */
  protected $localTaskManager;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Creates a LocalTasksBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Menu\LocalTaskManagerInterface $local_task_manager
   *   The local task manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LocalTaskManagerInterface $local_task_manager, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->localTaskManager = $local_task_manager;
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
      $container->get('plugin.manager.menu.local_task'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'label_display' => FALSE,
      'primary' => TRUE,
      'secondary' => TRUE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->configuration;
    $cacheability = new CacheableMetadata();
    $cacheability->addCacheableDependency($this->localTaskManager);
    $tabs = [
      '#theme' => 'menu_local_tasks',
    ];

    // Add only selected levels for the printed output.
    if ($config['primary']) {
      $links = $this->localTaskManager->getLocalTasks($this->routeMatch->getRouteName(), 0);
      $cacheability = $cacheability->merge($links['cacheability']);
      // Do not display single tabs.
      $tabs += [
        '#primary' => count(Element::getVisibleChildren($links['tabs'])) > 1 ? $links['tabs'] : [],
      ];
    }

    if ($config['secondary']) {
      $level = 1;
      $cacheability->addCacheContexts(['route']);
      $instances = $this->localTaskManager->getTasksBuild($this->routeMatch->getRouteName(), $cacheability);
      $current_url = Url::fromRoute($this->routeMatch->getRouteName(), $this->routeMatch->getRawParameters()->all())->toString();
      foreach ($instances as $key_instance => $instance) {
        if ($key_instance != 0) {
          foreach ($instance as $local_task) {
            $instance_url = Url::fromRoute($local_task["#link"]["url"]->getRouteName(), $local_task["#link"]["url"]->getRouteParameters())->toString();
            if ($instance_url == $current_url) {
              $level = $key_instance;
              break;
            }
          }
        }
        if ($level != 1) {
          break;
        }
      }
      $links = $this->localTaskManager->getLocalTasks($this->routeMatch->getRouteName(), $level);
      $cacheability = $cacheability->merge($links['cacheability']);
      // Do not display single tabs.
      $tabs += [
        '#secondary' => count(Element::getVisibleChildren($links['tabs'])) > 1 ? $links['tabs'] : [],
      ];
    }

    $build = [];
    $cacheability->applyTo($build);
    if (empty($tabs['#primary']) && empty($tabs['#secondary'])) {
      return $build;
    }

    return $build + $tabs;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->configuration;
    $defaults = $this->defaultConfiguration();

    $form['levels'] = [
      '#type' => 'details',
      '#title' => $this->t('Shown tabs'),
      '#description' => $this->t('Select tabs being shown in the block'),
      // Open if not set to defaults.
      '#open' => $defaults['primary'] !== $config['primary'] || $defaults['secondary'] !== $config['secondary'],
    ];
    $form['levels']['primary'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show primary tabs'),
      '#default_value' => $config['primary'],
    ];
    $form['levels']['secondary'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show secondary tabs'),
      '#default_value' => $config['secondary'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $levels = $form_state->getValue('levels');
    $this->configuration['primary'] = $levels['primary'];
    $this->configuration['secondary'] = $levels['secondary'];
  }

}
