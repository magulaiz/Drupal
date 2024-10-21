<?php

namespace Drupal\field_ui_test_deprecated\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\field\FieldStorageConfigInterface;
use Drupal\field_ui\Form\FieldStorageConfigEditForm;
use Drupal\Core\Hook\Attribute\Hook;
class FieldUiTestDeprecatedHooks
{
    /**
     * Implements hook_form_FORM_ID_alter() for field_storage_config_edit_form.
     */
    #[Hook('form_field_storage_config_edit_form_alter')]
    public function formFieldStorageConfigEditFormAlter(&$form, \Drupal\Core\Form\FormStateInterface $form_state)
    {
        if (!$form_state->getFormObject() instanceof \Drupal\field_ui\Form\FieldStorageConfigEditForm) {
            throw new \LogicException('field_storage_config_edit_form() expects to get access to the field storage config entity edit form.');
        }
        if (!$form_state->getFormObject()->getEntity() instanceof \Drupal\field\FieldStorageConfigInterface) {
            throw new \LogicException('field_storage_config_edit_form() expects to get access to the field storage config entity.');
        }
        if (!isset($form['cardinality_container']['cardinality'])) {
            throw new \LogicException('field_storage_config_edit_form() expects to that the cardinality container with the cardinality form element exists.');
        }
        $form['cardinality_container']['hello'] = ['#markup' => 'Greetings from the field_storage_config_edit_form() alter.'];
    }
}
