<?php

namespace Drupal\Tests\path_alias\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\Traits\Core\PathAliasTestTrait;

/**
 * Tests sites with multiple UI languages but content in a single language.
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

    // Enable the language selector on article nodes.
    $this->drupalGet('admin/config/regional/content-language');
    $this->submitForm(["entity_types[node]" => TRUE, "settings[node][article][settings][language][language_alterable]" => TRUE], 'Save configuration');

    $this->drupalLogout();
  }

  /**
   * Tests URL aliases work.
   */
  public function testPathAlias() {
    // @todo create an admin user with permissions
    $this->drupalLogin($this->rootUser);

    $this->drupalGet('node/add/article');
    $this->submitForm(['title[0][value]' => 'Test content DE', 'path[0][alias]' => '/test-content-de'], 'Save');
    $this->assertSession()->statusCodeEquals(200);
    // Should be on /test-content but we'll be on node/1.
    $this->assertSession()->linkByHrefExists('/test-content-de');
    $this->drupalGet('test-content-de');
    $this->assertSession()->statusCodeEquals(200);

    // Prove the current state of the entities.
    $node = $this->drupalGetNodeByTitle('Test content DE');
    $this->assertSame('de', $node->language()->getId());
    $this->assertSame('en', $this->rootUser->getPreferredLangcode());

    // Change the user preferred language to de.
    $this->drupalGet('user/1/edit');
    $this->submitForm(['preferred_langcode' => 'de'], 'Save');
    $this->assertSession()->pageTextContains('The changes have been saved.');

    $this->drupalGet('test-content-de');
    $this->assertSession()->statusCodeEquals(200);

    // Add an english article.
    $this->drupalGet('node/add/article');
    $this->submitForm(['title[0][value]' => 'Test content EN', 'path[0][alias]' => '/test-content-en', 'langcode[0][value]' => 'en'], 'Save');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkByHrefExists('/test-content-en');
    $this->drupalGet('test-content-en');
    $this->assertSession()->statusCodeEquals(200);

    // Prove the current state of the node.
    $node = $this->drupalGetNodeByTitle('Test content EN');
    $this->assertSame('en', $node->language()->getId());

    // Change the user preferred language to en.
    $this->drupalGet('user/1/edit');
    $this->submitForm(['preferred_langcode' => 'en'], 'Save');
    $this->assertSession()->pageTextContains('The changes have been saved.');
    $this->drupalGet('test-content-en');
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Tests URL aliases work after setting the prefix to an empty string.
   */
  public function testPathAliasAfterFixingPathPrefixes() {
    // @todo create an admin user with permissions
    $this->drupalLogin($this->rootUser);

    // Set the language url prefix config (even though it is disabled).
    $this->drupalGet('admin/config/regional/language/detection');
    $this->clickLink('Configure');
    $this->assertSession()->pageTextContains('Part of the URL that determines language');
    $this->submitForm(['prefix[de]' => ''], 'Save configuration');
    $this->assertSession()->pageTextContains('The configuration options have been saved.');

    // The DE node test will now pass. The EN node will not.
    $this->testPathAlias();
  }

}
