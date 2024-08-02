<?php

namespace Drupal\Core\Test;

use Drupal\TestTools\PhpUnitTestCaseResult;

/**
 * Converts JUnit XML to Drupal's {simpletest} schema.
 *
 * This is mainly for converting PHPUnit test results.
 *
 * This class is @internal and not considered to be API.
 */
class JUnitConverter {

  /**
   * Converts PHPUnit's JUnit XML output file to {simpletest} schema.
   *
   * @param int $test_id
   *   The current test ID.
   * @param string $phpunit_xml_file
   *   Path to the PHPUnit XML file.
   *
   * @return array[]
   *   The results as array of rows in a format that can be inserted into the
   *   {simpletest} table of the results database.
   *
   * @internal
   */
  public static function xmlToRows($test_id, $phpunit_xml_file) {
    $contents = @file_get_contents($phpunit_xml_file);
    if (!$contents) {
      return [];
    }
    return static::xmlElementToRows($test_id, new \SimpleXMLElement($contents));
  }

  /**
   * Parse test cases from XML to {simpletest} schema.
   *
   * @param int $test_id
   *   The current test ID.
   * @param \SimpleXMLElement $element
   *   The XML data from the JUnit file.
   *
   * @return array[]
   *   The results as array of rows in a format that can be inserted into the
   *   {simpletest} table of the results database.
   *
   * @internal
   */
  public static function xmlElementToRows($test_id, \SimpleXMLElement $element) {
    $records = [];
    $test_cases = static::findTestCases($element);
    foreach ($test_cases as $test_case) {
      $records[] = static::convertTestCaseToSimpletestRow($test_id, $test_case);
    }
    return $records;
  }

  /**
   * Finds all test cases recursively from a test suite list.
   *
   * @param \SimpleXMLElement $element
   *   The PHPUnit xml to search for test cases.
   * @param \SimpleXMLElement $parent
   *   (Optional) The parent of the current element. Defaults to NULL.
   *
   * @return array
   *   A list of all test cases.
   *
   * @internal
   */
  public static function findTestCases(\SimpleXMLElement $element, ?\SimpleXMLElement $parent = NULL) {
    if ($element->getName() === 'testcase') {
      return [$element];
    }

    $test_cases = [];
    foreach ($element as $child) {
      $test_cases[] = static::findTestCases($child, $element);
    }
    return array_merge(...$test_cases);
  }

  /**
   * Converts a PHPUnit test case result to a {simpletest} result row.
   *
   * @param int $test_id
   *   The current test ID.
   * @param \SimpleXMLElement $test_case
   *   The PHPUnit test case represented as XML element.
   *
   * @return array
   *   An array containing the {simpletest} result row.
   *
   * @internal
   */
  public static function convertTestCaseToSimpletestRow($test_id, \SimpleXMLElement $test_case) {
    $status = static::getStatus($test_case);

    $message = '';
    if ($status == PhpUnitTestCaseResult::Fail) {
      $lines = explode("\n", $test_case->failure);
      $message = $lines[2];
    }
    elseif ($status == PhpUnitTestCaseResult::Error) {
      $message = $test_case->error[0];
    }
    elseif ($status == PhpUnitTestCaseResult::Skip) {
      $message = 'Skipped';
    }
    elseif ($status == PhpUnitTestCaseResult::Incomplete) {
      $message = 'Incomplete';
    }

    $attributes = $test_case->attributes();

    $record = [
      'test_id' => $test_id,
      'test_class' => (string) $attributes->class,
      'status' => $status->value,
      'message' => $message,
      'message_group' => 'Other',
      'function' => $attributes->class . '->' . $attributes->name . '()',
      'line' => (int) $attributes->line ?: 0,
      'file' => (string) $attributes->file,
      'time' => (float) $attributes->time,
    ];
    return $record;
  }

  /**
   * Determine a status string for the given testcase.
   *
   * @param \SimpleXMLElement $test_case
   *   The test case XML element.
   *
   * @return \Drupal\TestTools\PhpUnitTestCaseResult
   *   The status value to insert into the {simpletest} record.
   */
  protected static function getStatus(\SimpleXMLElement $test_case): PhpUnitTestCaseResult {
    if ($test_case->error || $test_case->risky) {
      return PhpUnitTestCaseResult::Error;
    }
    if ($test_case->failure) {
      return PhpUnitTestCaseResult::Fail;
    }
    if ($test_case->skipped) {
      return PhpUnitTestCaseResult::Skip;
    }
    return PhpUnitTestCaseResult::Pass;
  }

}
