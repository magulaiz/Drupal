<?php

namespace Drupal\language\Form;

use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\Attribute\TrustedCallback;
use Drupal\language\Entity\ContentLanguageSettings;

class LanguageFormCallbacks {

  /**
   * Implements #process callback for language_element_info_alter()
   *
   * Expands the language_configuration form element.
   */
  #[TrustedCallback]
  public static function LanguageSelectProcess(array &$element) {
    // Don't set the options if another module (translation for example) already
    // set the options.
    if (!isset($element['#options'])) {
      $element['#options'] = [];
      foreach (\Drupal::languageManager()->getLanguages($element['#languages']) as $langcode => $language) {
        $element['#options'][$langcode] = $language->isLocked() ? t('- @name -', ['@name' => $language->getName()]) : $language->getName();
      }
    }
    return $element;
  }

  /**
   * Submit handler for the forms that have a language_configuration element.
   */
  #[TrustedCallback]
  public static function configurationElementSubmit(array &$form, FormStateInterface $form_state) {
    // Iterate through all the language_configuration elements and save their
    // values.
    // In case we are editing a bundle, we must check the new bundle name,
    // because e.g. hook_ENTITY_update fired before.
    if ($language = $form_state->get('language')) {
      foreach ($language as $element_name => $values) {
        $entity_type_id = $values['entity_type'];
        $bundle = $values['bundle'];
        $form_object = $form_state->getFormObject();
        if ($form_object instanceof EntityFormInterface) {
          /** @var \Drupal\Core\Entity\EntityFormInterface $form_object */
          $entity = $form_object->getEntity();
          if ($entity->getEntityType()->getBundleOf()) {
            $bundle = $entity->id();
            $language[$element_name]['bundle'] = $bundle;
          }
        }
        $config = ContentLanguageSettings::loadByEntityTypeBundle($entity_type_id, $bundle);
        $config->setDefaultLangcode($form_state->getValue([
          $element_name,
          'langcode',
        ]));
        $config->setLanguageAlterable($form_state->getValue([
          $element_name,
          'language_alterable',
        ]));
        $config->save();

        // Set the form_state language with the updated bundle.
        $form_state->set('language', $language);
      }
    }
  }

}
