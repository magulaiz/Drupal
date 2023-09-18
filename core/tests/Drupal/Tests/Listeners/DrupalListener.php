<?php

namespace Drupal\Tests\Listeners;

use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestSuite;
use Symfony\Bridge\PhpUnit\SymfonyTestsListener;

/**
 * Listens to PHPUnit test runs.
 *
 * @internal
 */
class DrupalListener implements TestListener {

  use TestListenerDefaultImplementation;
  use DrupalComponentTestListenerTrait;
  use TimeLimitListenerTrait;

  /**
   * The wrapped Symfony test listener.
   *
   * @var \Symfony\Bridge\PhpUnit\SymfonyTestsListener
   */
  private $symfonyListener;

  /**
   * Constructs the DrupalListener object.
   * @param float $time_threshold
   *   Time in seconds before the time limit should fail tests which don't
   *   belong to the slow group.
   * @param string $slow_group
   *   Group name which exempts long execution time failures.
   */
  public function __construct($time_threshold = 10.0, $slow_group = '#slow') {
    $this->symfonyListener = new SymfonyTestsListener();
    $this->setTimeThreshold($time_threshold);
    $this->setSlowGroup($slow_group);
  }

  /**
   * {@inheritdoc}
   */
  public function startTestSuite(TestSuite $suite): void {
    $this->symfonyListener->startTestSuite($suite);
  }

  /**
   * {@inheritdoc}
   */
  public function addSkippedTest(Test $test, \Throwable $t, float $time): void {
    $this->symfonyListener->addSkippedTest($test, $t, $time);
  }

  /**
   * {@inheritdoc}
   */
  public function startTest(Test $test): void {
    $this->symfonyListener->startTest($test);
    // Check for incorrect visibility of the $modules property.
    $class = new \ReflectionClass($test);
    if ($class->hasProperty('modules') && !$class->getProperty('modules')->isProtected()) {
      @trigger_error('The ' . get_class($test) . '::$modules property must be declared protected. See https://www.drupal.org/node/2909426', E_USER_DEPRECATED);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function endTest(Test $test, float $time): void {
    $this->symfonyListener->endTest($test, $time);
    $this->componentEndTest($test, $time);
    $this->timeLimitEndTest($test, $time);
  }

}
