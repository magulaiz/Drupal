<?php

namespace Drupal\Core\Test;

use Composer\Autoload\ClassLoader;
use Drupal\Core\Test\Exception\MissingGroupException;
use PHPUnit\TextUI\Configuration\Builder;
use PHPUnit\TextUI\Configuration\TestSuiteBuilder;

/**
 * Discovers available tests using the PHPUnit API.
 *
 * @internal
 */
class PhpUnitTestDiscovery {

  /**
   * The class loader.
   */
  protected ClassLoader $classLoader;

  /**
   * The app root.
   */
  private string $root;

  private array $map = [
    'PHPUnit-FunctionalJavascript' => 'functional-javascript',
    'PHPUnit-Functional' => 'functional',
    'PHPUnit-Kernel' => 'kernel',
    'PHPUnit-Unit' => 'unit',
    'PHPUnit-Build' => 'build',
  ];

  private array $reverseMap;

  /**
   * Constructs a new test discovery.
   *
   * @param string $root
   *   The app root.
   * @param class-string $class_loader
   *   The class loader. Normally Composer's ClassLoader, as included by the
   *   front controller, but may also be decorated.
   */
  public function __construct($root, $class_loader) {
    $this->root = $root;
    $this->classLoader = $class_loader;
    $this->reverseMap = array_flip($this->map);
  }

  public function getTestClasses($extension = NULL, array $types = [], ?string $directory = NULL): array {
    $args = ['--configuration', $this->root . \DIRECTORY_SEPARATOR . 'core'];

    if (!empty($types)) {
      $tmp = [];
      foreach ($types as $i) {
        $tmp[] = $this->map[$i] ?? $i;
      }
      $args[] = '--testsuite=' . implode(',', $tmp);
    }

    if ($directory !== NULL) {
      $args[] = $directory;
    }

    $phpUnitConfiguration = (new Builder())->build($args);
    $phpUnitTestSuite = (new TestSuiteBuilder())->build($phpUnitConfiguration);

    if ($directory !== NULL) {
      $list = [];
        foreach ($phpUnitTestSuite->tests() as $testClass) {
          if ($extension !== NULL && !str_starts_with($testClass->name(), "Drupal\\Tests\\{$extension}\\")) {
            continue;
          }

          if (!empty($types) && !in_array('PHPUnit-' . TestDiscovery::getPhpunitTestSuite($testClass->name()), $types, TRUE)) {
            continue;
          }

          $reflection = new \ReflectionClass($testClass->name());
          $docComment = $reflection->getDocComment();

          $annotations = [];
          // Look for annotations, allow an arbitrary amount of spaces before the
          // * but nothing else.
          preg_match_all('/^[ ]*\* (\@[^\s]*)(.*)/m', $docComment, $matches);
          if (isset($matches[1])) {
            foreach ($matches[1] as $key => $annotation) {
              $annotations[$annotation][] = substr($matches[2][$key], 1);
            }
          }

          $groups = array_filter($testClass->groups(), function (string $value): bool {
            return !str_starts_with($value, '__');
          });
          # $groups = $annotations['@group'] ?? [];
          // @todo add failure if missing

          if (isset($annotations['@coversDefaultClass'][0])) {
            $description = sprintf('Tests %s.', $annotations['@coversDefaultClass'][0]);
          }
          else {
            $description = TestDiscovery::parseTestClassSummary($docComment);
          }

          $item = [
            'name' => $testClass->name(),
            'group' => $groups[0],
            'groups' => $groups,
            'type' => 'PHPUnit-' . TestDiscovery::getPhpunitTestSuite($testClass->name()),
            'description' => $description,
            'file' => $this->classLoader->findFile($testClass->name()),
          ];

          foreach ($groups as $group) {
            $list[$group][$testClass->name()] = $item;
          }
        }
    }
    else {
      $list = [];
      foreach ($phpUnitTestSuite->tests() as $testSuite) {
        foreach ($testSuite->tests() as $testClass) {
          if ($extension !== NULL && !str_starts_with($testClass->name(), "Drupal\\Tests\\{$extension}\\")) {
            continue;
          }

          $reflection = new \ReflectionClass($testClass->name());
          $docComment = $reflection->getDocComment();

          $annotations = [];
          // Look for annotations, allow an arbitrary amount of spaces before the
          // * but nothing else.
          preg_match_all('/^[ ]*\* (\@[^\s]*)(.*)/m', $docComment, $matches);
          if (isset($matches[1])) {
            foreach ($matches[1] as $key => $annotation) {
              $annotations[$annotation][] = substr($matches[2][$key], 1);
            }
          }

          $groups = array_filter($testClass->groups(), function (string $value): bool {
            return !str_starts_with($value, '__');
          });
          # $groups = $annotations['@group'] ?? [];
          // @todo add failure if missing

          if (isset($annotations['@coversDefaultClass'][0])) {
            $description = sprintf('Tests %s.', $annotations['@coversDefaultClass'][0]);
          }
          else {
            $description = TestDiscovery::parseTestClassSummary($docComment);
          }

          $item = [
            'name' => $testClass->name(),
            'group' => $groups[0],
            'groups' => $groups,
            'type' => $this->reverseMap[$testSuite->name()] ?? $testSuite->name(),
            'description' => $description,
            'file' => $this->classLoader->findFile($testClass->name()),
          ];

          foreach ($groups as $group) {
            $list[$group][$testClass->name()] = $item;
          }
        }
      }
    }

    // Sort the groups and tests within the groups by name.
    uksort($list, 'strnatcasecmp');
    foreach ($list as &$tests) {
      uksort($tests, 'strnatcasecmp');
    }

    return $list;
  }

}
