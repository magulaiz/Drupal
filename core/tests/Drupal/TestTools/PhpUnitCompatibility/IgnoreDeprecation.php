<?php

declare(strict_types=1);

namespace Drupal\TestTools\PhpUnitCompatibility;

/**
 * Helper class to manage ignoring deprecations.
 *
 * This class contains static methods only and is not meant to be instantiated.
 *
 * @internal
 */
final class IgnoreDeprecation {

  /**
   * @var list<string>
   */
  private static array $ignoreDeprecationPatterns = [];

  /**
   * This class should not be instantiated.
   */
  private function __construct() {
  }

  public static function init(string $ignoreFile): void {
    if (!self::$ignoreDeprecationPatterns) {
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

}
