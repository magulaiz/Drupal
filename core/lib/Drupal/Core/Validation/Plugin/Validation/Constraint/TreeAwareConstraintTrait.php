<?php

declare(strict_types = 1);

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Drupal\Core\Config\Schema\ArrayElement;
use Drupal\Core\Config\Schema\Element;

/**
 * Helper methods for tree-aware validation constraints.
 */
trait TreeAwareConstraintTrait {

  /**
   * Find the parent property.
   *
   * @return \Drupal\Core\Config\Schema\Element
   *   The parent property.
   */
  private function getParentProperty(): Element {
    $parent_property_path = array_slice(explode('.', $this->context->getPropertyPath()), 0, -1);
    return self::findPropertyForPath($this->context->getRoot(), $parent_property_path);
  }

  /**
   * Finds the specified property path in the given tree.
   *
   * @todo consider adopting Symfony's PropertyAccess component.
   *
   * @param \Drupal\Core\Config\Schema\ArrayElement $tree
   *   A config schema (sub)tree.
   * @param string[] $property_path
   *   A property path, in array form.
   *
   * @return \Drupal\Core\Config\Schema\Element
   *   The element found at the specified property path.
   *
   * @throws \OutOfRangeException
   *   When requesting a non-existent property path.
   */
  private static function findPropertyForPath(ArrayElement $tree, array $property_path): Element {
    // Edge case: root is requested.
    if (empty($property_path)) {
      return $tree;
    }

    $elements = $tree->getElements();
    $name = array_shift($property_path);

    // Check if it exists.
    if (!isset($elements[$name])) {
      throw new \OutOfRangeException();
    }

    return self::findPropertyForPath($elements[$name], $property_path);
  }

}
