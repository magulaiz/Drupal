<?php

namespace Drupal\Core\Test;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExceptionWrapper;
use PHPUnit\Framework\SelfDescribing;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\Util\Filter;
use PHPUnit\Util\Xml;

/**
 * Generates a log file of the test execution in XML markup.
 *
 * The XML markup used is the same as the one that is used by the JUnit Ant
 * task.
 */
class JUnitListener implements TestListener {

  /**
   * @var \DOMDocument
   */
  protected $document;

  /**
   * @var \DOMElement
   */
  protected $root;

  /**
   * @var bool
   */
  protected $writeDocument = TRUE;

  /**
   * @var \DOMElement[]
   */
  protected $testSuites = [];

  /**
   * @var array
   */
  protected $testSuiteRuns = [];

  /**
   * @var int
   */
  protected $testSuiteLevel = 0;

  /**
   * @var \DOMElement
   */
  protected $currentTestCase;

  /**
   * Constructor.
   */
  public function __construct() {
    $this->document = new \DOMDocument('1.0', 'UTF-8');
    $this->document->formatOutput = TRUE;
    $this->root = $this->document->createElement('testsuites');
    $this->document->appendChild($this->root);
  }

  /**
   * Destructor.
   */
  public function __destruct() {
    $junit_file = getenv('SIMPLETEST_JUNIT_FILE');
    if (!empty($junit_file)) {
      @file_put_contents($junit_file, $this->getXML());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function addError(Test $test, \Throwable $t, float $time): void {
    $this->doAddFault($test, $t, $time, 'error');
    $this->testSuiteRuns[$this->testSuiteLevel]['errors']++;
  }

  /**
   * {@inheritdoc}
   */
  public function addWarning(Test $test, Warning $e, float $time): void {
    $this->doAddFault($test, $e, $time, 'warning');
    $this->testSuiteRuns[$this->testSuiteLevel]['failures']++;
  }

  /**
   * {@inheritdoc}
   */
  public function addFailure(Test $test, AssertionFailedError $e, float $time): void {
    $this->doAddFault($test, $e, $time, 'failure');
    $this->testSuiteRuns[$this->testSuiteLevel]['failures']++;
  }

  /**
   * {@inheritdoc}
   */
  public function addIncompleteTest(Test $test, \Throwable $t, float $time): void {
    $this->doAddSkipped($test, 'incomplete');
  }

  /**
   * {@inheritdoc}
   */
  public function addRiskyTest(Test $test, \Throwable $t, float $time): void {
    $this->doAddSkipped($test, 'risky');
  }

  /**
   * {@inheritdoc}
   */
  public function addSkippedTest(Test $test, \Throwable $t, float $time): void {
    $this->doAddSkipped($test, 'skipped');
  }

  /**
   * {@inheritdoc}
   */
  public function startTestSuite(TestSuite $suite): void {
    $testSuite = $this->document->createElement('testsuite');
    $testSuite->setAttribute('name', $suite->getName());
    if (class_exists($suite->getName(), FALSE)) {
      try {
        $class = new \ReflectionClass($suite->getName());
        $testSuite->setAttribute('file', $class->getFileName());
      }
      catch (\ReflectionException $e) {
        // Do nothing.
      }
    }
    if ($this->testSuiteLevel > 0) {
      $this->testSuites[$this->testSuiteLevel]->appendChild($testSuite);
    }
    else {
      $this->root->appendChild($testSuite);
    }
    $this->testSuiteLevel++;
    $this->testSuites[$this->testSuiteLevel] = $testSuite;
    $this->testSuiteRuns[$this->testSuiteLevel] = [
      'tests' => 0,
      'assertions' => 0,
      'errors' => 0,
      'failures' => 0,
      'risky' => 0,
      'skipped' => 0,
      'incomplete' => 0,
      'time' => 0,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function endTestSuite(TestSuite $suite): void {
    $properties = [
      'tests',
      'assertions',
      'errors',
      'failures',
      'risky',
      'skipped',
      'incomplete',
    ];

    // Add summary to the <testsuite> DOM element.
    foreach ($properties as $property) {
      $this->testSuites[$this->testSuiteLevel]->setAttribute($property, $this->testSuiteRuns[$this->testSuiteLevel][$property]);
    }
    $this->testSuites[$this->testSuiteLevel]->setAttribute('time', sprintf('%F', $this->testSuiteRuns[$this->testSuiteLevel]['time']));

    // When nesting, sum up results to the parent testsuite.
    if ($this->testSuiteLevel > 1) {
      foreach ($properties as $property) {
        $this->testSuiteRuns[$this->testSuiteLevel - 1][$property] += $this->testSuiteRuns[$this->testSuiteLevel][$property];
      }
      $this->testSuiteRuns[$this->testSuiteLevel - 1]['time'] += $this->testSuiteRuns[$this->testSuiteLevel]['time'];
    }
    $this->testSuiteLevel--;
  }

  /**
   * {@inheritdoc}
   */
  public function startTest(Test $test): void {
    $testCase = $this->document->createElement('testcase');
    $testCase->setAttribute('name', $test->getName());

    if ($test instanceof TestCase) {
      $class = new \ReflectionClass($test);
      $methodName = $test->getName(!$test->usesDataProvider());

      if ($class->hasMethod($methodName)) {
        $method = $class->getMethod($methodName);

        $testCase->setAttribute('class', $class->getName());
        $testCase->setAttribute('classname', str_replace('\\', '.', $class->getName()));
        $testCase->setAttribute('file', $class->getFileName());
        $testCase->setAttribute('line', $method->getStartLine());
      }
    }

    $this->currentTestCase = $testCase;
  }

  /**
   * {@inheritdoc}
   */
  public function endTest(Test $test, float $time): void {
    if ($test instanceof TestCase) {
      $num_assertions = $test->getNumAssertions();
      $this->testSuiteRuns[$this->testSuiteLevel]['assertions'] += $num_assertions;

      $this->currentTestCase->setAttribute('assertions', $num_assertions);
    }

    $this->currentTestCase->setAttribute('time', sprintf('%F', $time));

    $this->testSuites[$this->testSuiteLevel]->appendChild(
      $this->currentTestCase
    );

    $this->testSuiteRuns[$this->testSuiteLevel]['tests']++;
    $this->testSuiteRuns[$this->testSuiteLevel]['time'] += $time;

    if (method_exists($test, 'hasOutput') && $test->hasOutput()) {
      $systemOut = $this->document->createElement('system-out', Xml::prepareString($test->getActualOutput()));

      $this->currentTestCase->appendChild($systemOut);
    }

    $this->currentTestCase = NULL;
  }

  /**
   * Returns the XML as a string.
   *
   * @return string
   */
  public function getXML() {
    return $this->document->saveXML();
  }

  /**
   * Method to generalize addError() and addFailure()
   */
  private function doAddFault(Test $test, \Throwable $t, float $time, string $type): void {
    if ($this->currentTestCase === NULL) {
      return;
    }

    if ($test instanceof SelfDescribing) {
      $buffer = $test->toString() . "\n";
    }
    else {
      $buffer = '';
    }

    $buffer .= TestFailure::exceptionToString($t) . "\n" . Filter::getFilteredStacktrace($t);

    $fault = $this->document->createElement($type, Xml::prepareString($buffer));

    if ($t instanceof ExceptionWrapper) {
      $fault->setAttribute('type', $t->getClassName());
    }
    else {
      $fault->setAttribute('type', get_class($t));
    }

    $this->currentTestCase->appendChild($fault);
  }

  /**
   * Method to generalize unproductive tests.
   *
   * @see ::addSkippedTest
   * @see ::addIncompleteTest
   * @see ::addRiskyTest
   */
  private function doAddSkipped(Test $test, string $message): void {
    if ($this->currentTestCase === NULL) {
      return;
    }

    $skipped = $this->document->createElement('skipped');
    $skipped->setAttribute('message', $message);
    $this->currentTestCase->appendChild($skipped);

    switch ($message) {
      case 'risky':
        $this->testSuiteRuns[$this->testSuiteLevel]['risky']++;
        break;

      case 'skipped':
        $this->testSuiteRuns[$this->testSuiteLevel]['skipped']++;
        break;

      case 'incomplete':
        $this->testSuiteRuns[$this->testSuiteLevel]['incomplete']++;
        break;

    }
  }

}
