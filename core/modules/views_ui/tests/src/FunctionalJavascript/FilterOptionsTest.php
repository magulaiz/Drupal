<?php

declare(strict_types=1);

namespace Drupal\Tests\views_ui\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests the JavaScript filtering of options in add handler form.
 *
 * @group views_ui
 */
class FilterOptionsTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'views',
    'views_ui',
    'views_ui_test_field',
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

    $admin_user = $this->drupalCreateUser([
      'administer views',
      'administer site configuration',
      'access site in maintenance mode',
    ]);
    $this->drupalLogin($admin_user);
  }

  /**
   * Tests filtering options in the 'Add fields' dialog.
   */
  public function testFilterOptionsAddFields() {
    $this->drupalGet('admin/structure/views/view/content');

    $session = $this->getSession();
    $web_assert = $this->assertSession();
    $page = $session->getPage();

    // Open the dialog.
    $page->clickLink('views-add-field');

    // Wait for the popup to open and the search field to be available.
    $options_search = $web_assert->waitForField('override[controls][options_search]');

    // Test that the both special fields are visible.
    $this->assertTrue($page->findField('name[views.views_test_field_1]')->isVisible());
    $this->assertTrue($page->findField('name[views.views_test_field_2]')->isVisible());

    // Test the ".title" field in search.
    $options_search->setValue('FIELD_1_TITLE');
    $page->waitFor(10, function () use ($page) {
      return !$page->findField('name[views.views_test_field_2]')->isVisible();
    });
    $this->assertTrue($page->findField('name[views.views_test_field_1]')->isVisible());
    $this->assertFalse($page->findField('name[views.views_test_field_2]')->isVisible());

    // Test the ".description" field in search.
    $options_search->setValue('FIELD_2_DESCRIPTION');
    $page->waitFor(10, function () use ($page) {
      return !$page->findField('name[views.views_test_field_1]')->isVisible();
    });
    $this->assertTrue($page->findField('name[views.views_test_field_2]')->isVisible());
    $this->assertFalse($page->findField('name[views.views_test_field_1]')->isVisible());

    // Test the "label" field not in search.
    $options_search->setValue('FIELD_1_LABEL');
    $page->waitFor(10, function () use ($page) {
      return !$page->findField('name[views.views_test_field_2]')->isVisible();
    });
    $this->assertFalse($page->findField('name[views.views_test_field_2]')->isVisible());
    $this->assertFalse($page->findField('name[views.views_test_field_1]')->isVisible());
  }

  /**
   * Tests filtering options in the 'Add fields' dialog.
   */
  public function testFilterOptionsAddFieldsWithMaintenanceMode() {
    // Test popup when in maintenance mode. Copied from SiteMaintenanceTest.php.
    $edit = [
      'maintenance_mode' => 1,
    ];
    $this->drupalGet('admin/config/development/maintenance');
    $this->assertSession()->pageTextContains('Visitors will only see the maintenance mode message. Only users with the "Use the site in maintenance mode" permission will be able to access the site. Authorized users can log in directly via the user login page.');
    $this->submitForm($edit, 'Save configuration');

    $this->drupalGet('admin/structure/views/view/content');

    $session = $this->getSession();
    $web_assert = $this->assertSession();
    $page = $session->getPage();

    $add_link = $page->findById('views-add-field');
    $this->assertTrue($add_link->isVisible(), 'Add fields button found.');
    $page->clickLink('views-add-field');

    // Wait for the popup to open and the search field to be available.
    $web_assert->waitForField('override[controls][options_search]');
    $this->assertSession()->pageTextContains('Operating in maintenance mode. Go online.');
    // Test that the both special fields are visible.
    $this->assertTrue($page->findField('name[views.views_test_field_1]')->isVisible());
    $this->assertTrue($page->findField('name[views.views_test_field_2]')->isVisible());
  }

}
