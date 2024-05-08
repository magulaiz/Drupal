<?php

declare(strict_types=1);

namespace Drupal\Tests\content_translation\Functional;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversFunction;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the content translation UI check skip.
 *
 * @group content_translation
 */
#[CoversClass(\Drupal\language\Form\ContentLanguageSettingsForm::class)]
#[CoversFunction('_content_translation_form_language_content_settings_form_alter')]
class ContentTranslationUISkipTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['content_translation_test', 'user', 'node'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests the content_translation_ui_skip key functionality.
   */
  public function testUICheckSkip() {
    $admin_user = $this->drupalCreateUser([
      'translate any entity',
      'administer content translation',
      'administer languages',
    ]);
    $this->drupalLogin($admin_user);
    // Visit the content translation.
    $this->drupalGet('admin/config/regional/content-language');

    // Check the message regarding UI integration.
    $this->assertSession()->pageTextContains('Test entity - Translatable skip UI check');
    $this->assertSession()->pageTextContains('Test entity - Translatable check UI (Translation is not supported)');
  }

}
