<?php

declare(strict_types=1);

namespace Drupal\Tests\Core\Database\Stub;

use Drupal\Core\Database\Identifier\IdentifierProcessorBase;
use Drupal\Core\Database\Identifier\IdentifierType;

/**
 * A stub of the abstract IdentifierProcessorBase class for testing purposes.
 */
class StubIdentifierProcessor extends IdentifierProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function getMaxLength(IdentifierType $type): int {
    return 4000;
  }

}
