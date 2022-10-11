<?php

namespace Drupal\Core\Routing;

use Drupal\Core\Access\CheckProviderInterface;
use Drupal\Core\Controller\ControllerResolverInterface;
use Drupal\Core\Discovery\YamlDiscovery;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\DestructableInterface;
use Drupal\Component\EventDispatcher\Event;
use Symfony\Component\Routing\Annotation\Route as RouteAnnotation;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

/**
 * Managing class for rebuilding the router table.
 */
class RouteBuilder implements RouteBuilderInterface, DestructableInterface {

  /**
   * The dumper to which we should send collected routes.
   *
   * @var \Drupal\Core\Routing\MatcherDumperInterface
   */
  protected $dumper;

  /**
   * The used lock backend instance.
   *
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  protected $lock;

  /**
   * The event dispatcher to notify of routes.
   *
   * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The controller resolver.
   *
   * @var \Drupal\Core\Controller\ControllerResolverInterface
   */
  protected $controllerResolver;

  /**
   * The route collection during the rebuild.
   *
   * @var \Symfony\Component\Routing\RouteCollection
   */
  protected $routeCollection;

  /**
   * Flag that indicates if we are currently rebuilding the routes.
   *
   * @var bool
   */
  protected $building = FALSE;

  /**
   * Flag that indicates if we should rebuild at the end of the request.
   *
   * @var bool
   */
  protected $rebuildNeeded = FALSE;

  /**
   * The check provider.
   *
   * @var \Drupal\Core\Access\CheckProviderInterface
   */
  protected $checkProvider;

  /**
   * @var int
   */
  protected int $defaultRouteIndex = 0;

  /**
   * @var string
   */
  protected string $routeAnnotationClass = RouteAnnotation::class;

  /**
   * @var string|null
   */
  protected ?string $env = NULL;

  /**
   * Constructs the RouteBuilder using the passed MatcherDumperInterface.
   *
   * @param \Drupal\Core\Routing\MatcherDumperInterface $dumper
   *   The matcher dumper used to store the route information.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   The lock backend.
   * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $dispatcher
   *   The event dispatcher to notify of routes.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Controller\ControllerResolverInterface $controller_resolver
   *   The controller resolver.
   * @param \Drupal\Core\Access\CheckProviderInterface $check_provider
   *   The check provider.
   */
  public function __construct(MatcherDumperInterface $dumper, LockBackendInterface $lock, EventDispatcherInterface $dispatcher, ModuleHandlerInterface $module_handler, ControllerResolverInterface $controller_resolver, CheckProviderInterface $check_provider) {
    $this->dumper = $dumper;
    $this->lock = $lock;
    $this->dispatcher = $dispatcher;
    $this->moduleHandler = $module_handler;
    $this->controllerResolver = $controller_resolver;
    $this->checkProvider = $check_provider;
  }

