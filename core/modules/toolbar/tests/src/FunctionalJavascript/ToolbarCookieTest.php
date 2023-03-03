<?php

namespace Drupal\Tests\toolbar\FunctionalJavascript;

use Drupal\Component\Serialization\Json;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests the cookie set by the toolbar.
 *
 * @group toolbar
 */
class ToolbarCookieTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['toolbar', 'node'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  public function testToolbarCookie() {
    $admin_user = $this->drupalCreateUser([
      'access toolbar',
      'administer site configuration',
      'access content overview',
    ]);
    $this->drupalLogin($admin_user);
    $user_name = $admin_user->getAccountName();
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();

    $this->drupalGet('<front>');
    $this->assertNotEmpty($this->assertSession()->waitForElement('css', 'body.toolbar-horizontal'));
    $this->assertNotEmpty($this->assertSession()->waitForElementVisible('css', '.toolbar-tray'));
    $this->assertSession()->waitForElementRemoved('css', '.toolbar-loading');

    $page->clickLink('toolbar-item-user');
    $this->assertNotEmpty($assert_session->waitForElementVisible('css', '#toolbar-item-user.is-active'));

    // Expected cookie values with the user tray open with horizontal
    // orientation.
    $expected = [
      'toolbarUserName' => $user_name,
      'orientation' => 'horizontal',
      'hasActiveTab' => TRUE,
      'activeTabId' => 'toolbar-item-user',
      'activeTray' => 'toolbar-item-user-tray',
      'isOriented' => TRUE,
      'isFixed' => TRUE,
    ];
    $toolbar_state_cookie = JSON::decode($this->getSession()->getCookie('toolbarState'));
    $this->assertSame($expected, $toolbar_state_cookie);

    $page->clickLink('toolbar-item-user');
    $assert_session->assertNoElementAfterWait('css', '#toolbar-item-user.is-active');

    // Update expected cookie values to reflect no tray being open.
    $expected['hasActiveTab'] = FALSE;
    $expected['activeTabId'] = NULL;
    unset($expected['activeTray']);
    $toolbar_state_cookie = JSON::decode($this->getSession()->getCookie('toolbarState'));
    $this->assertSame($expected, $toolbar_state_cookie);

    $page->clickLink('toolbar-item-administration');
    $orientation_toggle = $assert_session->waitForElementVisible('css', '[title="Vertical orientation"]');
    $orientation_toggle->click();
    $assert_session->waitForElementVisible('css', 'body.toolbar-vertical');

    // Update expected cookie values to reflect the administration tray being
    // open with vertical orientation.
    $expected['orientation'] = 'vertical';
    $expected['hasActiveTab'] = TRUE;
    $expected['activeTabId'] = 'toolbar-item-administration';
    $expected['activeTray'] = 'toolbar-item-administration-tray';
    $toolbar_state_cookie = JSON::decode($this->getSession()->getCookie('toolbarState'));
    $this->assertSame($expected, $toolbar_state_cookie);

    $this->getSession()->resizeWindow(600, 600);

    // Update expected cookie values to reflect the viewport being at a width
    // that is narrow enough that the toolbar isn't fixed.
    $expected['isFixed'] = FALSE;
    $toolbar_state_cookie = JSON::decode($this->getSession()->getCookie('toolbarState'));
    $this->assertSame($expected, $toolbar_state_cookie);

    $this->drupalLogout();
    $toolbar_state_cookie = JSON::decode($this->getSession()->getCookie('toolbarState'));

    // After logging out, the cookie should be empty.
    $this->assertSame([], $toolbar_state_cookie);
  }

}
