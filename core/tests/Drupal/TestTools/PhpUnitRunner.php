<?php

declare(strict_types=1);

namespace Drupal\TestTools;

use Composer\Autoload\ClassLoader;
use Drupal\Core\Database\Database;
use Drupal\Core\Test\TestRun;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Process;

/**
 * Runs PHPUnit commands.
 *
 * This class is internal and not considered to be API.
 *
 * @code
 * $runner = PhpUnitRunner::create(\Drupal::getContainer());
 * $results = $runner->runOneTestClass($test_run);
 * @endcode
 *
 * @internal
 */
final class PhpUnitRunner implements ContainerInjectionInterface {

  /**
   * @param string $appRoot
   *   Path to the application root.
   * @param string $workingDirectory
   *   Path to the working directory.
   */
  public function __construct(
    public readonly string $appRoot,
    public readonly string $workingDirectory,
    private readonly ClassLoader $classLoader,
  ) {
  }

  public static function create(ContainerInterface $container): static {
    return new static(
      (string) $container->getParameter('app.root'),
      (string) $container->get('file_system')->realpath('public://simpletest'),
      $container->get('class_loader'),
    );
  }

  /**
   * Returns the command to run PHPUnit.
   *
   * @return string
   *   The command that can be run through exec().
   *
   * @internal
   */
  protected function phpUnitCommand(): string {
    // Load the actual autoloader being used and determine its filename using
    // reflection. We can determine the vendor directory based on that filename.
    $autoloader = require $this->appRoot . '/autoload.php';
    $reflector = new \ReflectionClass($autoloader);
    return dirname($reflector->getFileName(), 2) . '/phpunit/phpunit/phpunit';
  }

  /**
   * Invoke PHPUnit CLI with a built command line.
   *
   * @param string[] $command
   *   The command to execute in its command line components.
   * @param array<string,string> $processEnvironmentVariables
   *   Additional environment variables for the process to execute.
   * @param string|null $output
   *   (optional) The output by running the PHPUnit command.
   * @param string|null $error
   *   (optional) The error output by running the PHPUnit command.
   *
   * @return int
   *   The exit status code of the PHPUnit process.
   *
   * @internal
   */
  protected function runPhpUnit(array $command, array $processEnvironmentVariables, ?string &$output = NULL, ?string &$error = NULL): int {
    $process = new Process($command, $this->appRoot . "/core", $processEnvironmentVariables);
    $process->setTimeout(NULL);
    $process->run();
    $output = $process->getOutput();
    $error = $process->getErrorOutput();
    return $process->getExitCode();
  }

  /**
   * Executes one single PHPUnit test class and returns the test results.
   *
   * @param \Drupal\Core\Test\TestRun $testRun
   *   The test run object.
   *
   * @return int
   *   The status code of the PHPUnit CLI process executed.
   *
   * @internal
   */
  public function runOneTestClass(TestRun $testRun): int {
    global $base_url;
    $logJunitFilePath = $this->workingDirectory . DIRECTORY_SEPARATOR . $testRun->logFileName;

    // Setup an environment variable containing the database connection so that
    // functional tests can connect to the database.
    $processEnvironmentVariables = [
      'SIMPLETEST_DB' => Database::getConnectionInfoAsUrl(),
    ];

    // Setup an environment variable containing the base URL, if it is available.
    // This allows functional tests to browse the site under test. When running
    // tests via CLI, core/phpunit.xml.dist or core/scripts/run-tests.sh can set
    // this variable.
    if ($base_url) {
      $processEnvironmentVariables['SIMPLETEST_BASE_URL'] = $base_url;
      $processEnvironmentVariables['BROWSERTEST_OUTPUT_DIRECTORY'] = $this->workingDirectory;
    }

    // Build the command line for the PHPUnit CLI invocation.
    $command = [
      $this->phpUnitCommand(),
      '--log-junit',
      $logJunitFilePath,
    ];

    // If the deprecation handler bridge is active, we need to fail when there
    // are deprecations that get reported (i.e. not ignored or expected).
    if ($testRun->failOnDeprecation) {
      $command[] = '--fail-on-deprecation';
    }

    // Add to the command the file containing the test class to be run.
    $command[] = $testRun->testFilePath;

    // Execute PHPUnit through a subprocess.
    $status = $this->runPhpUnit($command, $processEnvironmentVariables, $output, $error);

    $testRun->processResults(
      $status,
      $output,
      $error,
      @file_get_contents($logJunitFilePath),
    );

    return $status;
  }

  /**
   * @todo Fix docs.
   */
  public function getTestClassMap(?array $suites = NULL, ?string $extension = NULL, ?string $directory = NULL): array {
    $map = [
      'PHPUnit-FunctionalJavascript' => 'functional-javascript',
      'PHPUnit-Functional' => 'functional',
      'PHPUnit-Kernel' => 'kernel',
      'PHPUnit-Unit' => 'unit',
      'PHPUnit-Build' => 'build',
    ];
    if ($suites !== NULL) {
      $tmp = [];
      foreach ($suites as $i) {
        $tmp[] = $map[$i] ?? $i;
      }
      $suites = $tmp;
    }

    $xmlOutputFile = $this->workingDirectory . DIRECTORY_SEPARATOR . 'test-list.xml';
    touch($xmlOutputFile);
    $realXmlOutputFile = realpath($xmlOutputFile);
    $command = [
      $this->phpUnitCommand(),
      '--list-tests-xml',
      $realXmlOutputFile,
    ];

    if ($suites !== NULL) {
      $command[] = '--testsuite';
      $command[] = implode(',', $suites);
    }

    $status = $this->runPhpUnit($command, [], $output, $error);

    $phpUnitXmlList = new \DOMDocument();
    $phpUnitXmlList->loadXML(file_get_contents($realXmlOutputFile));
    @unlink($realXmlOutputFile);

    $phpUnitList = [];
    foreach ($phpUnitXmlList->getElementsByTagName('testCaseClass') as $node) {
      $class = $node->getAttribute('name');
      if ($extension !== NULL) {
        // @todo To be completed.
        continue;
      }
      $file = $this->classLoader->findFile($class);
      if ($directory !== NULL && !str_contains($file, $directory)) {
        continue;
      }
      $phpUnitList[$class] = $file;
    }

    return $phpUnitList;
  }

}
