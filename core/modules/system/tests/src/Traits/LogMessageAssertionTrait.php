<?php

namespace Drupal\Tests\system\Traits;

use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Database\Database;

trait LogMessageAssertionTrait {

  /**
   * Verify a log entry was entered for a module's status change.
   *
   * @param $type
   *   The category to which this message belongs.
   * @param $message
   *   The message to store in the log. Keep $message translatable
   *   by not concatenating dynamic values into it! Variables in the
   *   message should be added by using placeholder strings alongside
   *   the variables argument to declare the value of the placeholders.
   *   See t() for documentation on how $message and $variables interact.
   * @param $variables
   *   Array of variables to replace in the message on display or
   *   NULL if message is already translated or not possible to
   *   translate.
   * @param $severity
   *   The severity of the message, as per RFC 3164.
   * @param $link
   *   A link to associate with the message.
   */
  public function assertLogMessage($type, $message, $variables = [], $severity = RfcLogLevel::NOTICE, $link = '') {
    $this->assertNotEmpty(Database::getConnection()->select('watchdog', 'w')
      ->condition('type', $type)
      ->condition('message', $message)
      ->condition('variables', serialize($variables))
      ->condition('severity', $severity)
      ->condition('link', $link)
      ->countQuery()
      ->execute()
      ->fetchField()
    );
  }

}
