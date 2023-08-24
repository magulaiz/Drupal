<?php

declare(strict_types = 1);

namespace Drupal\Core\Extension\Hook\Source;

/**
 * Interface for hook implementation sources.
 */
interface ImplementationSourceInterface {

  /**
   * Gets a list of implementations.
   *
   * @return array<string, list<array>>
   *   Lists of implementations by hook name.
   *
   * @phpstan-return array<string, list<array{
   *   module: non-empty-string,
   *   function?: callable-string,
   *   class?: class-string,
   *   service?: non-empty-string,
   *   method?: non-empty-string,
   *   weight?: int,
   *   before?: non-empty-string|list<non-empty-string>,
   *   after?: non-empty-string|list<non-empty-string>,
   * }>>
   */
  public function getImplementations(): array;

}
