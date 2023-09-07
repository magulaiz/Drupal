<?php

namespace Drupal\Tests\block\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests the update path for block title.
 *
 * @group Update
 * @group legacy
 */
class BlockTitleUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['block', 'language'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Add the page title block to the page.
    $this->drupalPlaceBlock('page_title_block', ['id' => 'stark_page_title']);

    // Create administrative user.
    $admin_user = $this->drupalCreateUser([
      'administer blocks',
      'administer themes',
    ]);
    $this->drupalLogin($admin_user);
  }

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-9.4.0.bare.standard.php.gz',
    ];
  }

  /**
   * Data provider for testPostUpdateAddContextualizePageTitle().
   *
   * @return array[][]
   *   The test cases.
   */
  public function providerTestPostUpdateAddContextualizePageTitle() {
    return [
      'Stark theme' => ['stark', FALSE],
      // For claro the 'contextual_title' configuration is enabled by default.
      'Claro theme' => ['claro', TRUE],
      'Olivero theme' => ['olivero', FALSE],
    ];
  }

  /**
   * Tests that title block is configured properly after update.
   *
   * @dataProvider providerTestPostUpdateAddContextualizePageTitle
   */
  public function testPostUpdateAddContextualizePageTitle(string $theme, bool $contextual_title_enabled): void {
    $this->runUpdates();

    $system_theme_config = $this->container->get('config.factory')
      ->getEditable('system.theme');
    $system_theme_config
      ->set('default', $theme)
      ->save();
    \Drupal::service('theme_installer')->install([$theme]);
    $edit['admin_theme'] = $theme;
    $this->drupalGet('admin/appearance');
    $this->submitForm($edit, 'Save configuration');
    $this->drupalGet('admin/structure/block');
    $this->drupalGet('admin/structure/block/manage/' . $theme . '_page_title');

    // If the title block is configured to show contextualized title then the value of the
    // configuration will be 1 otherwise 0.
    // @see \Drupal\Core\Block\Plugin\Block\PageTitleBlock::blockForm()
    if ($contextual_title_enabled) {
      $this->assertSession()->fieldValueEquals('settings[base_route_title]', 1);
    }
    else {
      $this->assertSession()->fieldValueEquals('settings[base_route_title]', 0);
    }
  }

}
