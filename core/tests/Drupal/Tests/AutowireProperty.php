<?php

declare(strict_types=1);

namespace Drupal\Tests;

use Symfony\Component\DependencyInjection\Argument\ArgumentInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Autowire properties.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class AutowireProperty extends Autowire {

  public function __construct(
    string|array|ArgumentInterface|NULL $value = NULL,
    ?string $service = NULL,
    ?string $expression = NULL,
    ?string $env = NULL,
    ?string $param = NULL,
    bool|string|array $lazy = false,
  ) {
    parent::__construct($value, $service, $expression, $env, $param, $lazy);
  }

}
