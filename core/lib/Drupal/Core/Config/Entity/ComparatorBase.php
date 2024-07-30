<?php

declare(strict_types=1);

namespace Drupal\Core\Config\Entity;

class ComparatorBase implements ComparatorInterface {

  /**
   * {@inheritdoc}
   */
  public function isEquivalent(array $a, array $b): bool {
    static::prepare($a);
    static::prepare($b);

    return $a == $b;
  }

  protected static function prepare(array &$data): void {
    unset($data['uuid'], $data['_core']);

    if (empty($data['dependencies'])) {
      unset($data['dependencies']);
    }

    // Ensure we don't get a false mismatch due to differing key order.
    // @todo When https://www.drupal.org/project/drupal/issues/3230826 is
    //   fixed in core, use that API instead to sort the config data.
    static::recursiveSortByKey($data);
  }

  protected static function recursiveSortByKey(array &$data): void {
    // If the array is a list, it is by definition already sorted.
    if (!array_is_list($data)) {
      ksort($data);
    }
    foreach ($data as &$value) {
      if (is_array($value)) {
        static::recursiveSortByKey($value);
      }
    }
  }

}
