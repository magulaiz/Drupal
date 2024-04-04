<?php

namespace Drupal\content_translation_test\Entity\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\Attribute\TrustedCallback;

class ContentTranslationTestFormCallbacks {

  /**
   * Form submission handler for custom field added based on a request parameter.
   *
   * @see content_translation_test_form_node_article_form_alter()
   */
  #[TrustedCallback]
  public static function testFormNodeFormSubmit($form, FormStateInterface $form_state) {
    \Drupal::state()->set('test_field_only_en_fr', $form_state->getValue('test_field_only_en_fr'));
  }

}
