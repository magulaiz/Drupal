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
    // @todo This is wrong behavior - only the invalid element should be
    //   marked invalid. But at least now we have the beginnings of a test.
    $assert_session->elementAttributeExists('css', '#edit-favorites', 'aria-invalid');
    $assert_session->elementAttributeExists('named', ['field', 'First choice'], 'aria-invalid');
    $assert_session->elementAttributeExists('named', ['field', 'Second choice'], 'aria-invalid');
  }

}
