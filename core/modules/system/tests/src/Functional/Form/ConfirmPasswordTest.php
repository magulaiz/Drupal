<?php

declare(strict_types=1);

namespace Drupal\Tests\system\Functional\Form;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\RandomGeneratorTrait;

/**
 * Tests maxlength attribute of confirm_password.
 */
class ConfirmPasswordTest extends BrowserTestBase {

  use RandomGeneratorTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['form_test'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests confirm_password maxlength.
   */
  public function testConfirmPasswordMaxLength(): void {
    $this->drupalGet('/form-test/confirm-password');
    $password = $this->randomMachineName(20);
    $edit = [
      'pass[pass1]' => $password,
      'pass[pass2]' => $password,
    ];
    $this->submitForm($edit, 'Submit');
    $this->assertSession()->pageTextContains('Password cannot be longer than 15 characters but is currently 20 characters long.');
  }

}
