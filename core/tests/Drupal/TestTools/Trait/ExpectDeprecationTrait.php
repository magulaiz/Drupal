<?php

declare(strict_types=1);

namespace Drupal\TestTools\Trait;

use PHPUnit\Event\Code\TestMethodBuilder;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;

// cspell:ignore errno errstr errfile errline

/**
 * Manage expected deprecations.
 *
 * @internal
 */
trait ExpectDeprecationTrait {

  /**
   * The previous error handler.
   *
   * @var callable|null
   */
  protected $previouslyDefinedErrorHandler;

  /**
   * @var list<string>
   */
  protected static array $ignoreDeprecationPatterns = [];

  protected array $expectedDeprecations = [];
  protected array $collectedDeprecations = [];

  #[Before]
  public function setUpErrorHandler(): void {
    $this->setUpIgnoreDeprecationPatterns();

    if ($this->previouslyDefinedErrorHandler === NULL) {
      // Get current handler.
      $handler = set_error_handler('var_dump');
      restore_error_handler();

      $this->previouslyDefinedErrorHandler = set_error_handler(
        function (int $errno, string $errstr, string $errfile = NULL, int $errline = NULL) use ($handler): bool {
          // Collect deprecations regardless of whether they are ignored or not.
          if (E_USER_DEPRECATED === $errno || E_DEPRECATED === $errno) {
            $this->collectedDeprecations[] = $errstr;
          }

          if ((E_USER_DEPRECATED === $errno || E_DEPRECATED === $errno) && $this->isIgnoredDeprecation($errstr)) {
            return TRUE;
          }
          elseif ((E_USER_DEPRECATED === $errno || E_DEPRECATED === $errno) && $this->isTestInLegacyGroup()) {
            return TRUE;
          }
          else {
            call_user_func($handler, $errno, $errstr, $errfile, $errline);
          }
          return TRUE;
        }
      );
    }
  }

  protected function setUpIgnoreDeprecationPatterns(): void {
    if (!self::$ignoreDeprecationPatterns) {
      $root = dirname(substr(__DIR__, 0, -strlen(__NAMESPACE__)), 2);
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

  #[After]
  public function tearDownErrorHandler(): void {
    if ($this->previouslyDefinedErrorHandler !== NULL) {
      restore_error_handler();
    }

    // Checks if collected deprecations match the expectations.
    if ($this->expectedDeprecations) {
      $prefix = "@expectedDeprecation:\n";
      $expDep = $prefix . '%A  ' . implode("\n%A  ", $this->expectedDeprecations) . "\n%A";
      $actDep = $prefix . '  ' . implode("\n  ", $this->collectedDeprecations) . "\n";
      $this->assertStringMatchesFormat($expDep, $actDep);
    }
  }

  public function expectDeprecation(string $message): void {
    $test = TestMethodBuilder::fromTestCase($this);
    if (!$test->metadata()->isIgnoreDeprecations()->isNotEmpty() && !$this->isTestInLegacyGroup()) {
      throw new \RuntimeException('expectDeprecation() can only be called from tests marked with #[IgnoreDeprecations] or \'@group legacy\'');
    }
    $this->expectedDeprecations[] = $message;
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

  public function isTestInLegacyGroup(): bool {
    [$testMethod] = explode(' ', $this->name());
    $classDoc = (new \ReflectionClass($this))->getDocComment();
    $methodDoc = (new \ReflectionMethod($this, $testMethod))->getDocComment();
    return str_contains($classDoc, '@group legacy') || str_contains($methodDoc, '@group legacy');
  }

}
