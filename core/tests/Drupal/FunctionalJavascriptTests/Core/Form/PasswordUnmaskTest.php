<?php

namespace Drupal\FunctionalJavascriptTests\Core\Form;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests the state of elements based on another elements.
 *
 * @group javascript
 */
class PasswordUnmaskTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['form_test'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Test the password_unmask form element functionality.
   */
  public function testPasswordRevealFormElement() {
    $this->drupalGet('/form-test/password-reveal');
    $page = $this->getSession()->getPage();

    $toggle_password_one_link = $page->find('css', '.toggle-password');
    $password_one_field = $page->findById('edit-password-one');
    $password_two_field = $page->findById('edit-password-two');

    /* Initial state: before the show password link is clicked. */

    // Test that both password fields type is password.
    $this->assertEquals($password_one_field->getAttribute('type'), 'password');
    $this->assertEquals($password_two_field->getAttribute('type'), 'password');

    // Change state: click the "show password" of password one link.
    $toggle_password_one_link->click();

    // Test that the password one field type is now text.
    $this->assertEquals($password_one_field->getAttribute('type'), 'text');
    // Test that the password two field type is still password.
    $this->assertEquals($password_two_field->getAttribute('type'), 'password');

    // Change state: click the "hide password" link.
    $toggle_password_one_link->click();

    // Test that the password field type is password.
    $this->assertEquals($password_one_field->getAttribute('type'), 'password');
    // Test that the password two field type is still password.
    $this->assertEquals($password_two_field->getAttribute('type'), 'password');

  }

}
