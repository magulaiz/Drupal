<?php

namespace Drupal\Core\Field\Plugin\Field\FieldType;

/**
 * Defines the 'created' entity field type.
 *
 * @FieldType(
 *   id = "created",
 *   label = @Translation("Created"),
 *   description = @Translation("An entity field containing a UNIX timestamp of when the entity has been created."),
 *   no_ui = TRUE,
 *   default_widget = "datetime_timestamp",
 *   default_formatter = "timestamp"
 * )
 */
class CreatedItem extends TimestampItem {

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * {@inheritdoc}
   */
  public function applyDefaultValue($notify = TRUE) {
    parent::applyDefaultValue($notify);
    // Created fields default to the current timestamp.
    $this->setValue(['value' => $this->time()->getRequestTime()], $notify);
    return $this;
  }

  /**
   * Get the time service.
   *
   * @return \Drupal\Component\Datetime\TimeInterface
   *   The time service.
   */
  protected function time() {
    if (!isset($this->time)) {
      $this->time = \Drupal::time();
    }
    return $this->time;
  }

}
