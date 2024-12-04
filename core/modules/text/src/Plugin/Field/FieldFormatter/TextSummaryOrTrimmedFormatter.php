<?php

namespace Drupal\text\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\Plugin\DataType\StringData;
use Drupal\text\Plugin\Field\FieldType\TextWithSummaryItem;

/**
 * Plugin implementation of the 'text_summary_or_trimmed' formatter.
 */
#[FieldFormatter(
  id: 'text_summary_or_trimmed',
  label: new TranslatableMarkup('Summary or trimmed'),
  field_types: [
    'text_with_summary',
  ],
)]
class TextSummaryOrTrimmedFormatter extends TextTrimmedFormatter {

  /**
   * {@inheritdoc}
   */
  protected function createSummary(array &$element, FieldItemInterface $item) {
    if (!$item instanceof TextWithSummaryItem) {
      parent::createSummary($element, $item);
    }
    else {
      $summary = $item->get('summary');
      if ($summary instanceof StringData && $summaryValue = $summary->getValue()) {
        $element['#text'] = $summaryValue;
      }
      else {
        parent::createSummary($element, $item);
      }
    }
  }

}
