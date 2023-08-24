<?php

declare(strict_types = 1);

namespace Drupal\Core\Attribute\Hook;

/**
 * Base class for hook implementation attributes.
 */
abstract class HookBase implements HookAttributeInterface {

  /**
   * Constructor.
   *
   * @param list<string> $hooks
   *   Hook names.
   * @param array $info
   *   Info about this implementation.
   *
   * @psalm-param array{
   *   module?: string,
   *   weight?: int,
   *   before?: string|list<string>,
   *   after?: string|list<string>,
   * } $info
   */
  protected function __construct(
    private readonly array $hooks,
    private readonly array $info,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function getHookNames(): array {
    return $this->hooks;
  }

  /**
   * {@inheritdoc}
   */
  public function getInfo(): array {
    return $this->info;
  }

}
