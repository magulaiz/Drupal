<?php

declare(strict_types = 1);

namespace Drupal\TestTools;

/**
 * Helper methods related to class hierarchies.
 */
class ClassHierarchyHelper {

  /**
   * Collects property values from classes in a hierarchy.
   *
   * @param class-string $class
   *   Class name.
   * @param string $property
   *   Property name.
   *
   * @return list<mixed>
   *   List of property values from classes in the hierarchy, parents first.
   */
  public static function collectPropertyValues(string $class, string $property): array {
    $rc = new \ReflectionClass($class);
    $values = [];
    while ($rc && $rc->hasProperty($property)) {
      $rp = $rc->getProperty($property);
      $rc = $rp->getDeclaringClass()->getParentClass();
      $values[] = $rp->getValue();
    }
    // Reorder so that parent properties are first.
    return array_reverse($values);
  }

}
