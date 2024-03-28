<?php

use Drupal\Core\Rector\AddParamTypeFromPhpDocRector;
use Rector\Config\RectorConfig;

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
  ->withParallel(300)
  ->withRules([AddParamTypeFromPhpDocRector::class])
;
