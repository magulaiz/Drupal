<?php

namespace Drupal\Core\Menu;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Access\AccessManagerInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;
use Drupal\Core\Plugin\Factory\ContainerFactory;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;

/**
 * Provides the default local task manager using YML as primary definition.
 */
class LocalTaskManager extends DefaultPluginManager implements LocalTaskManagerInterface {

  /**
   * {@inheritdoc}
   */
  protected $defaults = [
    // (required) The name of the route this task links to.
    'route_name' => '',
    // Parameters for route variables when generating a link.
    'route_parameters' => [],
    // The static title for the local task.
    'title' => '',
    // The route name where the root tab appears.
    'base_route' => '',
    // The plugin ID of the parent tab (or NULL for the top-level tab).
    'parent_id' => NULL,
    // The weight of the tab.
    'weight' => NULL,
    // The default link options.
    'options' => [],
    // Default class for local task implementations.
    'class' => 'Drupal\Core\Menu\LocalTaskDefault',
    // The plugin id. Set by the plugin system based on the top-level YAML key.
    'id' => '',
  ];

  /**
   * An argument resolver object.
   *
   * @var \Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface
   */
  protected $argumentResolver;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The plugin instances.
   *
   * @var array
   */
  protected $instances = [];

  /**
   * The local task render arrays for the current route.
   *
   * @var array
   */
  protected $taskData;

