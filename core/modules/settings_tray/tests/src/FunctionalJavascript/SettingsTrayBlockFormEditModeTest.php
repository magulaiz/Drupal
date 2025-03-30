<?php

declare(strict_types=1);

namespace Drupal\Tests\settings_tray\FunctionalJavascript;

/**
 * Tests enabling and disabling Edit Mode.
 *
 * @group settings_tray
 */
class SettingsTrayBlockFormEditModeTest extends SettingsTrayTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'settings_tray_test',
    'off_canvas_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $user = $this->createUser([
      'administer blocks',
      'access contextual links',
      'access toolbar',
    ]);
    $this->drupalLogin($user);
  }

  /**
   * Tests enabling and disabling Edit Mode.
   */
  public function testEditModeEnableDisable(): void {
    foreach (static::getTestThemes() as $theme) {
      $this->enableTheme($theme);
      $block = $this->placeBlock('system_powered_by_block');
      foreach (['contextual_link', 'toolbar_link'] as $enable_option) {
        $this->drupalGet('user');
        $this->assertEditModeDisabled();
        switch ($enable_option) {
          // Enable Edit mode.
          case 'contextual_link':
            $this->clickContextualLink($this->getBlockSelector($block), "Quick edit");
            $this->waitForOffCanvasToOpen();
            $this->assertEditModeEnabled();
            break;

          case 'toolbar_link':
            $this->enableEditMode();
            break;
        }
        $this->disableEditMode();

        // Make another page request to ensure Edit mode is still disabled.
        $this->drupalGet('user');
        $this->assertEditModeDisabled();
        // Make sure on this page request it also re-enables and disables
        // correctly.
        $this->enableEditMode();
        $this->disableEditMode();
      }
    }
  }

}
