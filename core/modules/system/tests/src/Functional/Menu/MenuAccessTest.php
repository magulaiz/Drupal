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
    file_put_contents("/Users/ted.bowman/sites/test.html", $this->getSession()->getPage()->getOuterHtml());
    $this->assertMenuItemRouteAccess('admin/structure', 200);
    $this->assertMenuItemRouteAccess('admin/people', 200);

    // This user has access to administer menus so the structure parent page
    // should be accessible.
    $this->drupalLogin($menuAdmin);
    $this->assertMenuItemRouteAccess('admin/structure', 200);
    $this->assertMenuItemRouteAccess('admin/people', 403);

    // This user has access to administer filters so the config parent page
    // should be accessible.
    $this->drupalLogin($filterAdmin);
    $this->assertMenuItemRouteAccess('admin/config', 200);
    $this->assertMenuItemRouteAccess('admin/people', 403);

    // This user doesn't have access to any of the child pages, so the parent
    // pages should not be accessible.
    $this->drupalLogin($webUser);
    $this->assertMenuItemRouteAccess('admin/structure', 403);
    $this->assertMenuItemRouteAccess('admin/people', 403);
    // As menu_test adds a menu link under config.
    $this->assertMenuItemRouteAccess('admin/config', 200);

    // Some comment.
    // Create a user with access to the parent but not to the child.
    $parentUser = $this->drupalCreateUser([
      'access parent test page',
    ]);
    $child1User = $this->drupalCreateUser([
      'access parent test page',
      'access child1 test page',
    ]);
    $superChild1User = $this->drupalCreateUser([
      'access parent test page',
      'access child1 test page',
      'access child2 test page',
      'access super child1 test page',
    ]);
    $superChild2User = $this->drupalCreateUser([
      'access parent test page',
      'access child1 test page',
      'access child2 test page',
      'access super child2 test page',
    ]);
    $superChild3User = $this->drupalCreateUser([
      'access parent test page',
      'access child1 test page',
      'access child2 test page',
      'access super child3 test page',
    ]);
    $this->drupalLogin($parentUser);
    $this->assertMenuItemRouteAccess(Url::fromRoute('menu_test.parent_test'), 403);
    $this->drupalLogin($child1User);
    $this->assertMenuItemRouteAccess(Url::fromRoute('menu_test.parent_test'), 403);
    $this->drupalLogin($superChild1User);
    $this->assertMenuItemRouteAccess(Url::fromRoute('menu_test.parent_test'), 200);
    $this->assertMenuItemRouteAccess(Url::fromRoute('menu_test.child1_test'), 200);
    $this->assertMenuItemRouteAccess(Url::fromRoute('menu_test.child2_test'), 403);
    $this->assertMenuItemRouteAccess(Url::fromRoute('menu_test.super_child1_test'), 200);
    $this->assertMenuItemRouteAccess(Url::fromRoute('menu_test.super_child2_test'), 403);
    $this->assertMenuItemRouteAccess(Url::fromRoute('menu_test.super_child3_test'), 403);
    $this->drupalLogin($superChild2User);
    $this->assertMenuItemRouteAccess(Url::fromRoute('menu_test.parent_test'), 200);
    $this->assertMenuItemRouteAccess(Url::fromRoute('menu_test.child1_test'), 403);
    $this->assertMenuItemRouteAccess(Url::fromRoute('menu_test.child2_test'), 200);
    $this->assertMenuItemRouteAccess(Url::fromRoute('menu_test.super_child1_test'), 403);
    $this->assertMenuItemRouteAccess(Url::fromRoute('menu_test.super_child2_test'), 200);
    $this->assertMenuItemRouteAccess(Url::fromRoute('menu_test.super_child3_test'), 403);
    $this->drupalLogin($superChild3User);
    $this->assertMenuItemRouteAccess(Url::fromRoute('menu_test.parent_test'), 200);
    $this->assertMenuItemRouteAccess(Url::fromRoute('menu_test.child1_test'), 403);
    $this->assertMenuItemRouteAccess(Url::fromRoute('menu_test.child2_test'), 200);
    $this->assertMenuItemRouteAccess(Url::fromRoute('menu_test.super_child1_test'), 403);
    $this->assertMenuItemRouteAccess(Url::fromRoute('menu_test.super_child2_test'), 403);
    $this->assertMenuItemRouteAccess(Url::fromRoute('menu_test.super_child3_test'), 200);

    // Test a route that has parameter defined in the menu item.
    $this->drupalLogin($parentUser);
    $this->assertMenuItemRouteAccess(Url::fromRoute('menu_test.parent_test_param', ['param' => 'param-in-menu']), 403);
    $this->drupalLogin($child1User);
    $this->assertMenuItemRouteAccess(Url::fromRoute('menu_test.parent_test_param', ['param' => 'param-in-menu']), 200);

    // Test a route that does not have a parameter defined in the menu item but
    // uses the route default parameter.
    // @todo Change the following test case to use a parent menu item that also
    // uses the routes default parameter.
    $this->drupalLogin($parentUser);
    $this->assertMenuItemRouteAccess(Url::fromRoute('menu_test.parent_test_param', ['param' => 'child_uses_default']), 403);
    $this->drupalLogin($child1User);
    $this->assertMenuItemRouteAccess(Url::fromRoute('menu_test.parent_test_param', ['param' => 'child_uses_default']), 200);

    // Test a route that does have a parameter defined in the menu item and that
    // parameter value is equal to the default value specific in the route.
    $this->drupalLogin($parentUser);
    $this->assertMenuItemRouteAccess(Url::fromRoute('menu_test.parent_test_param_explicit', ['param' => 'my_default']), 403);
    $this->drupalLogin($child1User);
    $this->assertMenuItemRouteAccess(Url::fromRoute('menu_test.parent_test_param_explicit', ['param' => 'my_default']), 200);

    // If we try to access a route that takes a parameter but route is not in the
    // with that parameter we should always be denied access.
    $this->drupalLogin($parentUser);
    $this->assertMenuItemRouteAccess(Url::fromRoute('menu_test.parent_test_param', ['param' => 'any-other']), 403);
    $this->drupalLogin($child1User);
    $this->assertMenuItemRouteAccess(Url::fromRoute('menu_test.parent_test_param', ['param' => 'any-other']), 403);
  }

  /**
   * Assert a route request connected to a menu item has the expected access.
   *
   * @param string|\Drupal\Core\Url $path
   *   The path variable as passed to \Drupal\Tests\UiHelperTrait::drupalGet().
   * @param int $expected_status
   *   The expected request status.
   */
  private function assertMenuItemRouteAccess(string|Url $path, int $expected_status): void {
    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals($expected_status);
    $this->assertSession()->pageTextNotContains('You do not have any administrative items.');
  }

}
