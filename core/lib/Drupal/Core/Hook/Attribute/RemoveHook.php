<?php

declare(strict_types=1);

namespace Drupal\Core\Hook\Attribute;

use Drupal\Core\Hook\HookAttributeInterface;

/**
 * Attribute for removing an implementation.
 *
 * The effect of this attribute is independent from the specific class or method
 * on which it is placed.
 *
 * @internal
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class RemoveHook implements HookAttributeInterface {

  /**
   * Constructs a RemoveHook object.
   *
   * @param string $hook
   *   The hook name from which to remove the target hook listener.
   * @param class-string $class
   *   The class name of the target hook listener.
   * @param string $method
   *   The method name of the target hook listener.
   *   If the class instance itself is the listener, this should be '__invoke'.
   */
  public function __construct(
    public readonly string $hook,
    public readonly string $class,
    public readonly string $method,
  ) {}

}
