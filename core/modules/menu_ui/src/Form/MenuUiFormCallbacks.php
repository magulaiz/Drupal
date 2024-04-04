<?php

namespace Drupal\menu_ui\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\Attribute\TrustedCallback;

/**
 * Implements trusted callbacks for menu_ui module.
 */
class MenuUiFormCallbacks {

  /**
   * Implements #validate callback for menu_ui_form_node_type_form_alter().
   */
  #[TrustedCallback]
  public static function nodeTypeFormValidate(array &$form, FormStateInterface $form_state):void {
    $available_menus = array_filter($form_state->getValue('menu_options'));
    // If there is at least one menu allowed, the selected item should be in
    // one of them.
    if (count($available_menus)) {
      $menu_item_id_parts = explode(':', $form_state->getValue('menu_parent'));
      if (!in_array($menu_item_id_parts[0], $available_menus)) {
        $form_state->setErrorByName('menu_parent', t('The selected menu link is not under one of the selected menus.'));
      }
    }
    else {
      $form_state->setValue('menu_parent', '');
    }
  }

}
