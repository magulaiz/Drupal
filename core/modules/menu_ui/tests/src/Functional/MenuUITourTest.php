<?php

namespace Drupal\Tests\menu_ui\Functional;

use Drupal\Tests\tour\Functional\TourTestBase;

/**
 * Tests the Menu UI tour.
 *
 * @group menu_ui
 */
class MenuUITourTest extends TourTestBase {

  /**
   * An admin user with administrative permissions for Menu.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['block', 'menu_ui', 'tour'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->adminUser = $this->drupalCreateUser(['administer menu', 'access tour']);
    $this->drupalLogin($this->adminUser);
    $this->drupalPlaceBlock('local_actions_block');
  }

  /**
   * Tests menu ui tour tip availability.
   */
  public function testMenuUiTourTips() {
    $this->drupalGet('admin/structure/menu');
    $this->assertTourTips();
  }

}
