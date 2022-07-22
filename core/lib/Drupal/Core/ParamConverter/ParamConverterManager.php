<?php

namespace Drupal\Core\ParamConverter;

use Drupal\Core\Routing\RouteObjectInterface;
use Symfony\Component\Routing\RouteCollection;

/**
 * Manages converter services for converting request parameters to full objects.
 *
 * A typical use case for this would be upcasting (converting) a node id to a
 * node entity.
 */
class ParamConverterManager implements ParamConverterManagerInterface {

  /**
   * Array of loaded converter services keyed by their ids.
   *
   * @var array
   */
  protected $converters = [];

  /**
   * Stores already upcasted parameters in the current request.
   *
   * @var array
   */
  protected $cached = [];

  /**
   * {@inheritdoc}
   */
  public function addConverter(ParamConverterInterface $param_converter, $id) {
    $this->converters[$id] = $param_converter;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getConverter($converter) {
    if (isset($this->converters[$converter])) {
      return $this->converters[$converter];
    }
    else {
      throw new \InvalidArgumentException(sprintf('No converter has been registered for %s', $converter));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setRouteParameterConverters(RouteCollection $routes) {
    foreach ($routes->all() as $route) {
      if (!$parameters = $route->getOption('parameters')) {
        // Continue with the next route if no parameters have been defined.
        continue;
      }

      // Loop over all defined parameters and look up the right converter.
      foreach ($parameters as $name => &$definition) {
        if (isset($definition['converter'])) {
          // Skip parameters that already have a manually set converter.
          continue;
        }

        foreach (array_keys($this->converters) as $converter) {
          if ($this->getConverter($converter)->applies($definition, $name, $route)) {
            $definition['converter'] = $converter;
            break;
          }
        }
      }

      // Override the parameters array.
      $route->setOption('parameters', $parameters);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function convert(array $defaults) {
    /** @var \Symfony\Component\Routing\Route $route */
    $route = $defaults[RouteObjectInterface::ROUTE_OBJECT];

    // Skip this enhancer if there are no parameter definitions.
    if (!$parameters = $route->getOption('parameters')) {
      return $defaults;
    }

    // Invoke the registered converter for each parameter.
    foreach ($parameters as $name => $definition) {
      if (!isset($defaults[$name])) {
        // Do not try to convert anything that is already set to NULL.
        continue;
      }

      if (!isset($definition['converter'])) {
        // Continue if no converter has been specified.
        continue;
      }

      // If a converter returns NULL it means that the parameter could not be
      // converted.
      $value = $defaults[$name];

      $cache_id = $this->generateParameterCacheId($name, $value, $definition, $defaults);
      if (array_key_exists($cache_id, $this->cached)) {
        $defaults[$name] = $this->cached[$cache_id];
      }
      else {
        $defaults[$name] = $this->getConverter($definition['converter'])->convert($value, $definition, $name, $defaults);
        if (isset($defaults[$name])) {
          $this->cached[$cache_id] = $defaults[$name];
        }
        else {
          $message = 'The "%s" parameter was not converted for the path "%s" (route name: "%s")';
          $route_name = $defaults[RouteObjectInterface::ROUTE_NAME];
          throw new ParamNotConvertedException(sprintf($message, $name, $route->getPath(), $route_name), 0, NULL, $route_name, [$name => $value]);
        }
      }

    }

    return $defaults;
  }

  /**
   * Generates a unique cache id for the given parameter upcast.
   *
   * @param string $name
   *   The name of the parameter.
   * @param mixed $value
   *   The raw value.
   * @param mixed $definition
   *   The parameter definition provided in the route options.
   * @param array $defaults
   *   The route defaults array.
   *
   * @return string
   *   The unique cache id.
   *
   * @throws \JsonException
   */
  private function generateParameterCacheId(string $name, $value, $definition, array $defaults): string {
    $cache_id_parts = [];
    $cache_id_parts[] = $name . '-' . (string) $value;
    $cache_id_parts[] = json_encode($definition, JSON_THROW_ON_ERROR);
    $cleaned_defaults = $defaults;
    // Deduplicate, remove the parameter in question, it is already part of
    // the cache id.
    unset($cleaned_defaults[$name]);
    // Remove internal properties.
    $cleaned_defaults = array_filter($cleaned_defaults, static function (string $key): bool {
      return strpos($key, '_') !== 0;
    }, ARRAY_FILTER_USE_KEY);
    $cache_id_parts[] = json_encode($cleaned_defaults, JSON_THROW_ON_ERROR);
    return md5(implode('.', $cache_id_parts));
  }

}
