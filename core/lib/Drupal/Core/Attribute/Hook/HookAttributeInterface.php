<?php

declare(strict_types = 1);

namespace Drupal\Core\Attribute\Hook;

/**
 * Interface for hook implementation attributes.
 */
interface HookAttributeInterface {

  /**
   * Gets implemented hook names.
   *
   * @return list<string>
   *   List of hook names.
   */
  public function getHookNames(): array;

  /**
   * Gets info about this implementation.
   *
   * @return array
   *   Info about this implementation.
   *
   * @phpstan-return array{
   *   module?: string,
   *   weight?: int,
   *   before?: string|list<string>,
   *   after?: string|list<string>,
   * }
   */
  public function getInfo(): array;

}
