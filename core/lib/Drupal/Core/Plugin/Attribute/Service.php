<?php

namespace Drupal\Core\Plugin\Attribute;

/**
 * Attribute to tell a parameter which service it requires.
 */
#[\Attribute(\Attribute::TARGET_PARAMETER)]
class Service {

  public function __construct(
    public string $service,
  ) {}

}
