<?php

namespace Drupal\field_third_party_test\Hook;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FormatterInterface;
use Drupal\Core\Field\WidgetInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
class FieldThirdPartyTestHooks
{
    /**
     * Implements hook_field_widget_third_party_settings_form().
     */
    #[Hook('field_widget_third_party_settings_form')]
    public function fieldWidgetThirdPartySettingsForm(\Drupal\Core\Field\WidgetInterface $plugin, \Drupal\Core\Field\FieldDefinitionInterface $field_definition, $form_mode, $form, \Drupal\Core\Form\FormStateInterface $form_state)
    {
        $element['field_test_widget_third_party_settings_form'] = ['#type' => 'textfield', '#title' => \t('3rd party widget settings form'), '#default_value' => $plugin->getThirdPartySetting('field_third_party_test', 'field_test_widget_third_party_settings_form')];
        return $element;
    }
    /**
     * Implements hook_field_widget_settings_summary_alter().
     */
    #[Hook('field_widget_settings_summary_alter')]
    public function fieldWidgetSettingsSummaryAlter(&$summary, $context)
    {
        $summary[] = 'field_test_field_widget_settings_summary_alter';
        return $summary;
    }
    /**
     * Implements hook_field_formatter_third_party_settings_form().
     */
    #[Hook('field_formatter_third_party_settings_form')]
    public function fieldFormatterThirdPartySettingsForm(\Drupal\Core\Field\FormatterInterface $plugin, \Drupal\Core\Field\FieldDefinitionInterface $field_definition, $view_mode, $form, \Drupal\Core\Form\FormStateInterface $form_state)
    {
        $element['field_test_field_formatter_third_party_settings_form'] = ['#type' => 'textfield', '#title' => \t('3rd party formatter settings form'), '#default_value' => $plugin->getThirdPartySetting('field_third_party_test', 'field_test_field_formatter_third_party_settings_form')];
        return $element;
    }
    /**
     * Implements hook_field_formatter_settings_summary_alter().
     */
    #[Hook('field_formatter_settings_summary_alter')]
    public function fieldFormatterSettingsSummaryAlter(&$summary, $context)
    {
        $summary[] = 'field_test_field_formatter_settings_summary_alter';
        return $summary;
    }
}
