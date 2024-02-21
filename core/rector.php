<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
  ->withPhpVersion(PhpVersion::PHP_81)
  ->withParallel(300)
  ->withPaths([
    __DIR__,
    __DIR__ . '/../composer'
  ])
  ->withFileExtensions([
    'engine',
    'inc',
    'install',
    'module',
    'php',
    'profile',
    'theme',
  ])
  ->withRules([
  ])
  ->withImportNames(
    importDocBlockNames: FALSE,
    importShortClasses: FALSE,
    removeUnusedImports: TRUE,
  )
  ->withIndent(indentSize: 2)
;

