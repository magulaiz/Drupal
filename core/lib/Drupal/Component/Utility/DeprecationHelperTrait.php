<?php

declare(strict_types=1);

namespace Drupal\Component\Utility;

/**
 * Provides a helper method for handling deprecated code paths in projects.
 */
trait DeprecationHelperTrait {

  /**
   * Handles removal of a deprecated parameter from a class constructor.
   *
   * @param array $args
   *   The arguments passed to the constructor.
   * @param int $position
   *   The position where the deprecated parameter may exist.
   * @param string $deprecatedClassName
   *   The class of the deprecated parameter.
   * @param string $message
   *   The deprecation message.
   *
   * @throws \ReflectionException
   *   Thrown when the function does not exist.
   */
  protected function removeDeprecatedParam(array $args, int $position, string $deprecatedClassName, string $message): void {
    if (!$args[$position] instanceof $deprecatedClassName) {
      return;
    }

    @trigger_error($message, E_USER_DEPRECATED);
    $function = new \ReflectionMethod($this, '__construct');
    $params = $function->getParameters();

    // Reassign the values of each parameter after the deprecated parameter.
    foreach (array_slice($params, $position, NULL, TRUE) as $i => $paramValue) {
      $paramName = $paramValue->getName();
      // @todo What if it doesn't exist? We may still want the value in the
      //   constructor. Maybe throw an exception and limit this to constructors
      //   where all params map to properties.
      if (property_exists($this, $paramName)) {
        $this->$paramName = $args[$i + 1];
      }
    }
  }

}
