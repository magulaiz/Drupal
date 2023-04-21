<?php

namespace Drupal\Tests\Core\Error;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\Process\PhpProcess;

/**
 * Tests logging of errors in core/error.inc.
 *
 * @group Error
 */
class DrupalLogErrorTest extends UnitTestCase {

  /**
   * Tests that fatal errors return a non-zero exit code.
   *
   * @dataProvider provideFatalExitCodeData
   */
  public function testFatalExitCode(string $script, string $output, string $errorOutput) {
    // We need to override the current working directory for invocations from
    // run-tests.sh to work properly.
    $process = new PhpProcess($script, $this->root);
    $process->run();

    // Assert the output strings as unrelated errors (like the log-exit.php
    // script throwing a PHP error) would still pass the final assertion.
    $this->assertEquals($output, $process->getOutput());
    $this->assertEquals($errorOutput, $process->getErrorOutput());
    $this->assertFalse($process->isSuccessful());
  }

  public function provideFatalExitCodeData() {
    $verbose = "\$GLOBALS['config']['system.logging']['error_level'] = 'verbose';";
    $scriptBody = $this->getScriptBody();
    $data['normal'] = [
      "<?php\n$scriptBody",
      "kernel test: This is a test message in test_function (line 456 of test.module).\n",
      "kernel test: This is a test message in test.module on line 456 backtrace\nand-more-backtrace\n",
    ];
    $data['verbose'] = [
      "<?php\n$verbose\n$scriptBody",
      "<details class=\"error-with-backtrace\"><summary>kernel test: This is a test message in test_function (line 456 of test.module).</summary>backtrace<br>and-more-backtrace\n</details>\n",
      "kernel test: This is a test message in test.module on line 456 backtrace\nand-more-backtrace\n",
    ];
    return $data;
  }

  protected function getScriptBody() {
    return <<<'EOT'
if (PHP_SAPI !== 'cli') {
  return;
}

$autoloader = require_once 'autoload.php';
require_once 'core/includes/errors.inc';
define('DRUPAL_TEST_IN_CHILD_SITE', FALSE);

$error = [
  '%type' => 'kernel test',
  '@message' => 'This is a test message',
  '%function' => 'test_function',
  '%file' => 'test.module',
  '%line' => 456,
  '@backtrace_string' => "backtrace\nand-more-backtrace",
  'severity_level' => 0,
  'backtrace' => [],
  'exception' => NULL,
];
_drupal_log_error($error, TRUE);
EOT;
  }

}
