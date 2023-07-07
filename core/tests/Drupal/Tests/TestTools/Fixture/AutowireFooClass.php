<?php

declare(strict_types = 1);

namespace Drupal\Tests\TestTools\Fixture;

use Drupal\Tests\Core\DependencyInjection\Fixture\BarClass;

class AutowireFooClass implements AutowireFooInterface {

  public function __construct(
    private readonly string $x,
    private readonly BarClass $bar,
  ) {}

  public function getX() {
    return $this->x;
  }

  public function getBar() {
    return $this->bar;
  }

}
