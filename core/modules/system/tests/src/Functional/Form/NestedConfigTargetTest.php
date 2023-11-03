<?php

namespace Drupal\Tests\system\Functional\Form;

use Drupal\Tests\BrowserTestBase;

class NestedConfigTargetTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['form_test'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  public function test(): void {
    $this->drupalGet('/form-test/nested-config-target');
    $page = $this->getSession()->getPage();
    $page->fillField('First choice', '');
    $page->pressButton('Save configuration');
    $assert_session = $this->assertSession();
    $assert_session->statusMessageContains('This value should not be blank.', 'error');
    // The parent element should be marked as invalid, along with its children.
    $assert_session->elementAttributeExists('css', '#edit-favorites', 'aria-invalid');
    $assert_session->elementAttributeExists('named', ['field', 'First choice'], 'aria-invalid');
    // The second choice is fine, so it should not be marked invalid.
    $assert_session->elementAttributeNotExists('named', ['field', 'Second choice'], 'aria-invalid');
  }

}
