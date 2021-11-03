<?php

namespace Drupal\drupal_autocomplete_test\Form;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for testing autocomplete options.
 */
class AutocompleteTestForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'drupal_autocomplete_test_form';
  }

  /**
   * {@inheritdoc}
   *
   * This form tests the various options that can be used to configure an
   * instance of the A11yAutocomplete JavaScript class. Options can be set
   * directly on an element in two ways:
   * - Using a data-autocomplete-(dash separated option name) attribute.
   *   ex: data-autocomplete-min-chars="2"
   * - The data-autocomplete attribute has a JSON string with all custom
   *   options. The option properties are camel cased.
   *   ex: data-autocomplete="{"minChars": 2}"
   * Every option tested via this form has a version implemented via
   * data-autocomplete={option: value} and another version implemented via
   * data-autocomplete-(dash separated option name).
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Inputs with the minimum characters option.
    $form['two_minChar_data_autocomplete'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Minimum 2 Characters data-autocomplete'),
      '#default_value' => '',
      '#description' => $this->t('This also tests appending minChar screenreader hints to descriptions'),
      '#autocomplete_route_name' => 'drupal_autocomplete.country_autocomplete',
      '#attributes' => [
        'data-autocomplete' => JSON::encode([
          'minChars' => 2,
        ]),
      ],
    ];
    $form['two_minChar_separate_data_attributes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Minimum 2 Characters data-min-char'),
      '#default_value' => '',
      '#autocomplete_route_name' => 'drupal_autocomplete.country_autocomplete',
      '#attributes' => [
        'data-autocomplete-min-chars' => 2,
      ],
    ];

    // Inputs with the first character ignore list option.
    $form['ignore_list_data_autocomplete'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Ignore list "u" data-autocomplete'),
      '#default_value' => '',
      '#description' => $this->t('This also tests appending default screenreader hints to descriptions'),
      '#autocomplete_route_name' => 'drupal_autocomplete.country_autocomplete',
      '#attributes' => [
        'data-autocomplete' => JSON::encode([
          'firstCharacterIgnoreList' => 'u',
        ]),
      ],
    ];
    $form['ignore_list_separate_data_attributes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Ignore list "u" separate data attributes'),
      '#default_value' => '',
      '#description' => $this->t('This also tests appending default screenreader hints to descriptions'),
      '#autocomplete_route_name' => 'drupal_autocomplete.country_autocomplete',
      '#attributes' => [
        'data-autocomplete-first-character-ignore-list' => 'u',
      ],
    ];

    // Inputs that use options to add custom classes.
    $form['custom_classes_data_autocomplete'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom classes data-autocomplete'),
      '#default_value' => '',
      '#autocomplete_route_name' => 'drupal_autocomplete.country_autocomplete',
      '#attributes' => [
        'data-autocomplete' => JSON::encode([
          'classes' => [
            'input' => 'class-added-to-input another-class-added-to-input',
            'listbox' => 'class-added-to-ul another-class-added-to-ul',
            'option' => 'class-added-to-item another-class-added-to-item',
          ],
        ]),
      ],
    ];
    $form['custom_classes_separate_data_attributes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom classes separate data attributes'),
      '#default_value' => '',
      '#autocomplete_route_name' => 'drupal_autocomplete.country_autocomplete',
      '#attributes' => [
        'data-autocomplete-classes-input' => 'class-added-to-input another-class-added-to-input',
        'data-autocomplete-classes-listbox' => 'class-added-to-ul another-class-added-to-ul',
        'data-autocomplete-classes-option' => 'class-added-to-item another-class-added-to-item',
      ],
    ];

    // Inputs with set cardinality and a custom separator.
    $form['cardinality_separator_data_autocomplete'] = [
      '#type' => 'textfield',
      '#title' => $this->t('2 Cardinality data-autocomplete'),
      '#default_value' => '',
      '#autocomplete_route_name' => 'drupal_autocomplete.country_autocomplete',
      '#attributes' => [
        'data-autocomplete' => JSON::encode([
          'cardinality' => '2',
          'separatorChar' => '|',
          'firstCharacterIgnoreList' => '|',
          'allowRepeatValues' => FALSE,
        ]),
      ],
    ];
    $form['cardinality_separator_separate_data_attributes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('2 Cardinality separate data attributes'),
      '#default_value' => '',
      '#autocomplete_route_name' => 'drupal_autocomplete.country_autocomplete',
      '#attributes' => [
        'data-autocomplete-cardinality' => '2',
        'data-autocomplete-separator-char' => '|',
        'data-autocomplete-first-character-ignore-list' => '|',
        'data-autocomplete-allow-repeat-values' => 'false',
      ],
    ];

    $custom_source = [
      [
        'label' => 'Zebra Label',
        'value' => 'Zebra Value',
      ],
      [
        'label' => 'Rhino Label',
        'value' => 'Rhino Value',
      ],
      [
        'label' => 'Cheetah Label',
        'value' => 'Cheetah Value',
      ],
      [
        'label' => 'Meerkat Label',
        'value' => 'Meerkat Value',
      ],
    ];

    // Inputs with a preset list instead of requesting it dynamically.
    $form['preset_list_data_autocomplete'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom list data-autocomplete'),
      '#default_value' => '',
      '#autocomplete_route_name' => 'drupal_autocomplete.country_autocomplete',
      '#attributes' => [
        'data-autocomplete' => JSON::encode([
          'source' => $custom_source,
        ]),
      ],
    ];
    $form['preset_list_separate_data_attributes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom list separate data attributes'),
      '#default_value' => '',
      '#autocomplete_route_name' => 'drupal_autocomplete.country_autocomplete',
      '#attributes' => [
        'data-autocomplete-source' => JSON::encode($custom_source),
      ],
    ];

    $form['#attached']['library'][] = 'core/drupal.autocomplete';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Intentionally empty.
  }

}
