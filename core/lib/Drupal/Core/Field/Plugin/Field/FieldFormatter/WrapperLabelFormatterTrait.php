<?php

namespace Drupal\Core\Field\Plugin\Field\FieldFormatter;

/**
 * Trait to help formatters has the ability to wrap labels in a heading tag.
 */
trait WrapperLabelFormatterTrait {

  /**
   * Alter form elements adding wrap label tag config.
   *
   * @param array $elements
   *   Form elements.
   */
  public function settingsFormWrapperOption(array &$elements = []) {
    $elements['wrap_label_tag'] = [
      '#type' => 'select',
      '#options' => [
        NULL => $this->t('--None--'),
        'h2' => $this->t('h2'),
        'h3' => $this->t('h3'),
        'h4' => $this->t('h4'),
        'h5' => $this->t('h5'),
        'h6' => $this->t('h6'),
      ],
      '#description' => $this->t('Choose which tag will wrap the label element.'),
      '#title' => 'Heading text.',
      '#default_value' => $this->getSetting('wrap_label_tag') ?? "div",
    ];
  }

  /**
   * Default configuration for wrap default settings.
   *
   * @return null[]
   */
  public static function wrapperLabelDefaultSettings() {
    return [
      'wrap_label_tag' => NULL,
    ];
  }

}
