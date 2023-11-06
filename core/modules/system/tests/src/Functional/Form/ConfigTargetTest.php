<?php

namespace Drupal\Tests\system\Functional\Form;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests forms using #config_target.
 *
 * @group Form
 */
class ConfigTargetTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['form_test'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests #config_target where #tree is set to TRUE.
   */
  public function testTree(): void {
    $this->drupalGet('/form-test/tree-config-target');
    $page = $this->getSession()->getPage();
    $page->fillField('Favorite', '');
    $page->pressButton('Save configuration');
    $assert_session = $this->assertSession();
    $assert_session->statusMessageContains('This value should not be blank.', 'error');
    $assert_session->elementAttributeExists('named', ['field', 'Favorite'], 'aria-invalid');
    $assert_session->elementAttributeNotExists('named', ['field', 'Nemesis'], 'aria-invalid');
  }

  /**
   * Tests #config_target with an incorrect key.
   */
  public function testIncorrectKey(): void {
    $this->drupalGet('/form-test/incorrect-config-target');
    $page = $this->getSession()->getPage();
    $page->pressButton('Save configuration');
    $assert_session = $this->assertSession();
    $assert_session->statusMessageContains('\'does_not_exist\' is not a supported key.', 'error');
    $assert_session->elementAttributeExists('named', ['field', 'Missing key'], 'aria-invalid');
  }

  /**
   * Tests #config_target where there is not a 1:1 property to element.
   */
  public function testNested(): void {
    $this->drupalGet('/form-test/nested-config-target');
    $page = $this->getSession()->getPage();
    $page->fillField('First choice', '');
    $page->pressButton('Save configuration');
    $assert_session = $this->assertSession();
    $assert_session->statusMessageContains('This value should not be blank.', 'error');
    $assert_session->elementAttributeNotExists('css', '#edit-favorites', 'aria-invalid');
    $assert_session->elementAttributeExists('named', ['field', 'First choice'], 'aria-invalid');
    $assert_session->elementAttributeNotExists('named', ['field', 'Second choice'], 'aria-invalid');
  }

}
