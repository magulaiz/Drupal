<?php

namespace Drupal\filter\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\filter\Attribute\Filter;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\FilterType;
use Drupal\filter\Plugin\FilterBase;

/**
 * Provides a filter to correct faulty and chopped off HTML.
 */
#[Filter(
  id: "filter_htmlcorrector",
  title: new TranslatableMarkup("Correct faulty and chopped off HTML"),
  type: FilterType::TransformIrreversible,
  weight: 10
)]
class FilterHtmlCorrector extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    return new FilterProcessResult(Html::normalize($text));
  }

}
