<?php

declare(strict_types=1);

namespace Drupal\TestTools\Trait;

/**
 * Manage expected deprecations.
 *
 * @internal
 */
trait ExpectDeprecationTrait {

  /**
   * @var list<string>
   */
  protected static array $ignoreDeprecationPatterns = [];

  public function setUpIgnoreDeprecationPatterns(): void {
    if (!self::$ignoreDeprecationPatterns) {
      $root = $this->root ?? dirname(substr(__DIR__, 0, -strlen(__NAMESPACE__)), 2);
      $ignoreFile = $root . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . '.deprecation-ignore.txt';
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

  public function expectDeprecation(string $message): void {
    // @todo Do something clever here.
  }

  public function isIgnoredDeprecation(string $deprecationMessage): bool {
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
