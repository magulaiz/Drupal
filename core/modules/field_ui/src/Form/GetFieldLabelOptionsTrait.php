<?php

namespace Drupal\field_ui\Form;

/**
 * Method getFieldLabelOptions originally from EntityViewDisplayEditForm file.
 */
trait GetFieldLabelOptionsTrait {

  /**
   * Returns an array of visibility options for field labels.
   *
   * @return array
   *   An array of visibility options.
   */
  public function getFieldLabelOptions(): array {
    return [
      'above' => $this->t('Above'),
      'inline' => $this->t('Inline'),
      'hidden' => '- ' . $this->t('Hidden') . ' -',
      'visually_hidden' => '- ' . $this->t('Visually Hidden') . ' -',
    ];
  }

}
