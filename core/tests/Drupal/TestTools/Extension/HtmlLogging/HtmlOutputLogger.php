<?php

declare(strict_types=1);

namespace Drupal\TestTools\Extension\HtmlLogging;

use PHPUnit\Event\Facade;
use PHPUnit\Event\TestRunner\Finished as TestRunnerFinished;
use PHPUnit\Event\TestRunner\Started as TestRunnerStarted;

/**
 * @internal
 */
final class HtmlOutputLogger {

  /**
   * The singleton instance.
   */
  private static ?self $instance = NULL;

  /**
   * @todo
   */
  private static array $links = [];

  /**
   * @throws \PHPUnit\Event\EventFacadeIsSealedException
   * @throws \PHPUnit\Util\Exception
   * @throws \PHPUnit\Event\UnknownSubscriberTypeException
   * @throws \RuntimeException
   */
  private function __construct(
    private readonly string $outputDirectory,
    private readonly bool $outputVerbose,
    private readonly Facade $facade,
  ) {
    $this->facade->registerSubscriber(new TestRunnerStartedSubscriber($this));
    $this->facade->registerSubscriber(new TestRunnerFinishedSubscriber($this));
  }

  /**
   * @todo
   *
   * @throws \PHPUnit\Event\EventFacadeIsSealedException
   * @throws \PHPUnit\Util\Exception
   * @throws \PHPUnit\Event\UnknownSubscriberTypeException
   * @throws \RuntimeException
   */
  public static function init(string $outputDirectory, bool $outputVerbose): void {
    if (self::$instance === NULL) {
      if (!is_dir($outputDirectory) || !is_writable($outputDirectory)) {
        throw new \RuntimeException("HTML output directory {$outputDirectory} is not a writable directory.");
      }
      self::$instance = new self($outputDirectory, $outputVerbose, Facade::instance());
    }
  }

  /**
   * @todo
   */
  public static function isEnabled(): bool {
    return self::$instance !== NULL;
  }

  /**
   * @todo
   *
   * @throws \RuntimeException
   */
  public static function log(string $logEntry): void {
    if (!self::isEnabled()) {
      throw new \RuntimeException("HTML output is not enabled");
    }
    self::$links[] = $logEntry;
  }

  /**
   * Empties the list of the HTML output created during the test run.
   */
  public function testRunnerStarted(TestRunnerStarted $event): void {
    self::$links = [];
  }

  /**
   * Prints the list of HTML output generated during the test.
   */
  public function testRunnerFinished(TestRunnerFinished $event): void {
    if (self::$links) {
      print "\n\n";
      if ($this->outputVerbose) {
        print "HTML output was generated.\n\n";
        foreach (self::$links as $link) {
          print $link;
        }
      }
      else {
        print "HTML output was generated, " . count(self::$links) . " page(s).";
      }
    }
  }

}
