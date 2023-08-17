<?php

declare(strict_types = 1);

namespace Drupal\KernelTests\Core\HookSystem\Attribute;

/**
 * Attribute to declare additional modules to install.
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class ExtraModules {

  /**
   * Constructor.
   *
   * @param list<string> $modules
   *   Module names.
   */
  public function __construct(
    public readonly array $modules,
  ) {}

}
