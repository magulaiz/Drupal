<?php

namespace Drupal\layout_builder\Plugin\DataType;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\Attribute\DataType;
use Drupal\Core\TypedData\TypedData;
use Drupal\layout_builder\Section;

/**
 * Provides a data type wrapping \Drupal\layout_builder\Section.
 *
 * @internal
 *   Plugin classes are internal.
 */
#[DataType(
  id: "layout_section",
  label: new TranslatableMarkup("Layout Section"),
  description: new TranslatableMarkup("A layout section"),
)]
class SectionData extends TypedData {

  /**
   * The section object.
   *
   * @var \Drupal\layout_builder\Section
   */
  protected $value;

  /**
   * {@inheritdoc}
   */
  public function getValue() {
    return $this->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($value, $notify = TRUE) {
    if ($value && !$value instanceof Section) {
      throw new \InvalidArgumentException(sprintf('Value assigned to "%s" is not a valid section', $this->getName()));
    }

    $this->value = $value;
    // Notify the parent of any changes.
    if ($notify && isset($this->parent)) {
      $this->parent->onChange($this->name);
    }
  }

}
