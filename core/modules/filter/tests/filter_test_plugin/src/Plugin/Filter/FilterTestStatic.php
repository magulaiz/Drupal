<?php

namespace Drupal\filter_test_plugin\Plugin\Filter;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\filter\Attribute\Filter;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\FilterType;
use Drupal\filter\Plugin\FilterBase;

/**
 * Provides a filter that returns the same static text.
 */
#[Filter(
  id: "filter_static_text",
  title: new TranslatableMarkup("Static filter"),
  type: FilterType::HtmlRestrictor,
  settings: [],
)]
class FilterTestStatic extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    return new FilterProcessResult('filtered text');
  }

}
