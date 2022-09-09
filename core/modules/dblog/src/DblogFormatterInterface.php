<?php

namespace Drupal\dblog;

use Drupal\Component\Render\MarkupInterface;

/**
 * Interface to format dblog entries.
 */
interface DblogFormatterInterface {

  /**
   * Formats a dblog entry.
   *
   * @param string $message
   *   A string containing placeholders. The string itself will not be escaped,
   *   any unsafe content must be in $args and inserted via placeholders.
   * @param array $variables
   *   An array with placeholder replacements, keyed by placeholder.
   * @param string|null $backtrace_string
   *   (optional) The key of $variables array that contains the log backtrace.
   *
   * @return \Drupal\Component\Render\MarkupInterface
   *   The markup of the rendered event.
   */
  public function format(string $message, array $variables, ?string $backtrace_string = NULL) : MarkupInterface;

}
