<?php

namespace Drupal\filter_test\Plugin\Filter;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\filter\Attribute\Filter;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\FilterType;
use Drupal\filter\Plugin\FilterBase;

/**
 * Provides a test filter to attach assets.
 */
#[Filter(
  id: "filter_test_assets",
  title: new TranslatableMarkup("Testing filter"),
  type: FilterType::TransformReversible,
  description: new TranslatableMarkup("Does not change content; attaches assets.")
)]
class FilterTestAssets extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);
    $result->addAttachments([
      'library' => [
        'filter/caption',
      ],
    ]);
    return $result;
  }

}
