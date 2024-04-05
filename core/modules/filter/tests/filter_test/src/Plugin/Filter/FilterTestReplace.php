<?php

namespace Drupal\filter_test\Plugin\Filter;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\filter\Attribute\Filter;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\FilterType;
use Drupal\filter\Plugin\FilterBase;

/**
 * Provides a test filter to replace all content.
 */
#[Filter(
  id: "filter_test_replace",
  title: new TranslatableMarkup("Testing filter"),
  type: FilterType::TransformIrreversible,
  description: new TranslatableMarkup("Replaces all content with filter and text format information.")
)]
class FilterTestReplace extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $text = [];
    $text[] = 'Filter: ' . $this->getLabel() . ' (' . $this->getPluginId() . ')';
    $text[] = 'Language: ' . $langcode;
    return new FilterProcessResult(implode("<br />\n", $text));
  }

}
