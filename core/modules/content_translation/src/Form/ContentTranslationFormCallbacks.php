<?php

namespace Drupal\content_translation\Form;

use Drupal\content_translation\BundleTranslationSettingsInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Security\Attribute\TrustedCallback;

/**
 * Provides trusted callbacks for content_translation.
 */
class ContentTranslationFormCallbacks {

  /**
   * Implements #process callback for content_translation_element_info_alter()
   *
   * Expands the language_configuration form element.
   */
  #[TrustedCallback]
  public static function languageConfigurationElementProcess(array &$element, FormStateInterface $form_state, array &$form) {
    if (empty($element['#content_translation_skip_alter']) && \Drupal::currentUser()->hasPermission('administer content translation')) {
      $key = $element['#name'];
      $form_state->set(['content_translation', 'key'], $key);
      $context = $form_state->get(['language', $key]);

      $element['content_translation'] = [
        '#type' => 'checkbox',
        '#title' => t('Enable translation'),
        // For new bundle, we don't know the bundle name yet,
        // default to no translatability.
        '#default_value' => $context['bundle'] ? \Drupal::service('content_translation.manager')->isEnabled($context['entity_type'], $context['bundle']) : FALSE,
        '#element_validate' => [[static::class, 'languageConfigurationElementValidate']],
      ];

      $submit_name = isset($form['actions']['save_continue']) ? 'save_continue' : 'submit';
      // Only add the submit handler on the submit button if the #submit
      // property is already available, otherwise this breaks
      // the form submit function.
      if (isset($form['actions'][$submit_name]['#submit'])) {
        $form['actions'][$submit_name]['#submit'][] = [static::class , 'languageConfigurationElementSubmit'];
      }
      else {
        $form['#submit'][] = [static::class , 'languageConfigurationElementSubmit'];
      }
    }
    return $element;
  }

  /**
   * Form submission handler for element added with TrustedFormCallbacks::languageConfigurationElementProcess().
   *
   * Stores the content translation settings.
   *
   * @see ::languageConfigurationElementValidate()
   */
  public static function languageConfigurationElementSubmit(array $form, FormStateInterface $form_state) {
    $key = $form_state->get(['content_translation', 'key']);
    $context = $form_state->get(['language', $key]);
    $enabled = $form_state->getValue([$key, 'content_translation']);

    if (\Drupal::service('content_translation.manager')->isEnabled($context['entity_type'], $context['bundle']) != $enabled) {
      \Drupal::service('content_translation.manager')->setEnabled($context['entity_type'], $context['bundle'], $enabled);
      \Drupal::service('router.builder')->setRebuildNeeded();
    }
  }

  /**
   * Implements #element_validate callback.
   *
   * Checks whether translation can be enabled: if language is set to one of the
   * special languages and language selector is not hidden, translation cannot
   * be enabled.
   *
   * @see static::languageConfigurationElementProcess()
   * @see static::languageConfigurationElementSubmit()
   */
  #[TrustedCallback]
  public static function languageConfigurationElementValidate(array $element, FormStateInterface $form_state, array $form) {
    $key = $form_state->get(['content_translation', 'key']);
    $values = $form_state->getValue($key);
    if (!$values['language_alterable'] && $values['content_translation'] && \Drupal::languageManager()->isLanguageLocked($values['langcode'])) {
      $locked_languages = [];
      foreach (\Drupal::languageManager()->getLanguages(LanguageInterface::STATE_LOCKED) as $language) {
        $locked_languages[$language->getId()] = $language->getName();
      }
      // @todo Set the correct form element name as soon as the element parents
      //   are correctly set. We should be using NestedArray::getValue() but for
      //   now we cannot.
      $form_state->setErrorByName('', t('"Show language selector" is not compatible with translating content that has default language: %choice. Either do not hide the language selector or pick a specific language.', ['%choice' => $locked_languages[$values['langcode']]]));
    }
  }

