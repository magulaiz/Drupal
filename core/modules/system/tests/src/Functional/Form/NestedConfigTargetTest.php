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
    $page->fillField('Favorite', '');
    $page->pressButton('Save configuration');
    $assert_session = $this->assertSession();
    $assert_session->statusMessageContains('This value should not be blank.', 'error');
    $assert_session->elementAttributeExists('named', ['field', 'Favorite'], 'aria-invalid');
    $assert_session->elementAttributeNotExists('named', ['field', 'Nemesis'], 'aria-invalid');
  }

}
