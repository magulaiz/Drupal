<?php

declare(strict_types=1);

namespace Drupal\Tests\Core\Test;

use Drupal\Core\Test\JUnitConverter;
use Drupal\Tests\UnitTestCase;
use org\bovigo\vfs\vfsStream;

/**
 * Tests Drupal\Core\Test\JUnitConverter.
 *
 * This test class has significant overlap with
 * Drupal\Tests\simpletest\Kernel\PhpUnitErrorTest.
 *
 * @coversDefaultClass \Drupal\Core\Test\JUnitConverter
 *
 * @group Test
 * @group simpletest
 *
 * @see \Drupal\Tests\simpletest\Kernel\PhpUnitErrorTest
 */
class JUnitConverterTest extends UnitTestCase {

  /**
   * Tests errors reported.
   *
   * @covers ::xmlToRows
   */
  public function testXmlToRowsWithErrors() {
    $phpunit_error_xml = __DIR__ . '/fixtures/phpunit_error.xml';

    $res = JUnitConverter::xmlToRows(1, $phpunit_error_xml);
    $this->assertCount(4, $res, 'All test cases got extracted');
    $this->assertNotEquals('pass', $res[0]['status']);
    $this->assertEquals('fail', $res[0]['status']);

    // Test nested testsuites, which appear when you use @dataProvider.
    for ($i = 0; $i < 3; $i++) {
      $this->assertNotEquals('pass', $res[$i + 1]['status']);
      $this->assertEquals('fail', $res[$i + 1]['status']);
    }

    // Make sure xmlToRows() does not balk if there are no test results.
    $this->assertSame([], JUnitConverter::xmlToRows(1, 'does_not_exist'));
  }

  /**
   * @covers ::xmlToRows
   */
  public function testXmlToRowsEmptyFile() {
    // File system with an empty XML file.
    vfsStream::setup('junit_test', NULL, ['empty.xml' => '']);
    $this->assertSame([], JUnitConverter::xmlToRows(23, vfsStream::url('junit_test/empty.xml')));
  }

  /**
   * @covers ::xmlElementToRows
   */
  public function testXmlElementToRows() {
    $junit = <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="Drupal\Tests\simpletest\Unit\TestDiscoveryTest" file="/Users/paul/projects/drupal/core/modules/simpletest/tests/src/Unit/TestDiscoveryTest.php" tests="3" assertions="5" errors="0" failures="0" skipped="0" time="0.215539">
    <testcase name="testGetTestClasses" class="Drupal\Tests\simpletest\Unit\TestDiscoveryTest" classname="Drupal.Tests.simpletest.Unit.TestDiscoveryTest" file="/Users/paul/projects/drupal/core/modules/simpletest/tests/src/Unit/TestDiscoveryTest.php" line="108" assertions="2" time="0.100787"/>
  </testsuite>
</testsuites>
EOD;
    $simpletest = [
      [
        'test_id' => 23,
        'test_class' => 'Drupal\Tests\simpletest\Unit\TestDiscoveryTest',
        'status' => 'pass',
        'message' => '',
        'message_group' => 'Other',
        'function' => 'Drupal\Tests\simpletest\Unit\TestDiscoveryTest->testGetTestClasses()',
        'line' => 108,
        'file' => '/Users/paul/projects/drupal/core/modules/simpletest/tests/src/Unit/TestDiscoveryTest.php',
      ],
    ];
    $this->assertEquals($simpletest, JUnitConverter::xmlElementToRows(23, new \SimpleXMLElement($junit)));
  }

  /**
   * @covers ::convertTestCaseToSimpletestRow
   *
   * @dataProvider simpletestDataProvider
   */
  public function testConvertTestCaseToSimpletestRow($junit, $simpletest): void {
    $this->assertEquals($simpletest, JUnitConverter::convertTestCaseToSimpletestRow($simpletest['test_id'], new \SimpleXMLElement($junit)));
    $this->assertLessThanOrEqual(255, strlen($simpletest['function']), 'Function value is less than or equal to 255');
  }

  /**
   * See testConvertTestCaseToSimpletestRow method for test cases.
   */
  public static function simpletestDataProvider(): array {
    // @todo once $this obj accessible in data provider static method
    // https://www.drupal.org/node/3421393, replace generateAlphanumericStr()
    // with $this->randomMachineName(), remove generateAlphanumericStr()
    $long_function_name = self::generateAlphanumericStr(220);
    return [
      [
        <<<EOD
        <testcase name="testGetTestClasses" class="Drupal\Tests\simpletest\Unit\TestDiscoveryTest" classname="Drupal.Tests.simpletest.Unit.TestDiscoveryTest" file="/Users/paul/projects/drupal/core/modules/simpletest/tests/src/Unit/TestDiscoveryTest.php" line="108" assertions="2" time="0.100787"/>
        EOD,
        [
          'test_id' => 23,
          'test_class' => 'Drupal\Tests\simpletest\Unit\TestDiscoveryTest',
          'status' => 'pass',
          'message' => '',
          'message_group' => 'Other',
          'function' => 'Drupal\Tests\simpletest\Unit\TestDiscoveryTest->testGetTestClasses()',
          'line' => 108,
          'file' => '/Users/paul/projects/drupal/core/modules/simpletest/tests/src/Unit/TestDiscoveryTest.php',
        ],
      ],
      [
        <<<EOD
        <testcase name="{$long_function_name}" class="Drupal\Tests\big_pipe\Unit\Render\BigPipeResponseAttachmentsProcessorTest" classname="Drupal.Tests.big_pipe.Unit.Render.BigPipeResponseAttachmentsProcessorTest" file="/Users/paul/projects/drupal/core/modules/big_pipe/tests/src/Unit/Render/BigPipeResponseAttachmentsProcessorTest.php" line="83" assertions="4" time="0.100787"/>
        EOD,
        [
          'test_id' => 24,
          'test_class' => 'Drupal\Tests\big_pipe\Unit\Render\BigPipeResponseAttachmentsProcessorTest',
          'status' => 'pass',
          'message' => '',
          'message_group' => 'Other',
          'function' => mb_substr("Drupal\Tests\big_pipe\Unit\Render\BigPipeResponseAttachmentsProcessorTest->{$long_function_name}()", 0, 255),
          'line' => 83,
          'file' => '/Users/paul/projects/drupal/core/modules/big_pipe/tests/src/Unit/Render/BigPipeResponseAttachmentsProcessorTest.php',
        ],
      ],
    ];

  }

  /**
   * Generates a string consisting of alphanumeric characters.
   */
  private static function generateAlphanumericStr($length = 220) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $charLength - 1)];
    }
    return $randomString;
  }

}
