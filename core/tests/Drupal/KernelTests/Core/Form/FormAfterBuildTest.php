<?php

namespace Drupal\KernelTests\Core\Form;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests forms with an #after_build callback.
 *
 * @group Form
 */
class FormAfterBuildTest extends KernelTestBase implements FormInterface {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'form_test',
  ];

  /**
   * Catches warnings and notices.
   *
   * @var string[]
   */
  private static $caughtErrors = [];

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_after_build_test';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['text'] = [
      '#type' => 'textfield',
      '#title' => 'Textfield',
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * Tests a form with an #after_build that adds an element with no '#parents'.
   */
  public function testUndefinedParentsInAfterBuild() {
    $form_state = new FormState();
    $form_builder = $this->container->get('form_builder');
    $error_message = NULL;
    try {
      $form_builder->submitForm($this, $form_state);
    }
    catch (\Throwable $e) {
      $error_message = $e->getMessage();
    }

    $this->assertTrue(empty($error_message), "The following error occurred during the form submission: {$error_message}");
  }

}
