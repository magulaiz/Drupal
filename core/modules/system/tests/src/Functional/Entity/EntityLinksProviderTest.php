<?php

namespace Drupal\Tests\system\Functional\Entity;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the entity form.
 *
 * @group Entity
 */
class EntityLinksProviderTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['entity_test'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $web_user = $this->drupalCreateUser([
      'administer entity ui tests',
      'administer entity ui test types',
    ]);
    $this->drupalLogin($web_user);
  }

  /**
   * Tests the UI of the entity types using the entity links provider.
   */
  public function testEntityUI() {
    // Test the config entity UI.
    $this->drupalGet('admin/structure/entity_ui_test_type');
    // TODO! not working!
    // $this->assertSession()->linkExists("Add Entity UI Test Type");

    // Test the content entity UI.
    // TODO: test all the things!
  }

}
