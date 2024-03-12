<?php

namespace Drupal\Tests\user\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests user logout.
 *
 * @group user
 */
class UserLogoutTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['user', 'block'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp() : void {
    parent::setUp();

    $this->placeBlock('system_menu_block:account');
  }

  /**
   * Tests user logout functionality.
   */
  public function testLogout() {
    $account = $this->createUser([]);
    $this->drupalLogin($account);

    // Test invalid csrf token.
    $this->drupalGet('user/logout', ['query' => ['token' => '123']]);
    $this->assertSession()->buttonExists('Log out');
    $this->getSession()->getPage()->clickLink('Log out');
    $this->drupalGet('user/login');
    $this->assertSession()->fieldExists('name');

    $this->drupalResetSession();
    $this->drupalLogin($account);

    // Test with valid logout link.
    $this->drupalGet('user');
    $this->getSession()->getPage()->clickLink('Log out');
    $this->drupalGet('user/login');
    $this->assertSession()->fieldExists('name');
  }

}