  /**
   * The route provider to load routes by name.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * The access manager.
   *
   * @var \Drupal\Core\Access\AccessManagerInterface
   */
  protected $accessManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * Constructs a \Drupal\Core\Menu\LocalTaskManager object.
   *
   * @param \Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface $argument_resolver
   *   An object to use in resolving route arguments.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request object to use for building titles and paths for plugin instances.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   The route provider to load routes by name.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Access\AccessManagerInterface $access_manager
   *   The access manager.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   */
  public function __construct(ArgumentResolverInterface $argument_resolver, RequestStack $request_stack, RouteMatchInterface $route_match, RouteProviderInterface $route_provider, ModuleHandlerInterface $module_handler, CacheBackendInterface $cache, LanguageManagerInterface $language_manager, AccessManagerInterface $access_manager, AccountInterface $account) {
    $this->factory = new ContainerFactory($this, '\Drupal\Core\Menu\LocalTaskInterface');
    $this->argumentResolver = $argument_resolver;
    $this->requestStack = $request_stack;
    $this->routeMatch = $route_match;
    $this->routeProvider = $route_provider;
    $this->accessManager = $access_manager;
    $this->account = $account;
    $this->moduleHandler = $module_handler;
    $this->alterInfo('local_tasks');
    $this->setCacheBackend($cache, 'local_task_plugins:' . $language_manager->getCurrentLanguage()->getId(), ['local_task']);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery() {
    if (!isset($this->discovery)) {
      $yaml_discovery = new YamlDiscovery('links.task', $this->moduleHandler->getModuleDirectories());
      $yaml_discovery->addTranslatableProperty('title', 'title_context');
      $this->discovery = new ContainerDerivativeDiscoveryDecorator($yaml_discovery);
    }
    return $this->discovery;
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    parent::processDefinition($definition, $plugin_id);
    // If there is no route name, this is a broken definition.
    if (empty($definition['route_name'])) {
      throw new PluginException(sprintf('Plugin (%s) definition must include "route_name"', $plugin_id));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle(LocalTaskInterface $local_task) {
    $controller = [$local_task, 'getTitle'];
    $request = $this->requestStack->getCurrentRequest();
    $arguments = $this->argumentResolver->getArguments($request, $controller);
    return call_user_func_array($controller, $arguments);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions() {
    $definitions = parent::getDefinitions();

    $count = 0;
    foreach ($definitions as &$definition) {
      if (isset($definition['weight'])) {
        // Add some micro weight.
        $definition['weight'] += ($count++) * 1e-6;
      }
    }

    return $definitions;
  }

  /**
   * Return active-trail of task definition plugin ids for the current route.
   *
   * @param string $route_name
   *   The route used to find active-trail of local tasks.
   *
   * @return array
   *   The task definition plugin ids for the route's active-trail.
   */
  protected function getActiveTrail(string $route_name): array {
    $ancestors = [];
    $definitions = $this->getDefinitions();
    // Build smaller arrays that are faster and easier to parse.
    $route_ids = array_keys($definitions);
    $route_names = array_combine($route_ids, array_column($definitions, 'route_name'));
    $route_parents = array_combine($route_ids, array_column($definitions, 'parent_id'));
    // Find all applicable plugin definition ids for this route_name.
    $route_name_options = array_keys($route_names, $route_name);
    // Use last available plugin definition match to find ancestors.
    if ($definition_id = end($route_name_options)) {
      // Find the deepest level of children for this route name.
      do {
        // Track previous definition IDs to not allow recursion.
        $definition_ids[] = $definition_id;
        // Find possible next definition ID.
        $definition_id = array_search($definition_id, $route_parents);
      } while (is_string($definition_id) && !in_array($definition_id, $definition_ids) && \array_key_exists($definition_id, $definitions) && $route_name == $definitions[$definition_id]['route_name']);
      // Start ancestors array with last-available non-NULL active task id
      // gathered from route and work upwards.
      $task_id = end($definition_ids);
      while (\array_key_exists($task_id, $definitions)) {
        if (!in_array($task_id, $ancestors)) {
          $ancestors[] = $task_id;
          $task_id = $definitions[$task_id]['parent_id'];
        }
        else {
          $task_id = NULL;
        }
      }
    }
    // Get active trail from furthest-up task_id.
    return array_reverse($ancestors);
  }

  /**
   * Find children for each plugin definition of a list of plugin definitions.
   *
   * @param array $definitions
   *   The plugin definitions list, 'parent_id' key is expected.
   *
   * @return array
   *   The modified plugin definitions list with children sub-arrays.
   */
  protected function generateChildrenForDefinitions(array $definitions): array {
    foreach ($definitions as $id => &$definition) {
      $parent_id = $definition['parent_id'];
      // Create children array on definition for uniformity.
      if (!array_key_exists('children', $definition)) {
        $definition['children'] = [];
      }
      // If the definition isn't the parent of itself, add a
      // definition pointer to the parent's child array.
      if (isset($definitions[$parent_id]) && $parent_id !== $id) {
        $definitions[$parent_id]['children'][$id] = &$definition;
        // Fill in the base_route from the parent route to insure consistency.
        $definition['base_route'] = $definitions[$parent_id]['route_name'];
      }
    }
    return $definitions;
  }

  /**
   * Builds a nested tree of plugin definitions based on the active_trail.
   *
   * (specific task definition plugins).
   *
   * @param array $active_trail
   *   An array of local task definition plugin IDs used to build a nested tree.
   *
   * @return array
   *   A nested menu tree array, starting at the base_route.
   */
  protected function getTaskTreeByActiveTrail(array $active_trail): array {
    // Generate task tree from definitions.
    $definitions = $this->getDefinitions();
    $task_tree = $this->generateChildrenForDefinitions($definitions);
    // Get top-level parent plugin information.
    $active_top_level = current($active_trail);
    $base_route = ($active_top_level && array_key_exists($active_top_level, $definitions)) ? $definitions[$active_top_level]['base_route'] : NULL;
    // Unset if both not top-level active-trail and one of the following:
    // - Base route isn't set.
    // - Current task base_route isn't same as the active trail base route.
    // - Parent ID is set (making it an nested-level local task)
    foreach ($task_tree as $task_id => $task) {
      if ($active_top_level !== $task_id && (is_null($base_route) || $task['base_route'] !== $base_route || $task['parent_id'] !== NULL)) {
        unset($task_tree[$task_id]);
      }
    }
    // Remove unrelated children from tree.
    $task_tree_pointer = &$task_tree;
    foreach ($active_trail as $active_plugin_id) {
      foreach (array_keys($task_tree_pointer) as $task_tree_key) {
        if ($task_tree_key !== $active_plugin_id) {
          $task_tree_pointer[$task_tree_key]['children'] = [];
        }
      }
      // Exit if current active trail plugin id doesn't exist in current tree.
      if (!array_key_exists($active_plugin_id, $task_tree_pointer)) {
        break;
      }
      // Move down a level in the tree.
      $task_tree_pointer = &$task_tree_pointer[$active_plugin_id]['children'];
    }
    return $task_tree;
  }

  /**
   * Return an array containing tree and breadcrumb data for the given route.
   *
   * @param string $route_name
   *   The current route to retrieve route breadcrumb and tree data array.
   *
   * @return array
   *   An array containing
   *   - tree: The current route's task tree.
   *   - active_trail: The current route's active task ids.
   */
  protected function getTaskTreeData(string $route_name): array {
    if ($cache = $this->cacheBackend->get($this->cacheKey . ':' . $route_name)) {
      $data = $cache->data;
    }
    else {
      $active_trail = $this->getActiveTrail($route_name);
      $task_tree = $this->getTaskTreeByActiveTrail($active_trail);
      $data = [
        'tree' => $task_tree,
        'active_trail' => $active_trail,
      ];
      $this->cacheBackend->set($this->cacheKey . ':' . $route_name, $data, Cache::PERMANENT, $this->cacheTags);
    }
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function getLocalTasksForRoute($route_name) {
    if (!isset($this->instances[$route_name])) {
      $this->instances[$route_name] = [];
      $data = $this->getTaskTreeData($route_name);
      $task_tree = $data['tree'];
      $active_trail = $data['active_trail'];
      $levels = count($active_trail);
      // Convert the tree keyed by plugin IDs into a simple one with
      // integer depth.  Create instances for each plugin along the way.
      $task_tree_pointer = &$task_tree;
      foreach ($active_trail as $level => $active_plugin_id) {
        foreach (array_keys($task_tree_pointer) as $plugin_id) {
          // Create a plugin instance for each element of the hierarchy.
          $plugin = $this->createInstance($plugin_id);
          // Set parent-level active-trail plugins as active.
          if ($plugin_id == $active_plugin_id && ($level + 1) < $levels && $plugin instanceof LocalTaskDefault) {
            $plugin->setActive();
          }
          $this->instances[$route_name][$level][$plugin_id] = $plugin;
        }
        // Exit if current active trail plugin id doesn't exist in current tree.
        if (!array_key_exists($active_plugin_id, $task_tree_pointer)) {
          break;
        }
        // Move down a level in the task tree.
        $task_tree_pointer = &$task_tree_pointer[$active_plugin_id]['children'];
      }
      // Print children that aren't in active-trail.
      if (!empty($task_tree_pointer)) {
        $level = isset($level) ? $level + 1 : 0;
        foreach (array_keys($task_tree_pointer) as $plugin_id) {
          // Create a plugin instance for each element of the hierarchy.
          $plugin = $this->createInstance($plugin_id);
          $this->instances[$route_name][$level][$plugin_id] = $plugin;
        }
      }
    }
    return $this->instances[$route_name];
  }

  /**
   * {@inheritdoc}
   */
  public function getTasksBuild($current_route_name, RefinableCacheableDependencyInterface &$cacheability) {
    $tree = $this->getLocalTasksForRoute($current_route_name);
    $build = [];

    // Collect all route names.
    $route_names = [];
    foreach ($tree as $instances) {
      foreach ($instances as $child) {
        $route_names[] = $child->getRouteName();
      }
    }
    // Pre-fetch all routes involved in the tree. This reduces the number
    // of SQL queries that would otherwise be triggered by the access manager.
    if ($route_names) {
      $this->routeProvider->getRoutesByNames($route_names);
    }

    foreach ($tree as $level => $instances) {
      /** @var \Drupal\Core\Menu\LocalTaskInterface[] $instances */
      foreach ($instances as $plugin_id => $child) {
        $route_name = $child->getRouteName();
        $route_parameters = $child->getRouteParameters($this->routeMatch);

        // Given that the active flag depends on the route we have to add the
        // route cache context.
        $cacheability->addCacheContexts(['route']);
        $active = $this->isRouteActive($current_route_name, $route_name, $route_parameters);

        // The plugin may have been set active in getLocalTasksForRoute() if
        // one of its child tabs is the active tab.
        $active = $active || $child->getActive();
        // @todo It might make sense to use link render elements instead.
        $link = [
          'title' => $this->getTitle($child),
          'url' => Url::fromRoute($route_name, $route_parameters),
          'localized_options' => $child->getOptions($this->routeMatch),
        ];
        $access = $this->accessManager->checkNamedRoute($route_name, $route_parameters, $this->account, TRUE);
        $build[$level][$plugin_id] = [
          '#theme' => 'menu_local_task',
          '#link' => $link,
          '#active' => $active,
          '#weight' => $child->getWeight(),
          '#access' => $access,
        ];
        $cacheability->addCacheableDependency($access)->addCacheableDependency($child);
      }
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getLocalTasks($route_name, $level = 0) {
    if (!isset($this->taskData[$route_name])) {
      $cacheability = new CacheableMetadata();
      $cacheability->addCacheContexts(['route']);
      // Look for route-based tabs.
      $this->taskData[$route_name] = [
        'tabs' => [],
        'cacheability' => $cacheability,
      ];

      if (!$this->requestStack->getCurrentRequest()->attributes->has('exception')) {
        // Safe to build tasks only when no exceptions raised.
        $data = [];
        $local_tasks = $this->getTasksBuild($route_name, $cacheability);
        foreach ($local_tasks as $tab_level => $items) {
          $data[$tab_level] = empty($data[$tab_level]) ? $items : array_merge($data[$tab_level], $items);
        }
        $this->taskData[$route_name]['tabs'] = $data;
        // Allow modules to alter local tasks.
        $this->moduleHandler->alter('menu_local_tasks', $this->taskData[$route_name], $route_name, $cacheability);
        $this->taskData[$route_name]['cacheability'] = $cacheability;
      }
    }

    if (isset($this->taskData[$route_name]['tabs'][$level])) {
      return [
        'tabs' => $this->taskData[$route_name]['tabs'][$level],
        'route_name' => $route_name,
        'cacheability' => $this->taskData[$route_name]['cacheability'],
      ];
    }

    return [
      'tabs' => [],
      'route_name' => $route_name,
      'cacheability' => $this->taskData[$route_name]['cacheability'],
    ];
  }

  /**
   * Determines whether the route of a certain local task is currently active.
   *
   * @param string $current_route_name
   *   The route name of the current main request.
   * @param string $route_name
   *   The route name of the local task to determine the active status.
   * @param array $route_parameters
   *   The parameter for the route.
   *
   * @return bool
   *   Returns TRUE if the passed route_name and route_parameters is considered
   *   as the same as the one from the request, otherwise FALSE.
   */
  protected function isRouteActive($current_route_name, $route_name, $route_parameters) {
    // Flag the list element as active if this tab's route and parameters match
    // the current request's route and route variables.
    $active = $current_route_name == $route_name;
    if ($active) {
      // The request is injected, so we need to verify that we have the expected
      // _raw_variables attribute.
      $raw_variables_bag = $this->routeMatch->getRawParameters();
      // If we don't have _raw_variables, we assume the attributes are still the
      // original values.
      $raw_variables = $raw_variables_bag ? $raw_variables_bag->all() : $this->routeMatch->getParameters()->all();
      $active = array_intersect_assoc($route_parameters, $raw_variables) == $route_parameters;
    }
    return $active;
  }

}
