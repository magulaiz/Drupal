<?php

namespace Drupal\Core\Test;

use Drupal\Core\Database\Database;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\TestTools\Extension\DeprecationBridge\DeprecationHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Run PHPUnit-based tests.
 *
 * This class runs PHPUnit-based tests and converts their JUnit results to a
 * format that can be stored in the {simpletest} database schema.
 *
 * This class is internal and not considered to be API.
 *
 * @code
 * $runner = PhpUnitTestRunner::create(\Drupal::getContainer());
 * $results = $runner->execute($test_run);
 * @endcode
 *
 * @internal
 */
class PhpUnitTestRunner implements ContainerInjectionInterface {

  private readonly PhpUnitRunner $runner;

  public function __construct($appRoot, $workingDirectory) {
    $this->runner = new PhpUnitRunner($appRoot, $workingDirectory);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      (string) $container->getParameter('app.root'),
      (string) $container->get('file_system')->realpath('public://simpletest')
    );
  }

  /**
   * Executes PHPUnit tests and returns the results of the run.
   *
   * @param \Drupal\Core\Test\TestRun $test_run
   *   The test run object.
   *
   * @return array
   *   The parsed results of PHPUnit's JUnit XML output, in the format of
   *   {simpletest}'s schema.
   *
   * @internal
   */
  public function execute(TestRun $testRun): array {
    $logJunitFilePath = $this->workingDirectory . DIRECTORY_SEPARATOR . $testRun->logFileName;
    $testRunResult = $this->runner->runOneTestClass($testRun);
    if ($testRunResult->status == TestStatus::PASS) {
      return JUnitConverter::xmlToRows($testRun->id(), $testRunResult->xmlLog);
    }
    return [
      [
        'test_id' => $testRun->id(),
        'test_class' => $testRun->testClassName,
        'status' => TestStatus::label($status),
        'message' => 'PHPUnit Test failed to complete; Error: ' . $testRunResult->output,
        'message_group' => 'Other',
        'function' => $testRun->testClassName,
        'line' => '0',
        'file' => $logJunitFilePath,
      ],
    ];
  }

  /**
   * Logs the parsed PHPUnit results into the test run.
   *
   * @param \Drupal\Core\Test\TestRun $test_run
   *   The test run object.
   * @param array[] $phpunit_results
   *   An array of test results, as returned from
   *   \Drupal\Core\Test\JUnitConverter::xmlToRows(). Can be the return value of
   *   PhpUnitTestRunner::execute().
   */
  public function processPhpUnitResults(TestRun $test_run, array $phpunit_results): void {
    foreach ($phpunit_results as $result) {
      $test_run->insertLogEntry($result);
    }
  }

  /**
   * Tallies test results per test class.
   *
   * @param string[][] $results
   *   Array of results in the {simpletest} schema. Can be the return value of
   *   PhpUnitTestRunner::execute().
   *
   * @return int[][]
   *   Array of status tallies, keyed by test class name and status type.
   *
   * @internal
   */
  public function summarizeResults(array $results): array {
    $summaries = [];
    foreach ($results as $result) {
      if (!isset($summaries[$result['test_class']])) {
        $summaries[$result['test_class']] = [
          '#pass' => 0,
          '#fail' => 0,
          '#exception' => 0,
          '#debug' => 0,
        ];
      }

      switch ($result['status']) {
        case 'pass':
          $summaries[$result['test_class']]['#pass']++;
          break;

        case 'fail':
          $summaries[$result['test_class']]['#fail']++;
          break;

        case 'exception':
          $summaries[$result['test_class']]['#exception']++;
          break;

        case 'debug':
          $summaries[$result['test_class']]['#debug']++;
          break;
      }
    }
    return $summaries;
  }

}
