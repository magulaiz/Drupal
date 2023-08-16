<?php

namespace Drupal\Tests\system\Functional\Menu;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the route access checks on menu links.
 *
 * @group Menu
 */
class MenuAccessTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['block', 'filter', 'menu_test', 'toolbar'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalPlaceBlock('local_tasks_block');
  }

  /**
   * Tests menu link for route with access check.
   *
   * @see \Drupal\menu_test\Access\AccessCheck::access()
   */
  public function testMenuBlockLinksAccessCheck() {
    $this->drupalPlaceBlock('system_menu_block:account');
    // Test that there's link rendered on the route.
    $this->drupalGet('menu_test_access_check_session');
    $this->assertSession()->linkExists('Test custom route access check');
    // Page is still accessible but there should be no menu link.
    $this->drupalGet('menu_test_access_check_session');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkNotExists('Test custom route access check');
    // Test that page is no more accessible.
    $this->drupalGet('menu_test_access_check_session');
    $this->assertSession()->statusCodeEquals(403);

    // Check for access to a restricted local task from a default local task.
    $this->drupalGet('foo/asdf');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkByHrefExists('foo/asdf');
    $this->assertSession()->linkByHrefExists('foo/asdf/b');
    $this->assertSession()->linkByHrefNotExists('foo/asdf/c');

    // Attempt to access a restricted local task.
    $this->drupalGet('foo/asdf/c');
    $this->assertSession()->statusCodeEquals(403);
    // No tab linking to foo/asdf should be found.
    $this->assertSession()->elementNotExists('xpath', $this->assertSession()->buildXPathQuery(
      '//ul[@class="tabs primary"]/li/a[@href=:href]', [
        ':href' => Url::fromRoute('menu_test.router_test1', ['bar' => 'asdf'])->toString(),
      ]
    ));
    $this->assertSession()->linkByHrefNotExists('foo/asdf/b');
    $this->assertSession()->linkByHrefNotExists('foo/asdf/c');
  }

  /**
   * Test routes implementing _access_admin_menu_block_page.
   */
  public function testSystemAdminMenuBlockAccessCheck(): void {
    // Create an admin user.
    $adminUser = $this->drupalCreateUser([], NULL, TRUE);

    // Create a user with 'administer menu' permission.
    $menuAdmin = $this->drupalCreateUser([
      'access administration pages',
      'administer menu',
    ]);

    // Create a user with 'administer filters' permission.
    $filterAdmin = $this->drupalCreateUser([
      'access administration pages',
      'administer filters',
    ]);

    // Create a user with 'access administration pages' permission.
    $webUser = $this->drupalCreateUser([
      'access administration pages',
    ]);

    // An admin user has access to all parent pages.
    $this->drupalLogin($adminUser);
    file_put_contents("/Users/ted.bowman/sites/test.html", $this->getSession()->getPage()->getContent());
    $this->drupalGet('admin/structure');
    $this->assertSession()->statusCodeEquals(200);
    $this->drupalGet('admin/people');
    $this->assertSession()->statusCodeEquals(200);

    // This user has access to administer menus so the structure parent page
    // should be accessible.
    $this->drupalLogin($menuAdmin);
    $this->drupalGet('admin/structure');
    $this->assertSession()->statusCodeEquals(200);
    $this->drupalGet('admin/people');
    $this->assertSession()->statusCodeEquals(403);

    // This user has access to administer filters so the config parent page
    // should be accessible.
    $this->drupalLogin($filterAdmin);
    $this->drupalGet('admin/config');
    $this->assertSession()->statusCodeEquals(200);
    $this->drupalGet('admin/people');
    $this->assertSession()->statusCodeEquals(403);

    // This user doesn't have access to any of the child pages, so the parent
    // pages should not be accessible.
    $this->drupalLogin($webUser);
    $this->drupalGet('admin/structure');
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalGet('admin/people');
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalGet('admin/config');
    // As menu_test adds a menu link under config.
    $this->assertSession()->statusCodeEquals(200);

    // Some comment.
    // Create a user with access to the parent but not to the child.
    $parentUser = $this->drupalCreateUser([
      'access parent test page',
    ]);
    $childUser = $this->drupalCreateUser([
      'access parent test page',
      'access child test page',
    ]);
    $superChildUser = $this->drupalCreateUser([
      'access parent test page',
      'access child test page',
      'access super child test page',
    ]);
    $this->drupalLogin($parentUser);
    $this->drupalGet(Url::fromRoute('menu_test.parent_test'));
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalLogin($childUser);
    $this->drupalGet(Url::fromRoute('menu_test.parent_test'));
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalLogin($superChildUser);
    $this->drupalGet(Url::fromRoute('menu_test.parent_test'));
    $this->assertSession()->statusCodeEquals(200);

    // Test a route that has parameter defined in the menu item.
    $this->drupalLogin($parentUser);
    $this->drupalGet(Url::fromRoute('menu_test.parent_test_param', ['param' => 'param-in-menu']));
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalLogin($childUser);
    $this->drupalGet(Url::fromRoute('menu_test.parent_test_param', ['param' => 'param-in-menu']));
    $this->assertSession()->statusCodeEquals(200);

    // Test a route that does not have a parameter defined in the menu item but
    // uses the route default parameter.
    $this->drupalLogin($parentUser);
    $this->drupalGet(Url::fromRoute('menu_test.parent_test_param', ['param' => 'my_default']));
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalLogin($childUser);
    $this->drupalGet(Url::fromRoute('menu_test.parent_test_param', ['param' => 'my_default']));
    $this->assertSession()->statusCodeEquals(200);

    // Test a route that does have a parameter defined in the menu item and that
    // parameter value is equal to the default value specific in the route.
    $this->drupalLogin($parentUser);
    $this->drupalGet(Url::fromRoute('menu_test.parent_test_param_explicit', ['param' => 'my_default']));
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalLogin($childUser);
    $this->drupalGet(Url::fromRoute('menu_test.parent_test_param_explicit', ['param' => 'my_default']));
    $this->assertSession()->statusCodeEquals(200);

    // If we try to access a route that takes a parameter but route is not in the
    // with that parameter we should always be denied access.
    $this->drupalLogin($parentUser);
    $this->drupalGet(Url::fromRoute('menu_test.parent_test_param', ['param' => 'any-other']));
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalLogin($childUser);
    $this->drupalGet(Url::fromRoute('menu_test.parent_test_param', ['param' => 'any-other']));
    $this->assertSession()->statusCodeEquals(403);

    $this->drupalLogin($parentUser);
    $this->drupalGet(Url::fromRoute('menu_test.parent_test_default'));
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalLogin($childUser);
    $this->drupalGet(Url::fromRoute('menu_test.parent_test_default'));
    $this->assertSession()->statusCodeEquals(200);
  }

}
