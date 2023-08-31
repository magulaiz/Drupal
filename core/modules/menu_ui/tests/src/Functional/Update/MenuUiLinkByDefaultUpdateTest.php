<?php

namespace Drupal\Tests\menu_ui\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests that link_by_default is added in third_party.menu_ui settings.
 *
 * @group Update
 * @group legacy
 * @covers \menu_ui_post_update_add_link_by_default
 */
class MenuUiLinkByDefaultUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['menu_ui', 'node', 'language'];

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      DRUPAL_ROOT . '/core/modules/system/tests/fixtures/update/drupal-9.4.0.filled.standard.php.gz',
      __DIR__ . '/../../../fixtures/update/page_third_party_menu_ui.php',
    ];
  }

  /**
   * Tests that link_by_default is added in node.type.page.third_party.menu_ui.
   *
   * @see menu_ui_post_update_add_link_by_default()
   */
  public function testLinkByDefaultAddedAfterUpdate() {
    $page_third_party_menu_ui_settings = $this->config('node.type.page')->get('third_party_settings.menu_ui');
    $this->assertArrayNotHasKey('link_by_default', $page_third_party_menu_ui_settings);

    $this->runUpdates();

    $page_third_party_menu_ui_settings = $this->config('node.type.page')->get('third_party_settings.menu_ui');
    $this->assertNotNull($page_third_party_menu_ui_settings['link_by_default']);
    $this->assertFalse($page_third_party_menu_ui_settings['link_by_default']);
  }

}