  /**
   * {@inheritdoc}
   */
  public function setRebuildNeeded() {
    $this->rebuildNeeded = TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function rebuild() {
    if ($this->building) {
      throw new \RuntimeException('Recursive router rebuild detected.');
    }

    if (!$this->lock->acquire('router_rebuild')) {
      // Wait for another request that is already doing this work.
      // We choose to block here since otherwise the routes might not be
      // available, resulting in a 404.
      $this->lock->wait('router_rebuild');
      return FALSE;
    }

    $this->building = TRUE;

    $collection = new RouteCollection();
    foreach ($this->getRouteDefinitions() as $routes) {
      // The top-level 'routes_callback' is a list of methods in controller
      // syntax, see \Drupal\Core\Controller\ControllerResolver. These methods
      // should return a set of \Symfony\Component\Routing\Route objects, either
      // in an associative array keyed by the route name, which will be iterated
      // over and added to the collection for this provider, or as a new
      // \Symfony\Component\Routing\RouteCollection object, which will be added
      // to the collection.
      if (isset($routes['route_callbacks'])) {
        foreach ($routes['route_callbacks'] as $route_callback) {
          $callback = $this->controllerResolver->getControllerFromDefinition($route_callback);
          if ($callback_routes = call_user_func($callback)) {
            // If a RouteCollection is returned, add the whole collection.
            if ($callback_routes instanceof RouteCollection) {
              $collection->addCollection($callback_routes);
            }
            // Otherwise, add each Route object individually.
            else {
              foreach ($callback_routes as $name => $callback_route) {
                $collection->add($name, $callback_route);
              }
            }
          }
        }
        unset($routes['route_callbacks']);
      }
      foreach ($routes as $name => $route_info) {
        $route_info += $this->resetGlobals();

        $route = $this->createRoute($route_info['path'], $route_info['defaults'], $route_info['requirements'], $route_info['options'], $route_info['host'], $route_info['schemes'], $route_info['methods'], $route_info['condition']);
        $collection->add($name, $route);
      }
    }
    $this->collectAttributedRoutes($collection);

    // DYNAMIC is supposed to be used to add new routes based upon all the
    // static defined ones.
    $this->dispatcher->dispatch(new RouteBuildEvent($collection), RoutingEvents::DYNAMIC);

    // ALTER is the final step to alter all the existing routes. We cannot stop
    // people from adding new routes here, but we define two separate steps to
    // make it clear.
    $this->dispatcher->dispatch(new RouteBuildEvent($collection), RoutingEvents::ALTER);

    $this->checkProvider->setChecks($collection);

    $this->dumper->addRoutes($collection);
    $this->dumper->dump();

    $this->lock->release('router_rebuild');
    $this->dispatcher->dispatch(new Event(), RoutingEvents::FINISHED);
    $this->building = FALSE;

    $this->rebuildNeeded = FALSE;

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function rebuildIfNeeded() {
    if ($this->rebuildNeeded) {
      return $this->rebuild();
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function destruct() {
    // Rebuild routes only once at the end of the request lifecycle to not
    // trigger multiple rebuilds and also make the page more responsive for the
    // user.
    $this->rebuildIfNeeded();
  }

  /**
   * Retrieves all defined routes from .routing.yml files.
   *
   * @return array
   *   The defined routes, keyed by provider.
   */
  protected function getRouteDefinitions() {
    // Always instantiate a new YamlDiscovery object so that we always search on
    // the up-to-date list of modules.
    $discovery = new YamlDiscovery('routing', $this->moduleHandler->getModuleDirectories());
    return $discovery->findAll();
  }

  private function collectAttributedRoutes(RouteCollection $collection) {
    // $loader = new AttributeLoader();
    foreach (\Drupal::getContainer()->getParameter('container.namespaces') as $namespace => $directory) {
      $directory .= '/Controller';
      $namespace .= '\Controller';
      if (is_dir($directory)) {
        $iterator = new \RecursiveIteratorIterator(
          new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($iterator as $fileinfo) {
          if ($fileinfo->getExtension() == 'php') {
            $sub_path = $iterator->getSubIterator()->getSubPath();
            $sub_path = $sub_path ? str_replace(DIRECTORY_SEPARATOR, '\\', $sub_path) . '\\' : '';
            $class = $namespace . '\\' . $sub_path . $fileinfo->getBasename('.php');
            $collection->addCollection($this->load($class));
          }
        }
      }
    }
  }

  private function load(string $class): RouteCollection {
    $collection = new RouteCollection();

    if (!class_exists($class)) {
      // In Symfony code this triggers an exception. It is removed here because
      // Drupal already has traits and other things in this folder.
      // Alternatively, we could remove this if clause and then check what the
      // resulting reflection object is.
      return $collection;
    }
    $class = new \ReflectionClass($class);
    if ($class->isAbstract()) {
      return $collection;
    }

    $globals = $this->getGlobals($class);

    if ($globals['env'] && $this->env !== $globals['env']) {
      return $collection;
    }

    foreach ($class->getMethods() as $method) {
      $this->defaultRouteIndex = 0;
      foreach ($this->getAnnotations($method) as $annot) {
        $this->addRoute($collection, $annot, $globals, $class, $method);
      }
    }

    if (0 === $collection->count() && $class->hasMethod('__invoke')) {
      $globals = $this->resetGlobals();
      foreach ($this->getAnnotations($class) as $annot) {
        $this->addRoute($collection, $annot, $globals, $class, $class->getMethod('__invoke'));
      }
    }

    return $collection;
  }

  private function getGlobals(\ReflectionClass $class) {
    $globals = $this->resetGlobals();

    $annot = NULL;
    if ($attribute = $class->getAttributes($this->routeAnnotationClass, \ReflectionAttribute::IS_INSTANCEOF)[0] ?? NULL) {
      $annot = $attribute->newInstance();
    }

    if ($annot) {
      if (NULL !== $annot->getName()) {
        $globals['name'] = $annot->getName();
      }

      if (NULL !== $annot->getPath()) {
        $globals['path'] = $annot->getPath();
      }

      $globals['localized_paths'] = $annot->getLocalizedPaths();

      if (NULL !== $annot->getRequirements()) {
        $globals['requirements'] = $annot->getRequirements();
      }

      if (NULL !== $annot->getOptions()) {
        $globals['options'] = $annot->getOptions();
      }

      if (NULL !== $annot->getDefaults()) {
        $globals['defaults'] = $annot->getDefaults();
      }

      if (NULL !== $annot->getSchemes()) {
        $globals['schemes'] = $annot->getSchemes();
      }

      if (NULL !== $annot->getMethods()) {
        $globals['methods'] = $annot->getMethods();
      }

      if (NULL !== $annot->getHost()) {
        $globals['host'] = $annot->getHost();
      }

      if (NULL !== $annot->getCondition()) {
        $globals['condition'] = $annot->getCondition();
      }

      $globals['priority'] = $annot->getPriority() ?? 0;
      $globals['env'] = $annot->getEnv();

      foreach ($globals['requirements'] as $placeholder => $requirement) {
        if (\is_int($placeholder)) {
          throw new \InvalidArgumentException(sprintf('A placeholder name must be a string (%d given). Did you forget to specify the placeholder key for the requirement "%s" in "%s"?', $placeholder, $requirement, $class->getName()));
        }
      }
    }

    return $globals;
  }

  private function resetGlobals(): array {
    return [
      'path' => NULL,
      'localized_paths' => [],
      'requirements' => [],
      'options' => [],
      'defaults' => [],
      'schemes' => [],
      'methods' => [],
      'host' => '',
      'condition' => '',
      'name' => '',
      'priority' => 0,
      'env' => NULL,
    ];
  }

  private function addRoute(RouteCollection $collection, object $annot, array $globals, \ReflectionClass $class, \ReflectionMethod $method) {
    if ($annot->getEnv() && $annot->getEnv() !== $this->env) {
      return;
    }

    $name = $annot->getName() ?? $this->getDefaultRouteName($class, $method);
    $name = $globals['name'] . $name;

    $requirements = $annot->getRequirements();

    foreach ($requirements as $placeholder => $requirement) {
      if (\is_int($placeholder)) {
        throw new \InvalidArgumentException(sprintf('A placeholder name must be a string (%d given). Did you forget to specify the placeholder key for the requirement "%s" of route "%s" in "%s::%s()"?', $placeholder, $requirement, $name, $class->getName(), $method->getName()));
      }
    }

    $defaults = array_replace($globals['defaults'], $annot->getDefaults());
    $requirements = array_replace($globals['requirements'], $requirements);
    $options = array_replace($globals['options'], $annot->getOptions());
    $schemes = array_merge($globals['schemes'], $annot->getSchemes());
    $methods = array_merge($globals['methods'], $annot->getMethods());

    $host = $annot->getHost() ?? $globals['host'];
    $condition = $annot->getCondition() ?? $globals['condition'];
    $priority = $annot->getPriority() ?? $globals['priority'];

    $path = $annot->getLocalizedPaths() ?: $annot->getPath();
    $prefix = $globals['localized_paths'] ?: $globals['path'];
    $paths = [];

    if (\is_array($path)) {
      if (!\is_array($prefix)) {
        foreach ($path as $locale => $localePath) {
          $paths[$locale] = $prefix . $localePath;
        }
      }
      elseif ($missing = array_diff_key($prefix, $path)) {
        throw new \LogicException(sprintf('Route to "%s" is missing paths for locale(s) "%s".', $class->name . '::' . $method->name, implode('", "', array_keys($missing))));
      }
      else {
        foreach ($path as $locale => $localePath) {
          if (!isset($prefix[$locale])) {
            throw new \LogicException(sprintf('Route to "%s" with locale "%s" is missing a corresponding prefix in class "%s".', $method->name, $locale, $class->name));
          }

          $paths[$locale] = $prefix[$locale] . $localePath;
        }
      }
    }
    elseif (\is_array($prefix)) {
      foreach ($prefix as $locale => $localePrefix) {
        $paths[$locale] = $localePrefix . $path;
      }
    }
    else {
      $paths[] = $prefix . $path;
    }

    foreach ($method->getParameters() as $param) {
      if (isset($defaults[$param->name]) || !$param->isDefaultValueAvailable()) {
        continue;
      }
      foreach ($paths as $locale => $path) {
        if (preg_match(sprintf('/\{%s(?:<.*?>)?\}/', preg_quote($param->name)), $path)) {
          $defaults[$param->name] = $param->getDefaultValue();
          break;
        }
      }
    }

    foreach ($paths as $locale => $path) {
      $route = $this->createRoute($path, $defaults, $requirements, $options, $host, $schemes, $methods, $condition);
      $this->configureRoute($route, $class, $method, $annot);
      if (0 !== $locale) {
        $route->setDefault('_locale', $locale);
        $route->setRequirement('_locale', preg_quote($locale));
        $route->setDefault('_canonical_route', $name);
        $collection->add($name . '.' . $locale, $route, $priority);
      }
      else {
        $collection->add($name, $route, $priority);
      }
    }
  }

  /**
   * Gets the default route name for a class method.
   */
  private function getDefaultRouteName(\ReflectionClass $class, \ReflectionMethod $method): string {
    $name = str_replace('\\', '_', $class->name) . '_' . $method->name;
    $name = \function_exists('mb_strtolower') && preg_match('//u', $name) ? mb_strtolower($name, 'UTF-8') : strtolower($name);
    if ($this->defaultRouteIndex > 0) {
      $name .= '_' . $this->defaultRouteIndex;
    }
    ++$this->defaultRouteIndex;

    return $name;
  }

  /**
   * Gets the PHP attributes.
   *
   * @param \ReflectionClass|\ReflectionMethod $reflection
   *   The reflected class or method.
   *
   * @return iterable<int, RouteAnnotation>
   */
  private function getAnnotations(object $reflection): iterable {
    foreach ($reflection->getAttributes($this->routeAnnotationClass, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
      yield $attribute->newInstance();
    }
  }

  private function createRoute(string $path, array $defaults, array $requirements, array $options, ?string $host, array $schemes, array $methods, ?string $condition): Route {
    // Ensure routes default to using Drupal's route compiler instead of
    // Symfony's.
    $options += [
      'compiler_class' => RouteCompiler::class,
    ];
    return new Route($path, $defaults, $requirements, $options, $host, $schemes, $methods, $condition);
  }

  /**
   * Configures the _controller default parameter of a given Route instance.
   */
  private function configureRoute(Route $route, \ReflectionClass $class, \ReflectionMethod $method, object $annot) {
    if ('__invoke' === $method->getName()) {
      $route->setDefault('_controller', $class->getName());
    }
    else {
      $route->setDefault('_controller', $class->getName() . '::' . $method->getName());
    }
  }

}
