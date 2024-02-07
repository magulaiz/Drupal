<?php

namespace Drupal\Core\Test;

use Drupal\Tests\AssertMailTrait as AssertMailTraitBase;

/**
 * Provides methods for testing emails sent during test runs.
 *
 * @deprecated in drupal:10.3.0 and is removed from drupal:11.0.0. Use the
 *   \Drupal\Tests\AssertMailTrait trait instead.
 */
trait AssertMailTrait {

  use AssertMailTraitBase {
    getMails as getMailsBase;
    assertMail as assertMailBase;
    assertMailString as assertMailStringBase;
    assertMailPattern as assertMailPatternBase;
  }

  /**
   * Gets an array containing all emails sent during this test case.
   *
   * @param array $filter
   *   An array containing key/value pairs used to filter the emails that are
   *   returned.
   *
   * @return array
   *   An array containing email messages captured during the current test.
   */
  protected function getMails(array $filter = []) {
    @trigger_error(__METHOD__ . "() is deprecated in drupal:10.3.0 and is removed from drupal:11.0.0. Use \Drupal\Tests\AssertMailTrait::getMails instead. See https://www.drupal.org/node/????????", E_USER_DEPRECATED);
    return $this->getMailsBase($filter);
  }

  /**
   * Asserts that the most recently sent email message has the given value.
   *
   * The field in $name must have the content described in $value.
   *
   * @param string $name
   *   Name of field or message property to assert. Examples: subject, body,
   *   id, ...
   * @param string $value
   *   Value of the field to assert.
   * @param string $message
   *   (optional) A message to display with the assertion. Do not translate
   *   messages: use \Drupal\Component\Render\FormattableMarkup to embed
   *   variables in the message text, not t(). If left blank, a default message
   *   will be displayed.
   *
   * @return bool
   *   TRUE on pass.
   */
  protected function assertMail($name, $value = '', $message = '') {
    @trigger_error(__METHOD__ . "() is deprecated in drupal:10.3.0 and is removed from drupal:11.0.0. Use \Drupal\Tests\AssertMailTrait::assertMail instead. See https://www.drupal.org/node/????????", E_USER_DEPRECATED);
    return $this->assertMailBase($name, $value, $message);
  }

  /**
   * Asserts that the most recently sent email message has the string in it.
   *
   * @param string $field_name
   *   Name of field or message property to assert: subject, body, id, ...
   * @param string $string
   *   String to search for.
   * @param int $email_depth
   *   Number of emails to search for string, starting with most recent.
   * @param string $message
   *   (optional) A message to display with the assertion. Do not translate
   *   messages: use \Drupal\Component\Render\FormattableMarkup to embed
   *   variables in the message text, not t(). If left blank, a default message
   *   will be displayed.
   */
  protected function assertMailString($field_name, $string, $email_depth, $message = '') {
    @trigger_error(__METHOD__ . "() is deprecated in drupal:10.3.0 and is removed from drupal:11.0.0. Use \Drupal\Tests\AssertMailTrait::assertMailString instead. See https://www.drupal.org/node/????????", E_USER_DEPRECATED);
    $this->assertMailStringBase($field_name, $string, $email_depth, $message);
  }

  /**
   * Asserts that the most recently sent email message has the pattern in it.
   *
   * @param string $field_name
   *   Name of field or message property to assert: subject, body, id, ...
   * @param string $regex
   *   Pattern to search for.
   * @param string $message
   *   (optional) A message to display with the assertion. Do not translate
   *   messages: use \Drupal\Component\Render\FormattableMarkup to embed
   *   variables in the message text, not t(). If left blank, a default message
   *   will be displayed.
   */
  protected function assertMailPattern($field_name, $regex, $message = '') {
    @trigger_error(__METHOD__ . "() is deprecated in drupal:10.3.0 and is removed from drupal:11.0.0. Use \Drupal\Tests\AssertMailTrait::assertMailPattern instead. See https://www.drupal.org/node/????????", E_USER_DEPRECATED);
    $this->assertMailPatternBase($field_name, $regex, $message);
  }

}
