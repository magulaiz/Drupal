<?php

namespace Drupal\ckeditor5_incompatible_filter_test\Plugin\Filter;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\filter\Attribute\Filter;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\FilterType;
use Drupal\filter\Plugin\FilterBase;

/**
 * Provides a filter incompatible with CKEditor 5.
 */
#[Filter(
  id: "filter_incompatible",
  title: new TranslatableMarkup("A MarkupLanguage filter incompatible with CKEditor 5"),
  type: FilterType::MarkupLanguage
)]
class FilterIsIncompatible extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    return new FilterProcessResult($text);
  }

}