  /**
   * Implements #validate handler for content_translation_admin_settings_form().
   *
   * @see static::formLanguageContentSettingsSubmit()
   */
  #[TrustedCallback]
  public static function formLanguageContentSettingsValidate(array $form, FormStateInterface $form_state) {
    $settings = &$form_state->getValue('settings');
    foreach ($settings as $entity_type => $entity_settings) {
      foreach ($entity_settings as $bundle => $bundle_settings) {
        if (!empty($bundle_settings['translatable'])) {
          $name = "settings][$entity_type][$bundle][translatable";

          $translatable_fields = isset($settings[$entity_type][$bundle]['fields']) ? array_filter($settings[$entity_type][$bundle]['fields']) : FALSE;
          if (empty($translatable_fields)) {
            $t_args = ['%bundle' => $form['settings'][$entity_type][$bundle]['settings']['#label']];
            $form_state->setErrorByName($name, t('At least one field needs to be translatable to enable %bundle for translation.', $t_args));
          }

          $values = $bundle_settings['settings']['language'];
          if (empty($values['language_alterable']) && \Drupal::languageManager()->isLanguageLocked($values['langcode'])) {
            $locked_languages = [];
            foreach (\Drupal::languageManager()->getLanguages(LanguageInterface::STATE_LOCKED) as $language) {
              $locked_languages[] = $language->getName();
            }
            $form_state->setErrorByName($name, t('Translation is not supported if language is always one of: @locked_languages', ['@locked_languages' => implode(', ', $locked_languages)]));
          }
        }
      }
    }
  }

  /**
   * Form submission handler for content_translation_admin_settings_form().
   *
   * @see static::formLanguageContentSettingsValidate()
   */
  public static function formLanguageContentSettingsSubmit(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\content_translation\ContentTranslationManagerInterface $content_translation_manager */
    $content_translation_manager = \Drupal::service('content_translation.manager');
    $entity_types = $form_state->getValue('entity_types');
    $settings = &$form_state->getValue('settings');

    // If an entity type is not translatable all its bundles and fields must be
    // marked as non-translatable. Similarly, if a bundle is made non-translatable
    // all of its fields will be not translatable.
    foreach ($settings as $entity_type_id => &$entity_settings) {
      foreach ($entity_settings as $bundle => &$bundle_settings) {
        $fields = \Drupal::service('entity_field.manager')->getFieldDefinitions($entity_type_id, $bundle);
        if (!empty($bundle_settings['translatable'])) {
          $bundle_settings['translatable'] = $bundle_settings['translatable'] && $entity_types[$entity_type_id];
        }
        if (!empty($bundle_settings['fields'])) {
          foreach ($bundle_settings['fields'] as $field_name => $translatable) {
            $translatable = $translatable && $bundle_settings['translatable'];
            // If we have column settings and no column is translatable, no point
            // in making the field translatable.
            if (isset($bundle_settings['columns'][$field_name]) && !array_filter($bundle_settings['columns'][$field_name])) {
              $translatable = FALSE;
            }
            $field_config = $fields[$field_name]->getConfig($bundle);
            if ($field_config->isTranslatable() != $translatable) {
              $field_config
                ->setTranslatable($translatable)
                ->save();
            }
          }
        }
        if (isset($bundle_settings['translatable'])) {
          // Store whether a bundle has translation enabled or not.
          $content_translation_manager->setEnabled($entity_type_id, $bundle, $bundle_settings['translatable']);

          // Store any other bundle settings.
          if ($content_translation_manager instanceof BundleTranslationSettingsInterface) {
            $content_translation_manager->setBundleTranslationSettings($entity_type_id, $bundle, $bundle_settings['settings']['content_translation']);
          }

          // Save translation_sync settings.
          if (!empty($bundle_settings['columns'])) {
            foreach ($bundle_settings['columns'] as $field_name => $column_settings) {
              $field_config = $fields[$field_name]->getConfig($bundle);
              if ($field_config->isTranslatable()) {
                $field_config->setThirdPartySetting('content_translation', 'translation_sync', $column_settings);
              }
              // If the field does not have translatable enabled we need to reset
              // the sync settings to their defaults.
              else {
                $field_config->unsetThirdPartySetting('content_translation', 'translation_sync');
              }
              $field_config->save();
            }
          }
        }
      }
    }

    // Ensure menu router information is correctly rebuilt.
    \Drupal::service('router.builder')->setRebuildNeeded();
  }

}
