<?php

namespace Drupal\Tests\Listeners;

use Drupal\Core\Test\JUnitListener;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use Symfony\Bridge\PhpUnit\SymfonyTestsListener;

/**
 * Listens to PHPUnit test runs.
 *
 * @internal
 */
class DrupalListener implements TestListener {

  use TestListenerDefaultImplementation;
  use DrupalComponentTestListenerTrait;
  use DrupalStandardsListenerTrait;

  /**
   * The wrapped Symfony test listener.
   *
   * @var \Symfony\Bridge\PhpUnit\SymfonyTestsListener
   */
  private $symfonyListener;

  /**
   * The wrapped Drupal JUnit test listener.
   *
   * @var \Drupal\Core\Test\JUnitListener
   */
  private $jUnitListener;

  /**
   * Constructs the DrupalListener object.
   */
  public function __construct() {
    $this->symfonyListener = new SymfonyTestsListener();
  }

  /**
   * {@inheritdoc}
   */
  public function startTestSuite(TestSuite $suite): void {
    $this->symfonyListener->startTestSuite($suite);
    $this->getJUnitListener()->startTestSuite($suite);
  }

  /**
   * {@inheritdoc}
   */
  public function endTestSuite(TestSuite $suite): void {
    $this->getJUnitListener()->endTestSuite($suite);
  }

  /**
   * {@inheritdoc}
   */
  public function addError(Test $test, \Throwable $t, float $time): void {
    $this->getJUnitListener()->addError($test, $t, $time);
  }

  /**
   * {@inheritdoc}
   */
  public function addWarning(Test $test, Warning $e, float $time): void {
    $this->getJUnitListener()->addWarning($test, $e, $time);
  }

  /**
   * {@inheritdoc}
   */
  public function addFailure(Test $test, AssertionFailedError $e, float $time): void {
    $this->getJUnitListener()->addFailure($test, $e, $time);
  }

  /**
   * {@inheritdoc}
   */
  public function addIncompleteTest(Test $test, \Throwable $t, float $time): void {
    $this->getJUnitListener()->addIncompleteTest($test, $t, $time);
  }

  /**
   * {@inheritdoc}
   */
  public function addRiskyTest(Test $test, \Throwable $t, float $time): void {
    $this->getJUnitListener()->addRiskyTest($test, $t, $time);
  }

  /**
   * {@inheritdoc}
   */
  public function addSkippedTest(Test $test, \Throwable $t, float $time): void {
    $this->symfonyListener->addSkippedTest($test, $t, $time);
    $this->getJUnitListener()->addSkippedTest($test, $t, $time);
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
    $this->getJUnitListener()->startTest($test);
  }

  /**
   * {@inheritdoc}
   */
  public function endTest(Test $test, float $time): void {
    $this->symfonyListener->endTest($test, $time);
    $this->componentEndTest($test, $time);
    $this->standardsEndTest($test, $time);
    $this->getJUnitListener()->endTest($test, $time);
  }

  /**
   * Returns the JUnit listener instance.
   *
   * We cannot add a <listener> to phpunit.xml or in the constructor here,
   * since the JUnit listener throws a deprecation that would not be possible
   * to silence, so we lazy instantiate here when needed.
   *
   * @return \PHPUnit\Framework\TestListener
   *   The JUnit listener.
   */
  private function getJUnitListener(): TestListener {
    if (!$this->jUnitListener) {
      $this->jUnitListener = new JUnitListener();
    }
    return $this->jUnitListener;
  }

}
