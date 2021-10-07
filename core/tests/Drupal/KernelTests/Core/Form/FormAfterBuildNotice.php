<?php

namespace Drupal\KernelTests\Core\Form;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests form after build doesn't add #parents.
 *
 * @group Form
 */
class FormAfterBuildNotice extends KernelTestBase implements FormInterface {

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
   * Triggers a notice/error on #after_build when new elements are added.
   */
  public function testUndefinedParentsInAfterBuild() {
    set_error_handler(self::class . '::errorHandler');
    $form_state = new FormState();
    $form_builder = $this->container->get('form_builder');
    $form_builder->submitForm($this, $form_state);
    $this->assertEmpty(self::$caughtErrors);
  }

  /**
   * Error handler to catch notices and warnings during test.
   */
  public static function errorHandler($number, $error, $file, $line) {
    self::$caughtErrors[] = $error;
  }

}
