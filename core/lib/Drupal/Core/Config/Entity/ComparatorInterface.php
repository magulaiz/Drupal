<?php

declare(strict_types=1);

namespace Drupal\Core\Config\Entity;

interface ComparatorInterface {

  public function isEquivalent(array $a, array $b): bool;

}
