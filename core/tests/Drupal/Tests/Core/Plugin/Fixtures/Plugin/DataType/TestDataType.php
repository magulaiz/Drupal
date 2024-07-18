<?php

declare(strict_types=1);

namespace Drupal\Tests\Core\Plugin\Fixtures\Plugin\DataType;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\Attribute\DataType;
use Drupal\Core\TypedData\TypedData;

/**
 * Provides a test data type.
 */
#[DataType(
  id: "test_data_type",
  label: new TranslatableMarkup("Test data type"),
  deriver: TestDataTypeDeriver::class,
)]
class TestDataType extends TypedData {

  /**
   * Required by the parent class.
   *
   * @var mixed
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
    $this->value = $value;
    // Notify the parent of any changes.
    if ($notify && isset($this->parent)) {
      $this->parent->onChange($this->name);
    }
  }

}
