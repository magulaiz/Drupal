<?php

declare(strict_types = 1);

namespace Drupal\Core\Attribute\Hook;

/**
 * Attribute for alter hook implementations.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Alter extends Hook {

  /**
   * Constructor.
   *
   * @param string $type
   *   Alter type.
   * @param string|null $module
   *   Module, or NULL to use the module from class name or service tag.
   * @param int $weight
   *   Weight.
   * @param array|string|null $before
   *   Module(s) before which this implementation should run.
   * @param array|string|null $after
   *   Module(s) after which this implementation should run.
   */
  public function __construct(
    string $type,
    string $module = NULL,
    int $weight = 0,
    array|string $before = NULL,
    array|string $after = NULL,
  ) {
    parent::__construct(
      $type . '_alter',
      $module,
      $weight,
      $before,
      $after,
    );
  }

}
