<?php

declare(strict_types=1);

namespace Drupal\TestTools\Extension\DeprecationBridge;

/**
 * @todo
 *
 * @internal
 */
final class DeprecationHandler {

  /**
   * The singleton instance.
   */
  private static ?self $instance = NULL;

  /**
   * @var list<string>
   */
  private static array $ignoreDeprecationPatterns = [];

  private static array $expectedDeprecations = [];
  private static array $collectedDeprecations = [];

  /**
   * @todo
   */
  private function __construct() {
  }

  /**
   * @todo
   */
  public static function isEnabled(): bool {
    return self::$instance !== NULL;
  }

  /**
   * @todo
   */
  public static function init(?string $ignoreFile = NULL): void {
    if (self::$instance === NULL) {
      if ($ignoreFile && !self::$ignoreDeprecationPatterns) {
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
              self::$ignoreDeprecationPatterns[] = $pattern;
            }
          }
        }
        finally {
          restore_error_handler();
        }
      }
      self::$instance = new self();
    }
  }

  public static function reset(): void {
    self::$expectedDeprecations = [];
    self::$collectedDeprecations = [];
  }

  public static function expectDeprecation(string $message): void {
    self::$expectedDeprecations[] = $message;
  }

  public static function getExpectedDeprecations(): array {
    return self::$expectedDeprecations;
  }

  public static function collectActualDeprecation(string $message): void {
    self::$collectedDeprecations[] = $message;
  }

  public static function getCollectedDeprecations(): array {
    return self::$collectedDeprecations;
  }

  public static function isIgnoredDeprecation(string $deprecationMessage): bool {
    if (!self::$ignoreDeprecationPatterns) {
      return FALSE;
    }
    $result = @preg_filter(self::$ignoreDeprecationPatterns, '$0', $deprecationMessage);
    if (preg_last_error() !== \PREG_NO_ERROR) {
      throw new \RuntimeException(preg_last_error_msg());
    }
    return (bool) $result;
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
