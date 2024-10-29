<?php

namespace Drupal\Core\Test;

use Drupal\TestTools\Extension\DeprecationBridge\DeprecationHandler;

/**
 * Implements an object that tracks execution of a test run.
 *
 * @internal
 */
class TestRun {

  /**
   * The test database prefix.
   *
   * @var string
   */
  protected $databasePrefix;

  /**
   * The latest class under test.
   *
   * @var string
   */
  protected $testClass;

  /**
   * @todo Add doc.
   */
  public readonly bool $failOnDeprecation;
  public readonly ?string $testFilePath;
  public readonly string $logFileName;
  private array $results;
  private array $summaries;

  /**
   * TestRun constructor.
   *
   * @param \Drupal\Core\Test\TestRunResultsStorageInterface $testRunResultsStorage
   *   The test run results storage.
   * @param string $testClassName
   *   The test class name of this test run.
   * @param int|string $testId
   *   A unique test run id.
   */
  public function __construct(
    protected readonly TestRunResultsStorageInterface $testRunResultsStorage,
    public readonly string $testClassName,
    public readonly int|string $testId,
  ) {
    // If the deprecation handler bridge is active, we need to fail when there
    // are deprecations that get reported (i.e. not ignored or expected).
    $this->failOnDeprecation = DeprecationHandler::getConfiguration() !== FALSE ? TRUE : FALSE;

    // The file containing the test class to be run.
    try {
      $this->testFilePath = (new \ReflectionClass($this->testClassName))->getFileName();
    }
    catch (\ReflectionException) {
      $this->testFilePath = NULL;
    }

    $this->logFileName = 'phpunit-' . $this->testId . '.xml';
  }

  /**
   * Returns a new test run object.
   *
   * @param \Drupal\Core\Test\TestRunResultsStorageInterface $testRunResultsStorage
   *   The test run results storage.
   * @param string $testClassName
   *   The test class name of this test run.
   *
   * @return self
   *   The new test run object.
   */
  public static function createNew(
    TestRunResultsStorageInterface $testRunResultsStorage,
    string $testClassName,
  ): TestRun {
    $testId = $testRunResultsStorage->createNew($testClassName);
    return new static($testRunResultsStorage, $testClassName, $testId);
  }

  /**
   * Returns a test run object from storage.
   *
   * @param \Drupal\Core\Test\TestRunResultsStorageInterface $testRunResultsStorage
   *   The test run results storage.
   * @param int|string $test_id
   *   The test run id.
   *
   * @return self
   *   The test run object.
   */
  public static function get(
    TestRunResultsStorageInterface $testRunResultsStorage,
    int|string $test_id,
  ): TestRun {
    $testConfiguration = $testRunResultsStorage->getTestConfiguration($test_id);
    return new static($testRunResultsStorage, $testConfiguration['testClassName'], $test_id);
  }

  /**
   * Returns the id of the test run object.
   *
   * @return int|string
   *   The id of the test run object.
   */
  public function id(): int|string {
    return $this->testId;
  }

  /**
   * Sets the test database prefix.
   *
   * @param string $database_prefix
   *   The database prefix.
   *
   * @throws \RuntimeException
   *   If the database prefix cannot be saved to storage.
   */
  public function setDatabasePrefix(string $database_prefix): void {
    $this->databasePrefix = $database_prefix;
    $this->testRunResultsStorage->setDatabasePrefix($this, $database_prefix);
  }

  /**
   * Gets the test database prefix.
   *
   * @return string
   *   The database prefix.
   */
  public function getDatabasePrefix(): string {
    if (is_null($this->databasePrefix)) {
      $state = $this->testRunResultsStorage->getCurrentTestRunState($this);
      $this->databasePrefix = $state['db_prefix'];
      $this->testClass = $state['test_class'];
    }
    return $this->databasePrefix;
  }

  /**
   * Gets the latest class under test.
   *
   * @return string
   *   The test class.
   */
  public function getTestClass(): string {
    if (is_null($this->testClass)) {
      $state = $this->testRunResultsStorage->getCurrentTestRunState($this);
      $this->databasePrefix = $state['db_prefix'];
      $this->testClass = $state['test_class'];
    }
    return $this->testClass;
  }

