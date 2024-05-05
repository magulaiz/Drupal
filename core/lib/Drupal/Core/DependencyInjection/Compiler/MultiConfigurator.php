<?php

namespace Drupal\Core\DependencyInjection\Compiler;

/**
 * Configurator that invokes multiple configurators on the same service.
 */
class MultiConfigurator {

  /**
   * Constructor.
   *
   * @param array[] $configurators
   *   Callbacks with additional arguments.
   */
  public function __construct(
    private array $configurators,
  ) {}

  /**
   * Invokes all the callbacks with the given arguments.
   *
   * @param object $service
   *   Service to be configured.
   */
  public function configure(object $service): void {
    foreach ($this->configurators as [$callback, $arguments]) {
      $callback($service, ...$arguments);
    }
  }

}
