<?php

namespace Drupal\Tests\path_alias\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\Traits\Core\PathAliasTestTrait;

/**
 * Tests altering the inbound path and the outbound path.
 *
 * @group path_alias
 */
class MultilingualUiMonolingualContentTest extends BrowserTestBase {

  use PathAliasTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['node', 'locale', 'path_alias', 'path'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  protected function setUp(): void {
    parent::setUp();

    $this->drupalCreateContentType(['type' => 'article']);

    // @todo create an admin user with permissions
    $this->drupalLogin($this->rootUser);

    // Add the German language
    $this->drupalGet('admin/config/regional/language/add');
    $this->submitForm(['predefined_langcode' => 'de'], 'Add language');
    $this->assertSession()->pageTextContains('The language German has been created and can now be used.');

    // Change the default language to German
    $this->submitForm(['site_default_language' => 'de'], 'Save configuration');
    $this->assertSession()->pageTextContains('Configuration saved.');

    // Set up detection and selection to not use URL detection.
    $this->drupalGet('admin/config/regional/language/detection');
    $this->submitForm([
      'language_interface[enabled][language-url]' => 0,
      'language_interface[enabled][language-user]' => 1,
    ], 'Save settings');
    $this->assertSession()->pageTextContains('Language detection configuration saved.');
    $this->drupalLogout();
  }

  /**
   * Tests URL aliases work.
   */
  public function testPathAlias() {
    // @todo create an admin user with permissions
    $this->drupalLogin($this->rootUser);

    $this->drupalGet('node/add/article');
    $this->submitForm(['title[0][value]' => 'Test content', 'path[0][alias]' => '/test-content'], 'Save');
    $this->assertSession()->statusCodeEquals(200);
    // Should be on /test-content but we'll be on node/1.
    $this->assertSession()->addressEquals('node/1');
    // The message will use the aliased URL.
    $this->assertSession()->linkByHrefExists('/test-content');
    // But getting it will 404.
    $this->drupalGet('test-content');
    $this->assertSession()->statusCodeEquals(200);
  }

}
