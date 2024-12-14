<?php

declare(strict_types=1);

namespace Drupal\FunctionalTests\Menu;

use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\system\Functional\Menu\AssertMenuActiveTrailTrait;

/**
 * Tests that <front> links are marked as being in the active trail.
 *
 * @group menu
 */
class MenuActiveTrailFrontTest extends BrowserTestBase {

  use AssertMenuActiveTrailTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'olivero';

  /**
   * The menu which active trail is tested.
   *
   * @var string
   */
  protected $menu = 'footer';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system', 'block', 'menu_link_content'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalPlaceBlock(
      'system_menu_block:' . $this->menu,
      [
        'level' => 1,
      ]
    );

    $menu_link_content = MenuLinkContent::create([
      'title' => 'Front link',
      'menu_name' => 'footer',
      'link' => ['uri' => 'route:<front>'],
    ]);
    $menu_link_content->save();
  }

  /**
   * Tests that <front> links are marked as being in the active trail.
   */
  public function testMenuActiveTrailFront(): void {
    $this->drupalGet('');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertMenuActiveTrail(['/' => 'Front link'], TRUE, 'menu__item--active-trail');
  }

}
