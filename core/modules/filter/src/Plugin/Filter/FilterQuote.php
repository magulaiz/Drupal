<?php

namespace Drupal\filter\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\filter\Attribute\Filter;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\filter\Plugin\FilterInterface;

/**
 * Provides a filter to distinguish between long and short quotes.
 */
#[Filter(
  id: "filter_quote",
  title: new TranslatableMarkup("Distinguish long and short quotes"),
  description: new TranslatableMarkup("Uses a <code>&lt;q&gt;</code> tag to wrap single line quotes and a <code>&lt;blockquote&gt;</code> tag to wrap those containing multi-lines"),
  type: FilterInterface::TYPE_TRANSFORM_IRREVERSIBLE,
  weight: 10
)]
class FilterQuote extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode): FilterProcessResult {
    $result = new FilterProcessResult($text);

    // If there are no blockquotes, return early.
    if (stripos($text, '<blockquote>') === FALSE) {
      return $result;
    }

    return $result->setProcessedText($this->transformBlockquotes($text));
  }

  /**
   * Transform markup of quotes to use <q> or <blockquote> wrapper.
   *
   * @param string $text
   *   The markup to transform.
   *
   * @return string
   *   The transformed text.
   */
  private function transformBlockquotes($text): string {
    $dom = Html::load($text);
    $xpath = new \DOMXPath($dom);
    foreach ($xpath->query('//blockquote') as $element) {
      assert($element instanceof \DOMElement);
      // Keep a <blockquote> tag if element contains multiple <p> tags or
      // at least one <br> tag.
      if ($element->getElementsByTagName('p')->length > 1 || $element->getElementsByTagName('br')->length > 0) {
        continue;
      }
      // Replace <blockquote> tag with a <q> tag.
      $qTag = $dom->createElement('q');
      foreach ($element->childNodes as $childElement) {
        $qTag->appendChild($childElement->cloneNode(TRUE));
      }
      $element->replaceWith($qTag);
    }
    return Html::serialize($dom);
  }

}
