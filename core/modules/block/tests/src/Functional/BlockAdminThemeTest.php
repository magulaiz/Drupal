<?php

namespace Drupal\Tests\block\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the block system with admin themes.
 *
 * @group block
 */
class BlockAdminThemeTest extends BrowserTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = ['block', 'contextual'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Check for the accessibility of the admin theme on the block admin page.
   */
  public function testAdminTheme() {
    // Create administrative user.
    $admin_user = $this->drupalCreateUser([
      'administer blocks',
      'administer themes',
    ]);
    $this->drupalLogin($admin_user);

    // Ensure that access to block admin page is denied when theme is not
    // installed.
    $this->drupalGet('admin/appearance/block/list/olivero');
    $this->assertSession()->statusCodeEquals(403);

    // Install admin theme and confirm that tab is accessible.
    \Drupal::service('theme_installer')->install(['olivero']);
    $edit['admin_theme'] = 'olivero';
    $this->drupalGet('admin/appearance');
    $this->submitForm($edit, 'Save configuration');
    $this->drupalGet('admin/appearance/block/list/olivero');
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Ensure contextual links are disabled in Claro theme.
   */
  public function testClaroAdminTheme() {
    // Create administrative user.
    $admin_user = $this->drupalCreateUser([
      'access administration pages',
      'administer themes',
      'access contextual links',
      'view the administration theme',
    ]);
    $this->drupalLogin($admin_user);

    // Install admin theme and confirm that tab is accessible.
    \Drupal::service('theme_installer')->install(['claro']);
    $edit['admin_theme'] = 'claro';
    $this->drupalGet('admin/appearance');
    $this->submitForm($edit, 'Save configuration');

    // Define our block settings.
    $settings = [
      'theme' => 'claro',
      'region' => 'header',
    ];

    // Place a block.
    $block = $this->drupalPlaceBlock('local_tasks_block', $settings);

    // Open admin page.
    $this->drupalGet('admin');

    // Check if contextual link classes are unavailable.
    $this->assertSession()->responseNotContains('<div data-contextual-id="block:block=' . $block->id() . ':langcode=en"></div>');
    $this->assertSession()->responseNotContains('contextual-region');
  }

  /**
   * Tests the deprecation message from the old block paths.
   *
   * @param string $old_path
   *   The deprecated path.
   * @param string $new_path
   *   The replacement path.
   * @param bool $message
   *   (optional) If TRUE, then test for the deprecation message.
   *
   * @group legacy
   *
   * @dataProvider providerTestBlockPageRedirects
   */
  public function testBlockPageRedirects(string $old_path, string $new_path, bool $message = TRUE): void {
    // Create administrative user.
    $admin_user = $this->drupalCreateUser([
      'administer blocks',
      'administer themes',
    ]);
    $this->drupalLogin($admin_user);

    // Place a block.
    $settings = [
      'theme' => 'stark',
      'region' => 'header',
    ];
    $block = $this->drupalPlaceBlock('local_tasks_block', $settings);

    [$old_path, $new_path] = str_replace(
      ['{theme}', '{block}', '{plugin_id}'],
      ['stark', $block->id(), 'stark_' . $block->id()],
      [$old_path, $new_path]
    );
    $query = ['region' => 'header', 'theme' => 'claro'];
    $final_url = $this->buildUrl($new_path, ['query' => $query]);

    $this->expectDeprecation('The path /admin/structure/block, with its child paths, is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use /admin/appearance/block. See https://www.drupal.org/node/3320855.');
    $this->drupalGet("$old_path", ['query' => $query]);
    $this->assertEquals($final_url, $this->getUrl());
    if ($message) {
      $base_path = parse_url($this->baseUrl, PHP_URL_PATH) ?? '';
      $this->assertSession()
        ->statusMessageContains("You have been redirected from {$base_path}{$old_path}. Update links, shortcuts, and bookmarks to use {$base_path}{$new_path}.");
    }
    else {
      $this->assertSession()
        ->statusMessageNotContains($old_path);
    }
  }

  /**
   * Provides data for testBlockPageRedirects.
   */
  public function providerTestBlockPageRedirects(): array {
    return [
      'block.admin_demo' => [
        '/admin/structure/block/demo/{theme}',
        '/admin/appearance/block/demo/{theme}',
        FALSE,
      ],
      'entity.block.delete_form' => [
        '/admin/structure/block/manage/{block}/delete',
        '/admin/appearance/block/manage/{block}/delete',
      ],
      'entity.block.edit_form' => [
        '/admin/structure/block/manage/{block}',
        '/admin/appearance/block/manage/{block}',
      ],
      'block.admin_display' => [
        '/admin/structure/block',
        '/admin/appearance/block',
      ],
      'block.admin_display_theme' => [
        '/admin/structure/block/list/{theme}',
        '/admin/appearance/block/list/{theme}',
      ],
      'block.admin_library' => [
        '/admin/structure/block/library/{theme}',
        '/admin/appearance/block/library/{theme}',
      ],
      'block.admin_add' => [
        '/admin/structure/block/add/{plugin_id}/{theme}',
        '/admin/appearance/block/add/{plugin_id}/{theme}',
      ],
    ];
  }

}
