<?php

namespace Drupal\dblog;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Component\Utility\Xss;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Render\MarkupInterface;

/**
 * Service to format dblog entries.
 */
class DblogFormatterTranslatable implements DblogFormatterInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function format(string $message, ?array $variables, ?string $backtrace_string = NULL) : MarkupInterface {
    if (empty($message) && empty($variables)) {
      return new FormattableMarkup('', []);
    }

    if (!is_array($variables)) {
      return $this->t('Log data is corrupted and cannot be unserialized: @message', ['@message' => Xss::filterAdmin($message)]);
    }

    // Ensure backtrace strings are properly formatted.
    if (!empty($backtrace_string) && isset($variables[$backtrace_string])) {
      $variables[$backtrace_string] = new FormattableMarkup(
        sprintf('<pre class="backtrace">@%s</pre>', $backtrace_string), $variables
      );
    }
    return $this->t(Xss::filterAdmin($message), $variables);
  }

}
