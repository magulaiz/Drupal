<?php

namespace Drupal\dblog\Logger;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Logger\RfcLoggerTrait;
use Psr\Log\LoggerInterface;

class Disabled implements LoggerInterface {
  use RfcLoggerTrait;
  use DependencySerializationTrait;

  public function log($level, string|\Stringable $message, array $context = []): void {
    // logging paused, do not log anything
  }
}
