<?php

namespace Drupal\filter\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * Provides a filter to display any HTML as plain text.
 *
 * @Filter(
 *   id = "filter_html_escape",
 *   title = @Translation("Display any HTML as plain text"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_HTML_RESTRICTOR,
 *   weight = -10
 * )
 */
class FilterHtmlEscape extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    return new FilterProcessResult(trim(Html::escape($text)));
  }

  /**
   * {@inheritdoc}
   */
  public function getHTMLRestrictions() {
    // Nothing is allowed.
    return ['allowed' => []];
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    return $this->t('No HTML tags allowed.');
  }

}
