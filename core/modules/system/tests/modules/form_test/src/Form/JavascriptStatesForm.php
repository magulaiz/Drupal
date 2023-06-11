<?php

namespace Drupal\form_test\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Builds a simple form to test states.
 *
 * @see \Drupal\FunctionalJavascriptTests\Core\Form\JavascriptStatesTest
 */
class JavascriptStatesForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'javascript_states_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['checkbox_trigger'] = [
      '#type' => 'checkbox',
      '#title' => 'Checkbox trigger',
    ];
    $form['textfield_trigger'] = [
      '#type' => 'textfield',
      '#title' => 'Textfield trigger',
    ];
    $form['radios_opposite1'] = [
      '#type' => 'radios',
      '#title' => 'Radios opposite 1',
      '#options' => [
        0 => 'zero',
        1 => 'one',
      ],
      '#default_value' => 0,
      0 => [
        '#states' => [
          'checked' => [
            ':input[name="radios_opposite2"]' => ['value' => 1],
          ],
        ],
      ],
      1 => [
        '#states' => [
          'checked' => [
            ':input[name="radios_opposite2"]' => ['value' => 0],
          ],
        ],
      ],
    ];
    $form['radios_opposite2'] = [
      '#type' => 'radios',
      '#title' => 'Radios opposite 2',
      '#options' => [
        0 => 'zero',
        1 => 'one',
      ],
      '#default_value' => 1,
      0 => [
        '#states' => [
          'checked' => [
            ':input[name="radios_opposite1"]' => ['value' => 1],
          ],
        ],
      ],
      1 => [
        '#states' => [
          'checked' => [
            ':input[name="radios_opposite1"]' => ['value' => 0],
          ],
        ],
      ],
    ];
    $form['radios_trigger'] = [
      '#type' => 'radios',
      '#title' => 'Radios trigger',
      '#options' => [
        'value1' => 'Value 1',
        'value2' => 'Value 2',
        'value3' => 'Value 3',
      ],
    ];
    $form['checkboxes_trigger'] = [
      '#type' => 'checkboxes',
      '#title' => 'Checkboxes trigger',
      '#options' => [
        'value1' => 'Value 1',
        'value2' => 'Value 2',
        'value3' => 'Value 3',
      ],
    ];
    $form['select_trigger'] = [
      '#type' => 'select',
      '#title' => 'Select trigger',
      '#options' => [
        'value1' => 'Value 1',
        'value2' => 'Value 2',
        'value3' => 'Value 3',
      ],
      '#empty_value' => '_none',
      '#empty_option' => '- None -',
    ];
    $form['number_trigger'] = [
      '#type' => 'number',
      '#title' => 'Number trigger',
    ];

    // Tested fields.
    // Checkbox trigger.
    $states = $this->getStatesBuilder();
    $form['textfield_invisible_when_checkbox_trigger_checked'] = [
      '#type' => 'textfield',
      '#title' => 'Textfield invisible when checkbox trigger checked',
      '#states' => $states->addStates(
        $states->state()->setInvisible(
          $states->watch(':input[name="checkbox_trigger"]')->isChecked()
        )
      )->toArray(),
    ];
    $states = $this->getStatesBuilder();
    $form['textfield_required_when_checkbox_trigger_checked'] = [
      '#type' => 'textfield',
      '#title' => 'Textfield required when checkbox trigger checked',
      '#states' => $states->addStates(
        $states->state()->setRequired(
          $states->watch(':input[name="checkbox_trigger"]')->isChecked()
        )
      )->toArray(),
    ];
    $states = $this->getStatesBuilder();
    $form['textfield_readonly_when_checkbox_trigger_checked'] = [
      '#type' => 'textfield',
      '#title' => 'Textfield readonly when checkbox trigger checked',
      '#states' => $states->addStates(
        $states->state()->setReadonly(
          $states->watch(':input[name="checkbox_trigger"]')->isChecked()
        )
      ),
    ];
    $states = $this->getStatesBuilder();
    $form['textarea_readonly_when_checkbox_trigger_checked'] = [
      '#type' => 'textarea',
      '#title' => 'Textarea readonly when checkbox trigger checked',
      '#states' => $states->addStates(
        $states->state()->setReadonly(
          $states->watch(':input[name="checkbox_trigger"]')->isChecked()
        )
      ),
    ];
    $states = $this->getStatesBuilder();
    $form['details_expanded_when_checkbox_trigger_checked'] = [
      '#type' => 'details',
      '#title' => 'Details expanded when checkbox trigger checked',
      '#states' => $states->addStates(
        $states->state()->setExpanded(
          $states->watch(':input[name="checkbox_trigger"]')->isChecked()
        )
      )->toArray(),
    ];
    $form['details_expanded_when_checkbox_trigger_checked']['textfield_in_details'] = [
      '#type' => 'textfield',
      '#title' => 'Textfield in details',
    ];
    $states = $this->getStatesBuilder();
    $form['checkbox_checked_when_checkbox_trigger_checked'] = [
      '#type' => 'checkbox',
      '#title' => 'Checkbox checked when checkbox trigger checked',
      '#states' => $states->addStates(
        $states->state()->setChecked(
          $states->watch(':input[name="checkbox_trigger"]')->isChecked()
        )
      )->toArray(),
    ];
    $states = $this->getStatesBuilder();
    $form['checkbox_unchecked_when_checkbox_trigger_checked'] = [
      '#type' => 'checkbox',
      '#title' => 'Checkbox unchecked when checkbox trigger checked',
      '#states' => $states->addStates(
        $states->state()->setUnchecked(
          $states->watch(':input[name="checkbox_trigger"]')->isChecked()
        )
      )->toArray(),
    ];
    $states = $this->getStatesBuilder();
    $form['checkbox_visible_when_checkbox_trigger_checked'] = [
      '#type' => 'checkbox',
      '#title' => 'Checkbox visible when checkbox trigger checked',
      '#states' => $states->addStates(
        $states->state()->setVisible(
          $states->watch(':input[name="checkbox_trigger"]')->isChecked()
        )
      )->toArray(),
    ];
    $states = $this->getStatesBuilder();
    $form['text_format_invisible_when_checkbox_trigger_checked'] = [
      '#type' => 'text_format',
      '#title' => 'Text format invisible when checkbox trigger checked',
      '#states' => $states->addStates(
        $states->state()->setInvisible(
          $states->watch(':input[name="checkbox_trigger"]')->isChecked()
        )
      )->toArray(),
    ];
    $states = $this->getStatesBuilder();
    $form['checkboxes_all_checked_when_checkbox_trigger_checked'] = [
      '#type' => 'checkboxes',
      '#title' => 'Checkboxes: all checked when checkbox trigger checked',
      '#options' => [
        'value1' => 'Value 1',
        'value2' => 'Value 2',
        'value3' => 'Value 3',
      ],
      '#states' => $states->addStates(
        $states->state()->setChecked(
          $states->watch(':input[name="checkbox_trigger"]')->isChecked()
        )
      ),
    ];
    $states = $this->getStatesBuilder();
    $form['checkboxes_some_checked_when_checkbox_trigger_checked'] = [
      '#type' => 'checkboxes',
      '#title' => 'Checkboxes: some checked when checkbox trigger checked',
      '#options' => [
        'value1' => 'Value 1',
        'value2' => 'Value 2',
        'value3' => 'Value 3',
      ],
      'value1' => [
        '#states' => $states->addStates(
          $states->state()->setChecked(
            $states->watch(':input[name="checkbox_trigger"]')->isChecked()
          )
        ),
      ],
      'value3' => [
        '#states' => $states->addStates(
          $states->state()->setChecked(
            $states->watch(':input[name="checkbox_trigger"]')->isChecked()
          )
        ),
      ],
    ];
    $states = $this->getStatesBuilder();
    $form['checkboxes_all_disabled_when_checkbox_trigger_checked'] = [
      '#type' => 'checkboxes',
      '#title' => 'Checkboxes: all disabled when checkbox trigger checked',
      '#options' => [
        'value1' => 'Value 1',
        'value2' => 'Value 2',
        'value3' => 'Value 3',
      ],
      '#states' => $states->addStates(
        $states->state()->setDisabled(
          $states->watch(':input[name="checkbox_trigger"]')->isChecked()
        )
      ),
    ];
    $states = $this->getStatesBuilder();
    $form['checkboxes_some_disabled_when_checkbox_trigger_checked'] = [
      '#type' => 'checkboxes',
      '#title' => 'Checkboxes: some disabled when checkbox trigger checked',
      '#options' => [
        'value1' => 'Value 1',
        'value2' => 'Value 2',
        'value3' => 'Value 3',
      ],
      'value1' => [
        '#states' => $states->addStates(
          $states->state()->setDisabled(
            $states->watch(':input[name="checkbox_trigger"]')->isChecked()
          )
        ),
      ],
      'value3' => [
        '#states' => $states->addStates(
          $states->state()->setDisabled(
            $states->watch(':input[name="checkbox_trigger"]')->isChecked()
          )
        ),
      ],
    ];
    $states = $this->getStatesBuilder();
    $form['radios_checked_when_checkbox_trigger_checked'] = [
      '#type' => 'radios',
      '#title' => 'Radios checked when checkbox trigger checked',
      '#options' => [
        'value1' => 'Value 1',
        'value2' => 'Value 2',
      ],
      'value1' => [
        '#states' => $states->addStates(
          $states->state()->setChecked(
            $states->watch(':input[name="checkbox_trigger"]')->isChecked()
          )
        ),
      ],
    ];
    $states = $this->getStatesBuilder();
    $form['radios_all_disabled_when_checkbox_trigger_checked'] = [
      '#type' => 'radios',
      '#title' => 'Radios: all disabled when checkbox trigger checked',
      '#options' => [
        'value1' => 'Value 1',
        'value2' => 'Value 2',
      ],
      '#states' => $states->addStates(
        $states->state()->setDisabled(
          $states->watch(':input[name="checkbox_trigger"]')->isChecked()
        )
      ),
    ];
    $states = $this->getStatesBuilder();
    $form['radios_some_disabled_when_checkbox_trigger_checked'] = [
      '#type' => 'radios',
      '#title' => 'Radios: some disabled when checkbox trigger checked',
      '#options' => [
        'value1' => 'Value 1',
        'value2' => 'Value 2',
      ],
      'value1' => [
        '#states' => $states->addStates(
          $states->state()->setDisabled(
            $states->watch(':input[name="checkbox_trigger"]')->isChecked()
          )
        ),
      ],
    ];

    // Checkboxes trigger.
    $states = $this->getStatesBuilder();
    $form['textfield_visible_when_checkboxes_trigger_value2_checked'] = [
      '#type' => 'textfield',
      '#title' => 'Textfield visible when checkboxes trigger value2 checked',
      '#states' => $states->addStates(
        $states->state()->setVisible(
          $states->watch(':input[name="checkboxes_trigger[value2]"]')->isChecked()
        )
      )->toArray(),
    ];
    $states = $this->getStatesBuilder();
    $form['textfield_visible_when_checkboxes_trigger_value3_checked'] = [
      '#type' => 'textfield',
      '#title' => 'Textfield visible when checkboxes trigger value3 checked',
      '#states' => $states->addStates(
        $states->state()->setVisible(
          $states->watch(':input[name="checkboxes_trigger[value3]"]')->isChecked()
        )
      )->toArray(),
    ];

    // Radios trigger.
    $states = $this->getStatesBuilder();
    $form['fieldset_visible_when_radios_trigger_has_value2'] = [
      '#type' => 'fieldset',
      '#title' => 'Fieldset visible when radio trigger has value2',
      '#states' => $states->addStates(
        $states->state()->setVisible(
          $states->watch(':input[name="radios_trigger"]')->valueEqualTo('value2')
        )
      )->toArray(),
    ];
    $form['fieldset_visible_when_radios_trigger_has_value2']['textfield_in_fieldset'] = [
      '#type' => 'textfield',
      '#title' => 'Textfield in fieldset',
    ];
    $states = $this->getStatesBuilder();
    $form['textfield_invisible_when_radios_trigger_has_value2'] = [
      '#type' => 'textfield',
      '#title' => 'Textfield invisible when radio trigger has value2',
      '#states' => $states->addStates(
        $states->state()->setInvisible(
          $states->watch(':input[name="radios_trigger"]')->valueEqualTo('value2')
        )
      )->toArray(),
    ];
    $states = $this->getStatesBuilder();
    $form['select_required_when_radios_trigger_has_value2'] = [
      '#type' => 'select',
      '#title' => 'Select required when radio trigger has value2',
      '#options' => [
        'value1' => 'Value 1',
        'value2' => 'Value 2',
        'value3' => 'Value 3',
      ],
      '#states' => $states->addStates(
        $states->state()->setRequired(
          $states->watch(':input[name="radios_trigger"]')->valueEqualTo('value2')
        )
      )->toArray(),
    ];
    $states = $this->getStatesBuilder();
    $form['checkbox_checked_when_radios_trigger_has_value3'] = [
      '#type' => 'checkbox',
      '#title' => 'Checkbox checked when radios trigger has value3',
      '#states' => $states->addStates(
        $states->state()->setChecked(
          $states->watch(':input[name="radios_trigger"]')->valueEqualTo('value3')
        )
      )->toArray(),
    ];
    $states = $this->getStatesBuilder();
    $form['checkbox_unchecked_when_radios_trigger_has_value3'] = [
      '#type' => 'checkbox',
      '#title' => 'Checkbox unchecked when radios trigger has value3',
      '#states' => $states->addStates(
        $states->state()->setUnchecked(
          $states->watch(':input[name="radios_trigger"]')->valueEqualTo('value3')
        )
      )->toArray(),
    ];
    $states = $this->getStatesBuilder();
    $form['details_expanded_when_radios_trigger_has_value3'] = [
      '#type' => 'details',
      '#title' => 'Details expanded when radio trigger has value3',
      '#states' => $states->addStates(
        $states->state()->setExpanded(
          $states->watch(':input[name="radios_trigger"]')->valueEqualTo('value3')
        )
      )->toArray(),
    ];
    $form['details_expanded_when_radios_trigger_has_value3']['textfield_in_details'] = [
      '#type' => 'textfield',
      '#title' => 'Textfield in details',
    ];

    // Select trigger
    $states = $this->getStatesBuilder();
    $form['item_visible_when_select_trigger_has_value2'] = [
      '#type' => 'item',
      '#title' => 'Item visible when select trigger has value2',
      '#states' => $states->addStates(
        $states->state()->setVisible(
          $states->watch(':input[name="select_trigger"]')->valueEqualTo('value2')
        )
      )->toArray(),
    ];
    $states = $this->getStatesBuilder();
    $form['textfield_visible_when_select_trigger_has_value3'] = [
      '#type' => 'textfield',
      '#title' => 'Textfield visible when select trigger has value3',
      '#states' => $states->addStates(
        $states->state()->setVisible(
          $states->watch(':input[name="select_trigger"]')->valueEqualTo('value3')
        )
      )->toArray(),
    ];
    $states = $this->getStatesBuilder();
    $form['textfield_visible_when_select_trigger_has_value2_or_value3'] = [
      '#type' => 'textfield',
      '#title' => 'Textfield visible when select trigger has value2 or value3',
      '#states' => $states->addStates(
        $states->state()->setVisible(
          $states->watch(':input[name="select_trigger"]')
            ->valueEqualTo('value2')
            ->valueEqualTo('value3')
        )
      )->toArray(),
    ];

    // Textfield trigger.
    $states = $this->getStatesBuilder();
    $form['checkbox_checked_when_textfield_trigger_filled'] = [
      '#type' => 'checkbox',
      '#title' => 'Checkbox checked when textfield trigger filled',
      '#default_value' => '0',
      '#states' => $states->addStates(
        $states->state()->setChecked(
          $states->watch(':input[name="textfield_trigger"]')->isFilled()
        )
      )->toArray(),
    ];
    $states = $this->getStatesBuilder();
    $form['checkbox_unchecked_when_textfield_trigger_filled'] = [
      '#type' => 'checkbox',
      '#title' => 'Checkbox unchecked when textfield trigger filled',
      '#default_value' => '1',
      '#states' => $states->addStates(
        $states->state()->setUnchecked(
          $states->watch(':input[name="textfield_trigger"]')->isFilled()
        )
      )->toArray(),
    ];
    $states = $this->getStatesBuilder();
    $form['select_invisible_when_textfield_trigger_filled'] = [
      '#type' => 'select',
      '#title' => 'Select invisible when textfield trigger filled',
      '#options' => [0 => 0, 1 => 1, 2 => 2],
      '#states' => $states->addStates(
        $states->state()->setInvisible(
          $states->watch(':input[name="textfield_trigger"]')->isFilled()
        )
      )->toArray(),
    ];
    $states = $this->getStatesBuilder();
    $form['select_visible_when_textfield_trigger_filled'] = [
      '#type' => 'select',
      '#title' => 'Select visible when textfield trigger filled',
      '#options' => [0 => 0, 1 => 1, 2 => 2],
      '#states' => $states->addStates(
        $states->state()->setVisible(
          $states->watch(':input[name="textfield_trigger"]')->isFilled()
        )
      )->toArray(),
    ];
    $states = $this->getStatesBuilder();
    $form['textfield_required_when_textfield_trigger_filled'] = [
      '#type' => 'textfield',
      '#title' => 'Textfield required  when textfield trigger filled',
      '#states' => $states->addStates(
        $states->state()->setRequired(
          $states->watch(':input[name="textfield_trigger"]')->isFilled()
        )
      )->toArray(),
    ];
    $states = $this->getStatesBuilder();
    $form['details_expanded_when_textfield_trigger_filled'] = [
      '#type' => 'details',
      '#title' => 'Details expanded when textfield trigger filled',
      '#states' => $states->addStates(
        $states->state()->setExpanded(
          $states->watch(':input[name="textfield_trigger"]')->isFilled()
        )
      )->toArray(),
    ];
    $form['details_expanded_when_textfield_trigger_filled']['textfield_in_details'] = [
      '#type' => 'textfield',
      '#title' => 'Textfield in details',
    ];

    // Multiple triggers.
    $states = $this->getStatesBuilder();
    $form['item_visible_when_select_trigger_has_value2_and_textfield_trigger_filled'] = [
      '#type' => 'item',
      '#title' => 'Item visible when select trigger has value2 and textfield trigger filled',
      '#states' => $states->addStates(
        $states->state()->setVisible(
          $states->watch(':input[name="select_trigger"]')->valueEqualTo('value2'),
          $states->watch(':input[name="textfield_trigger"]')->isFilled(),
        )
      )->toArray(),
    ];

    // Number triggers.
    $form['item_visible_when_number_trigger_filled_by_spinner'] = [
      '#type' => 'item',
      '#title' => 'Item visible when number trigger filled by spinner widget',
      '#states' => [
        'visible' => [
          ':input[name="number_trigger"]' => ['filled' => TRUE],
        ],
      ],
    ];

    $form['select'] = [
      '#type' => 'select',
      '#title' => 'select 1',
      '#options' => [0 => 0, 1 => 1, 2 => 2],
    ];
    $form['number'] = [
      '#type' => 'number',
      '#title' => 'enter 1',
    ];
    $states = $this->getStatesBuilder();
    $form['textfield'] = [
      '#type' => 'textfield',
      '#title' => 'textfield',
      '#states' => $states->addStates(
        $states->state()->setVisible(
          $states->or(
            $states->watch(':input[name="select"]')->valueEqualTo('1')
          ),
          $states->or(
            $states->watch(':input[name="number"]')->valueEqualTo('1')
          )
        )
      )->toArray(),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
