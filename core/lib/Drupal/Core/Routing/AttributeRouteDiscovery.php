<?php

namespace Drupal\Core\Routing;

use Symfony\Component\Routing\Annotation\Route as RouteAnnotation;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class AttributeRouteDiscovery extends AbstractRouteDiscovery {
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
   * @return iterable<int, \Symfony\Component\Routing\RouteCollection>
   */
  public function collectRoutes(): iterable {
    foreach (\Drupal::getContainer()->getParameter('container.namespaces') as $namespace => $directory) {
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
            yield $this->load($class);
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
