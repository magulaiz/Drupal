<?php

use Rector\Config\RectorConfig;
use Drupal\Core\Rector\StringTypeRector;

return RectorConfig::configure()
  ->withPaths([
    'core/lib',
    'core/modules',
    'core/profiles',
    'core/tests',
    'core/themes',
  ])
  ->withSkipPath('core/lib/Drupal/Component/Annotation/Doctrine')
  ->withFileExtensions(['php'])
  ->withRules([StringTypeRector::class])
  ->withParallel(300)
;
