<?php

namespace Drupal\Tests\block\Functional;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Tests adding config for branding block.
 *
 * @group block
 */
class BlockConfigUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // Set a site slogan.
    $this->config('system.site')
      ->set('slogan', 'Community plumbing')
      ->set('name', 'Brand Name')
      ->save();
    // Add the system branding block to the page.
    $this->drupalPlaceBlock('system_branding_block', [
      'region' => 'header',
      'id' => 'site_branding',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles(): void {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../modules/system/tests/fixtures/update/drupal-9.4.0.filled.standard.php.gz',
    ];
  }

  /**
   * Tests adding config to branding block.
   *
   * @see block_post_update_branding_block()
   */
  public function testBrandingBlockConfigUpdate(): void {
    $user = $this->drupalCreateUser(['administer blocks']);
    $this->drupalLogin($user);
    $this->drupalGet('');
    // Check absence of config key in array before running update.
    $this->assertArrayNotHasKey('config', $this->config('block.block.site_branding')->get('dependencies'));
    $this->runUpdates();
    $this->assertSame(['system.site'], $this->config('block.block.site_branding')->get('dependencies')['config']);
  }

}
