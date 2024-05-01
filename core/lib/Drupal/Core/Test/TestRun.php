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
   * @todo
   */
  public readonly bool $failOnDeprecation;
  public readonly bool $processIsolation;
  public readonly string $testFilePath;
  public readonly string $logFileName;

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

    // Non-Unit tests should be run in isolation.
    $this->processIsolation = TestDiscovery::getPhpunitTestSuite($this->testClassName) !== 'Unit' ? TRUE : FALSE;

    // The file containing the test class to be run.
    $this->testFilePath = (new \ReflectionClass($this->testClassName))->getFileName();

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
    $testClassName = $testRunResultsStorage->getTestClassName($test_id);
    return new static($testRunResultsStorage, $testClassName, $test_id);
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
