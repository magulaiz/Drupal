<?php

declare(strict_types=1);

namespace Drupal\TestTools\Extension\DeprecationBridge;

use PHPUnit\Framework\TestCase;

/**
 * @todo
 *
 * @internal
 */
final class DeprecationHandler {

  private static bool $enabled = FALSE;

  /**
   * @var list<string>
   */
  private static array $deprecationIgnorePatterns = [];

  /**
   * @var list<string>
   */
  private static array $expectedDeprecations = [];

  /**
   * @var list<string>
   */
  private static array $collectedDeprecations = [];

  /**
   * This class should not be instantiated.
   */
  private function __construct() {
    throw new \LogicException(__CLASS__ . ' should not be instantiated');
  }

  /**
   * @todo
   */
  public static function getConfiguration(): array|FALSE {
    $environmentVariable = getenv('SYMFONY_DEPRECATIONS_HELPER');
    if ($environmentVariable === 'disabled') {
      return FALSE;
    }
    if ($environmentVariable === FALSE) {
      // Ensure ignored deprecation patterns listed in .deprecation-ignore.txt
      // are considered in testing.
      $relativeFilePath = __DIR__ . "/../../../../../.deprecation-ignore.txt";
      $deprecationIgnoreFilename = realpath($relativeFilePath);
      if (empty($deprecationIgnoreFilename)) {
        throw new \InvalidArgumentException(sprintf('The ignoreFile "%s" does not exist.', $relativeFilePath));
      }
      $environmentVariable = "ignoreFile=$deprecationIgnoreFilename";
    }
    parse_str($environmentVariable, $configuration);
    return $configuration;
  }

  /**
   * @todo
   */
  public static function isEnabled(): bool {
    return self::$enabled;
  }

  /**
   * @todo
   */
  public static function init(?string $ignoreFile = NULL): void {
    if (self::isEnabled()) {
      throw new \LogicException(__CLASS__ . ' is already initialized');
    }

    // Load the deprecation ignore patterns from the specified file.
    if ($ignoreFile && !self::$deprecationIgnorePatterns) {
      if (!is_file($ignoreFile)) {
        throw new \InvalidArgumentException(sprintf('The ignoreFile "%s" does not exist.', $ignoreFile));
      }
      set_error_handler(static function ($t, $m) use ($ignoreFile, &$line) {
        throw new \RuntimeException(sprintf('Invalid pattern found in "%s" on line "%d"', $ignoreFile, 1 + $line) . substr($m, 12));
      });
      try {
        foreach (file($ignoreFile) as $line => $pattern) {
          if ((trim($pattern)[0] ?? '#') !== '#') {
            preg_match($pattern, '');
            self::$deprecationIgnorePatterns[] = $pattern;
          }
        }
      }
      finally {
        restore_error_handler();
      }
    }

    // Mark the extension as enabled.
    self::$enabled = TRUE;
  }

  public static function reset(): void {
    if (!self::isEnabled()) {
      return;
    }
    self::$expectedDeprecations = [];
    self::$collectedDeprecations = [];
  }

  public static function expectDeprecation(string $message): void {
    if (!self::isEnabled()) {
      return;
    }
    self::$expectedDeprecations[] = $message;
  }

  public static function getExpectedDeprecations(): array {
    if (!self::isEnabled()) {
      throw new \LogicException(__CLASS__ . ' is not initialized');
    }
    return self::$expectedDeprecations;
  }

  public static function collectActualDeprecation(string $message): void {
    if (!self::isEnabled()) {
      return;
    }
    self::$collectedDeprecations[] = $message;
  }

  public static function getCollectedDeprecations(): array {
    if (!self::isEnabled()) {
      throw new \LogicException(__CLASS__ . ' is not initialized');
    }
    return self::$collectedDeprecations;
  }

  public static function isIgnoredDeprecation(string $deprecationMessage): bool {
    if (!self::$deprecationIgnorePatterns) {
      return FALSE;
    }
    $result = @preg_filter(self::$deprecationIgnorePatterns, '$0', $deprecationMessage);
    if (preg_last_error() !== \PREG_NO_ERROR) {
      throw new \RuntimeException(preg_last_error_msg());
    }
    return (bool) $result;
  }

  public static function isDeprecationTest(TestCase $testCase): bool {
    return $testCase->valueObjectForEvents()->metadata()->isIgnoreDeprecations()->isNotEmpty() || self::isTestInLegacyGroup($testCase);
  }

  private static function isTestInLegacyGroup(TestCase $testCase): bool {
    $groups = [];
    foreach ($testCase->valueObjectForEvents()->metadata()->isGroup() as $metadata) {
      $groups[] = $metadata->groupName();
    }
    return in_array('legacy', $groups, TRUE);
  }

  /**
   * @todo
   */
  public static function currentErrorHandler(): ?callable {
    $currentHandler = set_error_handler('var_dump');
    restore_error_handler();
    return $currentHandler;
  }

  /**
   * @todo for debugging. Remove eventually.
   */
  public static function dumpErrorHandler($msg): void {
    $handler = self::currentErrorHandler();
    dump([$msg, (is_object($handler) ? get_class($handler) : $handler)]);
  }

}
