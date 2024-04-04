<?php

namespace Drupal\field_ui\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\Attribute\TrustedCallback;
use Drupal\field_ui\FieldUI;

/**
 * Provides trusted callbacks for field_ui module.
 */
class FieldUiFormCallbacks {

  /**
   * Form submission handler for the 'Save and manage fields' button.
   *
   * @see field_ui_form_alter()
   */
  #[TrustedCallback]
  public static function formManageFieldFormSubmit(array $form, FormStateInterface $form_state) {
    $provider = $form_state->getFormObject()->getEntity()->getEntityType()->getProvider();
    $id = $form_state->getFormObject()->getEntity()->id();
    if ($form_state->getTriggeringElement()['#parents'][0] === 'save_continue' && $route_info = FieldUI::getOverviewRouteInfo($provider, $id)) {
      $form_state->setRedirectUrl($route_info);
    }
  }

}
