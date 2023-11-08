<?php

namespace Drupal\Tests\config\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests the config export form.
 *
 * @group config
 */
class ConfigExportTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['config', 'system', 'block'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests Ajax form functionality on the config export page.
   */
  public function testAjaxOnExportPage() {
    $this->drupalLogin($this->drupalCreateUser([
      'export configuration',
    ]));

    $page = $this->getSession()->getPage();

    // Check that the export is empty on load.
    $this->drupalGet('admin/config/development/configuration/single/export');
    $this->assertTrue($this->assertSession()->optionExists('edit-config-name', '- Select -')->isSelected());
    $this->assertSession()->fieldValueEquals('export', '');

    // Check that the export is filled when selecting a config name.
    $page->selectFieldOption('config_name', 'system.site');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->fieldValueNotEquals('export', '');

    // Check that the export is empty when selecting "- Select -" option in
    // the config name.
    $page->selectFieldOption('config_name', '- Select -');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->fieldValueEquals('export', '');

    // Check that the export is emptied again when selecting a config type.
    $page->selectFieldOption('config_type', 'Action');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->fieldValueEquals('export', '');

    // Check that the 'Configuration name' list is sorted alphabetically by ID, not label.
    // Options 1 and 4 include the randomly generated username, so use Contains instead of Equals.
    $page->selectFieldOption('config_type', 'Action');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $options = $page->findField('config_name')->findAll('css', 'option');
    $this->assertStringContainsString('user_add_role_action', $options[1]->getValue());
    $this->assertEquals('user_block_user_action', $options[2]->getValue());
    $this->assertEquals('user_cancel_user_action', $options[3]->getValue());
    $this->assertStringContainsString('user_remove_role_action', $options[4]->getValue());
    $this->assertEquals('user_unblock_user_action', $options[5]->getValue());
  }

}
