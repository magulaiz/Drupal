<?php

namespace Drupal\Component\Utility;

use Masterminds\HTML5\Serializer\OutputRules;

/**
 * Drupal-specific HTML5 serializer rules.
 */
class HtmlSerializerRules extends OutputRules {

  /**
   * {@inheritdoc}
   */
  protected function escape($text, $attribute = FALSE) {
    // Additionally escape tag start and end characters in attributes values,
    // in order to avoid triggering XSS filters.
    if ($attribute) {
      return strtr($text, [
        '<' => '&lt;',
        '>' => '&gt;',
        '"' => '&quot;',
        '&' => '&amp;',
        "\xc2\xa0" => '&nbsp;',
      ]);
    }

    return parent::escape($text, $attribute);
  }

}
