<?php

namespace Drupal\Tests\block\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests page title block.
 *
 * @group Block
 */
class PageTitleBlockTest extends BrowserTestBase {
  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = ['block', 'update'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // Add the page title block to the page.
    $this->drupalPlaceBlock('page_title_block', ['region' => 'header', 'id' => 'stark_page_title']);

    // Create administrative user.
    $admin_user = $this->drupalCreateUser([
      'administer blocks',
      'administer themes',
      'administer software updates',
    ]);
    $this->drupalLogin($admin_user);
  }

  /**
   * Data provider for testTestContextualizeTitle().
   *
   * @return array[][]
   *   The test cases.
   */
  public function providerTestContextualizeTitle() {
    return [
      'Stark theme' => [
        $this->defaultTheme,
        FALSE,
        'Update',
        'Extend',
      ],
      // For Claro theme the contextualize_title settings is enabled by default
      // hence the title will always be contextualized.
      'Claro theme' => [
        'claro',
        TRUE,
        'Extend',
        'Extend',
      ],
      'Olivero theme' => [
        'olivero',
        FALSE,
        'Update',
        'Extend',
      ],
    ];
  }

  /**
   * Check if the page title block shows contextualized title.
   *
   * @dataProvider providerTestContextualizeTitle
   */
  public function testContextualizeTitle(string $theme, bool $contextualize_title_enabled, string $non_contextualized_title, string $contextualized_title) {
    if ($theme !== $this->defaultTheme) {
      $system_theme_config = $this->container->get('config.factory')
        ->getEditable('system.theme');
      $system_theme_config
        ->set('default', $theme)
        ->save();
      \Drupal::service('theme_installer')->install([$theme]);
    }
    $edit['admin_theme'] = $theme;
    $this->drupalGet('admin/appearance');
    $this->submitForm($edit, 'Save configuration');

    // Make sure the title shown is non-contextualized.
    $this->drupalGet('admin/modules/update');
    $this->assertSession()->elementTextEquals('css', 'h1', $non_contextualized_title);

    // Checking if the title block is configured for showing contextualized
    // title and if it's not then configure it.
    $this->drupalGet('admin/structure/block/manage/' . $theme . '_page_title');
    if ($contextualize_title_enabled) {
      $this->assertSession()->checkboxChecked('settings[contextualize_title]');
    }
    else {
      $this->assertSession()->checkboxNotChecked('settings[contextualize_title]');
      $this->submitForm(['settings[contextualize_title]' => TRUE], 'Save block');
    }

    // Make sure the title shown is contextualized.
    $this->drupalGet('admin/modules/update');
    $this->assertSession()->elementTextEquals('css', 'h1', $contextualized_title);
  }

}
