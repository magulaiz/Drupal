<?php

namespace Drupal\Tests\user\Kernel;

use Drupal\Core\Form\FormState;
use Drupal\KernelTests\KernelTestBase;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;

/**
 * Verifies the field order in user account forms.
 *
 * @group user
 */
class UserAccountFormFieldsTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['system', 'user', 'field'];

  /**
   * @var \Drupal\user\UserInterface
   */
  protected UserInterface $user;

  /**
   * Tests the root user account form section in the "Configure site" form.
   */
  public function testInstallConfigureForm() {
    require_once $this->root . '/core/includes/install.core.inc';
    require_once $this->root . '/core/includes/install.inc';
    $install_state = install_state_defaults();
    $form_state = new FormState();
    $form_state->addBuildInfo('args', [&$install_state]);
    $form = $this->container->get('form_builder')
      ->buildForm('Drupal\Core\Installer\Form\SiteConfigureForm', $form_state);

    // Verify that web browsers may autocomplete the email value and
    // autofill/prefill the name and pass values.
    foreach (['mail', 'name', 'pass'] as $key) {
      $this->assertFalse(isset($form['account'][$key]['#attributes']['autocomplete']), "'$key' field: 'autocomplete' attribute not found.");
    }
  }

  /**
   * Tests the user registration form.
   */
  public function testUserRegistrationForm() {
    // Install default configuration; required for AccountFormController.
    $this->installConfig(['user']);

    // Disable email confirmation to unlock the password field.
    $this->config('user.settings')
      ->set('verify_mail', FALSE)
      ->save();

    $form = $this->buildAccountForm('register');

    // Verify that web browsers may autocomplete the email value and
    // autofill/prefill the name and pass values.
    foreach (['mail', 'name', 'pass'] as $key) {
      $this->assertFalse(isset($form['account'][$key]['#attributes']['autocomplete']), "'$key' field: 'autocomplete' attribute not found.");
    }
  }

  /**
   * Tests the user edit form.
   */
  public function testUserEditForm() {
    // Install default configuration; required for AccountFormController.
    $this->installConfig(['user']);
    $this->installEntitySchema('user');

    $this->user = User::create(['name' => 'test']);
    $this->user->save();

    $form = $this->buildAccountForm('default');

    // Verify that autocomplete is off on all account fields.
    foreach (['name'] as $key) {
      $this->assertSame('off', $form['account'][$key]['#attributes']['autocomplete'], "'{$key}' field: 'autocomplete' attribute is 'off'.");
    }
  }

  /**
   * Builds the user account form for a given operation.
   *
   * @param string $operation
   *   The entity operation; one of 'register' or 'default'.
   *
   * @return array
   *   The form array.
   */
  protected function buildAccountForm($operation) {
    // @see HtmlEntityFormController::getFormObject()
    $entity_type = 'user';
    if ($operation != 'register') {
      // Use an existing user.
      $entity = $this->user;
    }
    else {
      $entity = $this->container->get('entity_type.manager')
        ->getStorage($entity_type)
        ->create();
    }

    // @see EntityFormBuilder::getForm()
    return $this->container->get('entity.form_builder')->getForm($entity, $operation);
  }

}
