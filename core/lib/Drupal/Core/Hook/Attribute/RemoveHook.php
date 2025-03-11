<?php

declare(strict_types=1);

namespace Drupal\Core\Hook\Attribute;

use Drupal\Core\Hook\HookAttributeInterface;

/**
 * Attribute for removing an implementation.
 *
 * @internal
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class RemoveHook implements HookAttributeInterface {

  /**
   * Constructs a RemoveHook object.
   *
   * @param string $hook
   *   The hook parameter of the #Hook being modified.
   * @param class-string $class
   *   The class the implementation to modify is in.
   * @param string $method
   *   The method name of the #Hook being modified. If the hook attribute is
   *   on a class and does not have method set, then use __invoke.
   */
  public function __construct(
    public readonly string $hook,
    public readonly string $class,
    public readonly string $method,
  ) {}

}
