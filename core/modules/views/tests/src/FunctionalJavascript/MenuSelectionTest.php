<?php

namespace Drupal\Tests\views\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests the menu selection.
 *
 * @group mymodule
 */
class MenuSelectionTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['views_ui', 'menu_ui'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'starterkit_theme';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalLogin($this->rootUser);
  }

  /**
   * Tests the behavior of parent link selection list based on selected menu.
   */
  public function testSelectLists() {

    // Visit the edit form for the test menu link.
    $this->drupalGet('admin/structure/menu/manage/account');
    $this->clickLink('Edit');
    // Select the tools option from the menu list.
    $this->getSession()->getPage()->findField('menu_parent_menu')->selectOption('<Tools>');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->optionExists('Parent link', '-- Compose tips (disabled)');
    $this->assertSession()->optionExists('Parent link', '<Tools>');

  }

}
