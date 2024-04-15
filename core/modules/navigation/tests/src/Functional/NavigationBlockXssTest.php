<?php

declare(strict_types=1);

namespace Drupal\Tests\navigation\Functional;

use Drupal\Core\Url;
use Drupal\system\Entity\Menu;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\navigation\Traits\NavigationBlockCreationTrait;

/**
 * Tests that the navigation module properly escapes nav block descriptions.
 *
 * @group navigation
 */
class NavigationBlockXssTest extends BrowserTestBase {

  use NavigationBlockCreationTrait;

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = ['navigation', 'menu_ui'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests nothing is escaped other than navigation blocks explicitly tested.
   */
  public function testNoUnexpectedEscaping() {
    $this->drupalLogin($this->drupalCreateUser([
      'administer navigation_block',
      'access administration pages',
    ]));
    $this->drupalGet(Url::fromRoute('navigation_block.admin_display'));
    $this->clickLink('Place navigation block');
    $this->assertSession()->assertNoEscaped('<');
  }

  /**
   * Tests XSS in Place navigation block page.
   */
  public function testXssInPlaceBlock() {
    $this->container->get('module_installer')->install(['navigation_test']);
    $this->drupalLogin($this->drupalCreateUser([
      'administer navigation_block',
      'access administration pages',
    ]));
    $this->drupalGet('admin/config/user-interface/navigation-block/library');
    $this->assertSession()->responseNotContains("<script>alert('XSS subject');</script>");
    $this->assertSession()->assertEscaped("<script>alert('XSS subject');</script>");
    $this->assertSession()->responseNotContains("<script>alert('XSS category');</script>");
    $this->assertSession()->assertEscaped("<script>alert('XSS category');</script>");
  }

  /**
   * Tests XSS in title.
   */
  public function testXssInTitle() {
    $this->container->get('module_installer')->install(['navigation_test']);
    \Drupal::state()->set('navigation_test.content', $this->randomMachineName());
    $this->drupalPlaceNavigationBlock('test_xss_title', [
      'label' => '<script>alert("XSS label");</script>',
      'label_display' => TRUE,
    ]);
    $this->drupalLogin($this->drupalCreateUser([
      'administer navigation_block',
      'access administration pages',
      'access navigation',
    ]));
    \Drupal::state()->set('navigation_test.content', $this->randomMachineName());

    // Check that the navigation block title was properly sanitized when
    // rendered.
    $this->drupalGet('');
    $this->assertSession()->responseNotContains('<script>alert("XSS label");</script>');
    $this->assertSession()->assertEscaped('<script>alert("XSS label");</script>');

    // Check that the navigation block title was properly sanitized in
    // Navigation Block Plugin UI Admin page.
    // Hide the navigation bar through permissions to avoid false positives.
    $this->drupalLogin($this->drupalCreateUser([
      'administer navigation_block',
      'access administration pages',
    ]));
    $this->drupalGet(Url::fromRoute('navigation_block.admin_display'));
    $this->assertSession()->responseNotContains('<script>alert("XSS label");</script>');
    $this->assertSession()->assertEscaped('<script>alert("XSS label");</script>');
  }

  /**
   * Tests XSS in category.
   */
  public function testXssInCategory() {
    $this->container->get('module_installer')->install(['navigation_test']);
    $this->drupalPlaceNavigationBlock('test_xss_title');
    $this->drupalLogin($this->drupalCreateUser([
      'administer navigation_block',
      'access administration pages',
    ]));
    $this->drupalGet(Url::fromRoute('navigation_block.admin_display'));
    $this->assertSession()->responseNotContains("<script>alert('XSS category');</script>");
    $this->assertSession()->assertEscaped("<script>alert('XSS category');</script>");
  }

  /**
   * Tests various modules that provide navigation blocks for XSS.
   */
  public function testNavigationBlockXss() {
    $this->drupalLogin($this->drupalCreateUser([
      'administer navigation_block',
      'access administration pages',
    ]));

    $this->doMenuTest();

    $this->drupalGet(Url::fromRoute('navigation_block.admin_display'));
    $this->clickLink('Place navigation block');
    // Check that the page does not have double escaped HTML tags.
    $this->assertSession()->responseNotContains('&amp;lt;');
  }

  /**
   * Tests XSS coming from Menu navigation block labels.
   */
  protected function doMenuTest() {
    Menu::create([
      'id' => $this->randomMachineName(),
      'label' => '<script>alert("menu");</script>',
    ])->save();

    $this->drupalGet(Url::fromRoute('navigation_block.admin_display'));
    $this->clickLink('Place navigation block');

    $this->assertSession()->assertEscaped('<script>alert("menu");</script>');
    $this->assertSession()->responseNotContains('<script>alert("menu");</script>');
  }

}
