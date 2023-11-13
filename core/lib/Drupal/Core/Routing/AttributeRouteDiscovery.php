<?php

namespace Drupal\Core\Routing;

use Symfony\Component\Routing\Annotation\Route as RouteAttribute;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Discovers routes using Symfony's Route attribute.
 *
 * @see \Symfony\Component\Routing\Annotation\Route
 */
class AttributeRouteDiscovery extends AbstractStaticRouteDiscovery {

  /**
   * The default route index used when creating default route names.
   */
  protected int $defaultRouteIndex = 0;

  /**
   * The PHP attribute class.
   */
  protected string $routeAttributeClass = RouteAttribute::class;

  /**
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   */
  public function __construct(protected \Traversable $namespaces) {
  }

  /**
   * {@inheritdoc}
   */
  protected static function getPriority(): int {
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  protected function collectRoutes(): iterable {
    foreach ($this->namespaces as $namespace => $directory) {
      $directory .= '/Controller';
      $namespace .= '\\Controller';
      if (is_dir($directory)) {
        $iterator = new \RecursiveIteratorIterator(
          new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($iterator as $fileinfo) {
          if ($fileinfo->getExtension() == 'php') {
            $sub_path = $iterator->getSubIterator()->getSubPath();
            $sub_path = $sub_path ? str_replace(DIRECTORY_SEPARATOR, '\\', $sub_path) . '\\' : '';
            $class = $namespace . '\\' . $sub_path . $fileinfo->getBasename('.php');
            yield $this->createRouteCollection($class);
          }
        }
      }
    }
  }

  /**
   * Creates a route collection from a class's attributed methods.
   *
   * @param class-string $class
   *   The class to generate a route collection for.
   *
   * @return \Symfony\Component\Routing\RouteCollection
   *   The route collection.
   */
  private function createRouteCollection(string $class): RouteCollection {
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

    foreach ($class->getMethods() as $method) {
      $this->defaultRouteIndex = 0;
      foreach ($this->getAttributes($method) as $attribute) {
        $this->addRoute($collection, $attribute, $globals, $class, $method);
      }
    }

    // See https://symfony.com/doc/current/controller/service.html#invokable-controllers.
    if (0 === $collection->count() && $class->hasMethod('__invoke')) {
      $globals = $this->resetGlobals();
      foreach ($this->getAttributes($class) as $attribute) {
        $this->addRoute($collection, $attribute, $globals, $class, $class->getMethod('__invoke'));
      }
    }

    return $collection;
  }

  /**
   * Creates the default route settings for a class.
   *
   * A class can use the route attribute on the class to set defaults for all
   * attributed methods on the class.
   *
   * @param \ReflectionClass $class
   *   The class to create global settings for.
   *
   * @return array
   *   An array of route defaults.
   */
  private function getGlobals(\ReflectionClass $class) {
    $globals = $this->resetGlobals();

    $attribute = ($class->getAttributes($this->routeAttributeClass, \ReflectionAttribute::IS_INSTANCEOF)[0] ?? NULL)?->newInstance();
    if ($attribute) {
      if (NULL !== $attribute->getName()) {
        $globals['name'] = $attribute->getName();
      }

      if (NULL !== $attribute->getPath()) {
        $globals['path'] = $attribute->getPath();
      }

      $globals['localized_paths'] = $attribute->getLocalizedPaths();
      if (!empty($globals['localized_paths'])) {
        throw new UnsupportedRouteAttributePropertyException(sprintf('The "%s" route attribute does not support arrays in class "%s"', "path", $class->getName()));
      }

      if (NULL !== $attribute->getRequirements()) {
        $globals['requirements'] = $attribute->getRequirements();
      }

      if (NULL !== $attribute->getOptions()) {
        $globals['options'] = $attribute->getOptions();
      }

      if (NULL !== $attribute->getDefaults()) {
        $globals['defaults'] = $attribute->getDefaults();
        if (!empty($attribute->getDefaults()['_locale'])) {
          throw new UnsupportedRouteAttributePropertyException(sprintf('The "%s" route attribute is not supported in class "%s""', "locale", $class->getName()));
        }
      }

      if (NULL !== $attribute->getSchemes()) {
        $globals['schemes'] = $attribute->getSchemes();
      }

      if (NULL !== $attribute->getMethods()) {
        $globals['methods'] = $attribute->getMethods();
      }

      if (NULL !== $attribute->getHost()) {
        $globals['host'] = $attribute->getHost();
      }

      if (NULL !== $attribute->getCondition()) {
        $globals['condition'] = $attribute->getCondition();
      }

      $globals['priority'] = $attribute->getPriority() ?? 0;

      foreach ($globals['requirements'] as $placeholder => $requirement) {
        if (\is_int($placeholder)) {
          throw new \InvalidArgumentException(sprintf('A placeholder name must be a string (%d given). Did you forget to specify the placeholder key for the requirement "%s" in "%s"?', $placeholder, $requirement, $class->getName()));
        }
      }
    }

    return $globals;
  }

  /**
   * Adds a route to the provided route collection.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   The route collection to add the route to.
   * @param \Symfony\Component\Routing\Annotation\Route $attribute
   *   The attribute object that describes the route.
   * @param array $globals
   *   The defaults for the class.
   * @param \ReflectionClass $class
   *   The class.
   * @param \ReflectionMethod $method
   *   The attributed method.
   */
  private function addRoute(RouteCollection $collection, RouteAttribute $attribute, array $globals, \ReflectionClass $class, \ReflectionMethod $method) {
    $name = $attribute->getName() ?? $this->getDefaultRouteName($class, $method);
    $name = $globals['name'] . $name;

    if (!empty($attribute->getLocalizedPaths())) {
      throw new UnsupportedRouteAttributePropertyException(sprintf('The "%s" route attribute does not support arrays on route "%s" in "%s::%s()"', "path", $name, $class->getName(), $method->getName()));
    }
    if (!empty($attribute->getDefaults()['_locale'])) {
      throw new UnsupportedRouteAttributePropertyException(sprintf('The "%s" route attribute is not supported on route "%s" in "%s::%s()"', "locale", $name, $class->getName(), $method->getName()));
    }

    $requirements = $attribute->getRequirements();

    foreach ($requirements as $placeholder => $requirement) {
      if (\is_int($placeholder)) {
        throw new \InvalidArgumentException(sprintf('A placeholder name must be a string (%d given). Did you forget to specify the placeholder key for the requirement "%s" of route "%s" in "%s::%s()"?', $placeholder, $requirement, $name, $class->getName(), $method->getName()));
      }
    }

    $defaults = array_replace($globals['defaults'], $attribute->getDefaults());
    $requirements = array_replace($globals['requirements'], $requirements);
    $options = array_replace($globals['options'], $attribute->getOptions());
    $schemes = array_merge($globals['schemes'], $attribute->getSchemes());
    $methods = array_merge($globals['methods'], $attribute->getMethods());

    $host = $attribute->getHost() ?? $globals['host'];
    $condition = $attribute->getCondition() ?? $globals['condition'];
    $priority = $attribute->getPriority() ?? $globals['priority'];

    $path = $attribute->getPath();
    $prefix = $globals['path'];

    $route = $this->createRoute($prefix . $path, $defaults, $requirements, $options, $host, $schemes, $methods, $condition);
    $this->configureRoute($route, $class, $method);
    $collection->add($name, $route, $priority);
  }

  /**
   * Gets the default route name for a class method.
   *
   * @param \ReflectionClass $class
   *   The class.
   * @param \ReflectionMethod $method
   *   The method.
   *
   * @return string
   *   The default route name for a class method.
   */
  private function getDefaultRouteName(\ReflectionClass $class, \ReflectionMethod $method): string {
    $name = str_replace('\\', '_', $class->name) . '_' . $method->name;
    $name = preg_match('//u', $name) ? mb_strtolower($name, 'UTF-8') : strtolower($name);
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
   * @return iterable<int, RouteAttribute>
   */
  private function getAttributes(object $reflection): iterable {
    foreach ($reflection->getAttributes($this->routeAttributeClass, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
      yield $attribute->newInstance();
    }
  }

  /**
   * Configures the _controller default parameter of a given Route instance.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The route to configure.
   * @param \ReflectionClass $class
   *   The class.
   * @param \ReflectionMethod $method
   *   The method.
   */
  private function configureRoute(Route $route, \ReflectionClass $class, \ReflectionMethod $method) {
    if ('__invoke' === $method->getName()) {
      $route->setDefault('_controller', $class->getName());
    }
    else {
      $route->setDefault('_controller', $class->getName() . '::' . $method->getName());
    }
  }

}
