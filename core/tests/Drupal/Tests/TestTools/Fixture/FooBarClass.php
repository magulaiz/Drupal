<?php

declare(strict_types = 1);

namespace Drupal\Tests\TestTools\Fixture;

class FooBarClass {

  public function foo($x, $y): string {
    return __METHOD__;
  }

  public function bar(): string {
    return __METHOD__;
  }

  public function optionalArgs($x, $y = 5): void {}

}
