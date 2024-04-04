<?php

namespace Drupal\page_cache_form_test\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\Attribute\TrustedCallback;

/**
 * Implements trusted callbacks for page_cache tests.
 *
 * @package Drupal\page_cache_form_test\Form
 */
class TestFormTrustedCallbacks {

  /**
   * Implements #process callback.
   *
   * Callback implemented for
   * page_cache_form_test_form_page_cache_form_test_alter().
   */
  #[TrustedCallback]
  public static function pageCacheProcess(array &$element, FormStateInterface $form_state, array &$form) {
    if (isset($form_state->getBuildInfo()['immutable']) && $form_state->getBuildInfo()['immutable']) {
      $element['#suffix'] = 'Immutable: TRUE';
    }
    else {
      $element['#suffix'] = 'Immutable: FALSE';
    }
    return $element;
  }

}
