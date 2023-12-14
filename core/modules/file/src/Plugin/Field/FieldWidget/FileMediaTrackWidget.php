<?php

namespace Drupal\file\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Render\ElementInfoManagerInterface;
use Drupal\file\Plugin\Field\FieldType\FileMediaTrackItem;

/**
 * Plugin implementation of the 'media_track' widget.
 *
 * @FieldWidget(
 *   id = "media_track",
 *   label = @Translation("Media Track"),
 *   field_types = {
 *     "media_track"
 *   }
 * )
 */
class FileMediaTrackWidget extends FileWidget {

  /**
   * Constructs a MediaTrackWidget object.
   *
   * @param string $plugin_id
   *   The plugin_id for the widget.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Render\ElementInfoManagerInterface $element_info
   *   The element info manager service.
   *
   * @todo Is this constructor necessary? Remove if it doesn't need to differ from parent.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, ElementInfoManagerInterface $element_info) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings, $element_info);
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'progress_indicator' => 'throbber',
    ] + parent::defaultSettings();
  }

  /**
   * Overrides \Drupal\file\Plugin\Field\FieldWidget\FileWidget::formMultipleElements().
   *
   * Special handling for draggable multiple widgets and 'add more' button.
   */
  protected function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $elements = parent::formMultipleElements($items, $form, $form_state);

    $cardinality = $this->fieldDefinition->getFieldStorageDefinition()->getCardinality();
    $file_upload_help = [
      '#theme' => 'file_upload_help',
      '#description' => '',
      '#upload_validators' => $elements[0]['#upload_validators'],
      '#cardinality' => $cardinality,
    ];
    if ($cardinality == 1) {
      // If there's only one field, return it as delta 0.
      if (empty($elements[0]['#default_value']['fids'])) {
        $file_upload_help['#description'] = $this->getFilteredDescription();
        $elements[0]['#description'] = \Drupal::service('renderer')->renderPlain($file_upload_help);
      }
    }
    else {
      $elements['#file_upload_description'] = $file_upload_help;
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   *
   * Add the field settings so they can be used in the process method.
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['#field_settings'] = $this->getFieldSettings();

    return $element;
  }

  /**
   * Form API callback: Processes a media_track field element.
   *
   * Expands the media_track type to include the alt and title fields.
   *
   * This method is assigned as a #process callback in formElement() method.
   */
  public static function process($element, FormStateInterface $form_state, $form) {
    $item = $element['#value'];
    $item['fids'] = $element['fids']['#value'];

    $element['label'] = [
      '#title' => t('Label'),
      '#type' => 'textfield',
      '#default_value' => isset($item['label']) ? $item['label'] : '',
      '#description' => t('Label for the track file.'),
      '#maxlength' => 128,
      '#access' => (bool) $item['fids'],
    ];

    // Reduce the list of options to only those allowed by the field's
    // configuration.
    $kind_options = FileMediaTrackItem::getKindOptions();
    foreach (array_diff(array_keys($kind_options), $element['#field_settings']['kinds']) as $key) {
      unset($kind_options[$key]);
    }

    $element['kind'] = [
      '#title' => t('Kind'),
      '#type' => 'select',
      '#description' => t('The kind of media track.'),
      '#options' => $kind_options,
      '#default_value' => $item['kind'] ?? '',
      '#access' => (bool) $item['fids'],
    ];

    $srclang_options = [];
    if ($element['#field_settings']['languages'] == 'all') {
      // Need to list all languages.
      $languages = LanguageManager::getStandardLanguageList();
      foreach ($languages as $key => $language) {
        if ($language[0] == $language[1]) {
          // Both the native language name and the English language name are
          // the same, so only show one of them.
          $srclang_options[$key] = $language[0];
        }
        else {
          // The native language name and English language name are different
          // so show both of them.
          $srclang_options[$key] = t('@lang0 / @lang1', [
            '@lang0' => $language[0],
            '@lang1' => $language[1],
          ]);
        }
      }
    }
    else {
      // Only list the installed languages.
      $languages = \Drupal::languageManager()->getLanguages();
      foreach ($languages as $key => $language) {
        $srclang_options[$key] = $language->getName();
      }
    }

    $element['srclang'] = [
      '#title' => t('SRC Language'),
      '#description' => t('Choose from one of the installed languages.'),
      '#type' => 'select',
      '#options' => $srclang_options,
      '#default_value' => $item['srclang'] ?? '',
      '#maxlength' => 20,
      '#access' => (bool) $item['fids'],
      '#element_validate' => [[get_called_class(), 'validateRequiredFields']],
    ];
    // @todo checkbox or radio? Note this has unusual validation requirements!
    // @see https://www.w3.org/TR/html/semantics-embedded-content.html#elementdef-track
    $element['default'] = [
      '#type' => 'checkbox',
      '#title' => t('Default track'),
      '#default_value' => $item['default'] ?? '',
      '#description' => t('Use this as the default track of this kind.'),
      '#access' => (bool) $item['fids'],
    ];

    return parent::process($element, $form_state, $form);
  }

  /**
   * Validate callback for kind/srclang/label/default.
   *
   * This is separated in a validate function instead of a #required flag to
   * avoid being validated on the process callback.
   *
   * @todo Do we actually need anything here? This is copied from ImageWidget.
   * Haven't yet decided if the extra inputs on media track are to be validated.
   * The 'default' track boolean has complex validation, see the HTML5.2
   * rec for details.
   */
  public static function validateRequiredFields($element, FormStateInterface $form_state) {
    // Only do validation if the function is triggered from other places than
    // the image process form.
    $triggering_element = $form_state->getTriggeringElement();
    if (!empty($triggering_element['#submit']) && in_array('file_managed_file_submit', $triggering_element['#submit'], TRUE)) {
      $form_state->setLimitValidationErrors([]);
    }
  }

}
