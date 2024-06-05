<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Element;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests maxlength attribute of confirm_password.
 *
 * @group Form
 */
class ConfirmPasswordTest extends KernelTestBase implements FormInterface {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['system', 'user'];

  /**
   * Tests confirm_password maxlength.
   */
  public function testConfirmPasswordMaxLength(): void {
    // Valid form state.
    $password = $this->randomMachineName(10);
    $form_state = (new FormState())
      ->setValues([
        // Form state values are structured like #parents.
        'pass' => ['pass1' => $password, 'pass2' => $password],
      ]);
    $form_builder = $this->container->get('form_builder');
    $form_builder->submitForm($this, $form_state);
    $this->assertCount(0, $form_state->getErrors());

    // Invalid form state.
    $password = $this->randomMachineName(20);
    $form_state = (new FormState())
      ->setValues([
        'pass' => ['pass1' => $password, 'pass2' => $password],
      ]);
    $form_builder = $this->container->get('form_builder');
    $form_builder->submitForm($this, $form_state);
    $errors = $form_state->getErrors();
    $this->assertCount(1, $errors);
    $this->assertEquals([
      'pass' => 'Enter password cannot be longer than 15 characters but is currently 20 characters long.',
    ], $errors);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'form_test_confirm_password_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['pass'] = [
      '#type' => 'password_confirm',
      '#title' => 'Enter password',
      '#maxlength' => 15,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Submit',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {

  }

}
