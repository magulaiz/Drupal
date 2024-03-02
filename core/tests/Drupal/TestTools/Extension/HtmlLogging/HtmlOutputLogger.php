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
  public static function init(): void {
    if (self::$instance === NULL) {
      self::$instance = new self(Facade::instance());
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
      print "\n";
      // @todo decide whether to go verbose or not, or configurable.
      print "HTML output was generated, " . count(self::$links) . " page(s).\n";
      print "\n";
    }
  }

}
