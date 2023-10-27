<?php

declare(strict_types = 1);

namespace Drupal\Core\Extension\Hook\SingleModuleCallbackList;

/**
 * Callback list optimized for a single callback.
 *
 * This allows to skip the merge logic and just return the single result.
 */
class SingleModuleCallbackListSingle implements SingleModuleCallbackListInterface {

  /**
   * Constructor.
   *
   * @param \Closure $function
   *   Closure for the single function.
   */
  public function __construct(
    private readonly \Closure $function,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function containsMainFunction(): bool {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function invoke(array $args = []): mixed {
    return ($this->function)(...$args);
  }

  /**
   * {@inheritdoc}
   */
  public function getCallbacks(): array {
    return [$this->function];
  }

}