  /**
   * Processes PHPUnit CLI results.
   *
   * @internal
   */
  public function processResults(
    int $status,
    string $output,
    string $error,
    string $logJunit,
  ): array {
    if ($status == TestStatus::PASS) {
      $this->results = JUnitConverter::xmlToRows($this->testId, $logJunit);
    }
    else {
      $this->results = [
        [
          'test_id' => $this->testId,
          'test_class' => $this->testClassName,
          'status' => TestStatus::label($status),
          'message' => 'PHPUnit Test failed to complete; Error: ' . $output,
          'message_group' => 'Other',
          'function' => $this->testClassName,
          'line' => '0',
          'file' => $this->logFileName,
        ],
      ];
    }

    // Logs the parsed PHPUnit results.
    foreach ($this->results as $result) {
      $this->insertLogEntry($result);
    }

    // Tallies test results per test class.
    $this->summarizeResults();

    return $this->results;
  }

  /**
   * Tallies test results per test class.
   *
   * Processes the array of results in the {simpletest} schema.
   *
   * @return array<string<array<string,int>>
   *   Array of status tallies, keyed by test class name and status type.
   *
   * @internal
   */
  private function summarizeResults(): array {
    $this->summaries = [];
    foreach ($this->results as $result) {
      if (!isset($this->summaries[$result['test_class']])) {
        $this->summaries[$result['test_class']] = [
          '#pass' => 0,
          '#fail' => 0,
          '#exception' => 0,
          '#debug' => 0,
        ];
      }

      switch ($result['status']) {
        case 'pass':
          $this->summaries[$result['test_class']]['#pass']++;
          break;

        case 'fail':
          $this->summaries[$result['test_class']]['#fail']++;
          break;

        case 'exception':
          $this->summaries[$result['test_class']]['#exception']++;
          break;

        case 'debug':
          $this->summaries[$result['test_class']]['#debug']++;
          break;

      }
    }
    return $this->summaries;
  }

  /**
   * Returns decoded test results.
   *
   * @internal
   */
  public function getResults(): array {
    return $this->results;
  }

  /**
   * Returns test results statistics.
   *
   * @internal
   */
  public function getSummaries(): array {
    return $this->summaries;
  }

  /**
   * Adds a test log entry.
   *
   * @param array $entry
   *   The array of the log entry elements.
   *
   * @return bool
   *   TRUE if the addition was successful, FALSE otherwise.
   */
  public function insertLogEntry(array $entry): bool {
    $this->testClass = $entry['test_class'];
    return $this->testRunResultsStorage->insertLogEntry($this, $entry);
  }

  /**
   * Get test results for a test run, ordered by test class.
   *
   * @return array
   *   Array of results ordered by test class and message id.
   */
  public function getLogEntriesByTestClass(): array {
    return $this->testRunResultsStorage->getLogEntriesByTestClass($this);
  }

  /**
   * Removes the test results from the storage.
   *
   * @return int
   *   The number of log entries that were removed from storage.
   */
  public function removeResults(): int {
    return $this->testRunResultsStorage->removeResults($this);
  }

  /**
   * Reads the PHP error log and reports any errors as assertion failures.
   *
   * The errors in the log should only be fatal errors since any other errors
   * will have been recorded by the error handler.
   *
   * @param string $error_log_path
   *   The path of log file.
   * @param string $test_class
   *   The test class to which the log relates.
   *
   * @return bool
   *   Whether any fatal errors were found.
   */
  public function processPhpErrorLogFile(string $error_log_path, string $test_class): bool {
    $found = FALSE;
    if (file_exists($error_log_path)) {
      foreach (file($error_log_path) as $line) {
        if (preg_match('/\[.*?\] (.*?): (.*?) in (.*) on line (\d+)/', $line, $match)) {
          // Parse PHP fatal errors for example: PHP Fatal error: Call to
          // undefined function break_me() in /path/to/file.php on line 17
          $this->insertLogEntry([
            'test_class' => $test_class,
            'status' => 'fail',
            'message' => $match[2],
            'message_group' => $match[1],
            'line' => $match[4],
            'file' => $match[3],
          ]);
        }
        else {
          // Unknown format, place the entire message in the log.
          $this->insertLogEntry([
            'test_class' => $test_class,
            'status' => 'fail',
            'message' => $line,
            'message_group' => 'Fatal error',
          ]);
        }
        $found = TRUE;
      }
    }
    return $found;
  }

}
