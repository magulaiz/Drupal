<?php

namespace Drupal\navigation_test\Plugin\NavigationBlock;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\navigation\Attribute\NavigationBlock;
use Drupal\navigation\NavigationBlockPluginBase;

/**
 * Provides a test settings validation navigation block.
 */
#[NavigationBlock(
  id: "test_settings_validation",
  admin_label: new TranslatableMarkup("Test settings validation block"),
)]
class TestSettingsValidationBlock extends NavigationBlockPluginBase {

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    return ['digits' => ['#type' => 'textfield']] + $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    if (!ctype_digit($form_state->getValue('digits'))) {
      $form_state->setErrorByName('digits', $this->t('Only digits are allowed'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return ['#markup' => 'foo'];
  }

}
