<?php

declare(strict_types = 1);

namespace Drupal\Tests\TestTools\Fixture;

class BazBooClass {

  public function baz($x, $y): mixed {
    return __METHOD__;
  }

  public function boo(): string {
    return __METHOD__;
  }

}
