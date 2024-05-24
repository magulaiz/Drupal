<?php

declare(strict_types=1);

namespace Drupal\Tests\navigation\Kernel;

use Drupal\block\Entity\Block;
use Drupal\KernelTests\KernelTestBase;
use Drupal\system\Entity\Menu;

/**
 * Tests \Drupal\navigation\Plugin\Block\SystemMenuNavigationBlock.
 *
 * @group navigation
 * @see \Drupal\navigation\Plugin\Derivative\SystemMenuNavigationBlock
 * @see \Drupal\navigation\Plugin\Block\NavigationMenuBlock
 * @todo Expand test coverage to all SystemMenuNavigationBlock functionality,
 * including block_menu_delete().
 */
class SystemMenuNavigationBlockTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'system',
    'navigation',
    'block',
    'layout_builder',
  ];

  /**
   * The navigation block under test.
   *
   * @var \Drupal\navigation\Plugin\Block\NavigationMenuBlock
   */
  protected $navigationBlock;

  /**
   * The menu for testing.
   *
   * @var \Drupal\system\MenuInterface
   */
  protected $menu;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Add a new custom menu.
    $menu_name = 'mock';
    $label = $this->randomMachineName(16);

    $this->menu = Menu::create([
      'id' => $menu_name,
      'label' => $label,
      'description' => 'Description text',
    ]);
    $this->menu->save();

  }

  /**
   * Tests calculation of a system navigation menu block's config dependencies.
   */
  public function testSystemMenuBlockConfigDependencies() {
    $block = Block::create([
      'plugin' => 'navigation_menu:' . $this->menu->id(),
      'region' => 'content',
      'id' => 'machine_name',
      'theme' => 'stark',
    ]);

    $dependencies = $block->calculateDependencies()->getDependencies();
    $expected = [
      'config' => [
        'system.menu.' . $this->menu->id(),
      ],
      'module' => [
        'navigation',
        'system',
      ],
      'theme' => [
        'stark',
      ],
    ];
    $this->assertSame($expected, $dependencies);
  }

}
