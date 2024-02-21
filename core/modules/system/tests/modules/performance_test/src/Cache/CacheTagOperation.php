<?php

declare(strict_types = 1);

namespace Drupal\performance_test\Cache;

enum CacheTagOperation {
  case getCurrentChecksum;
  case invalidateTags;
  case isValid;
}
