<?php

declare(strict_types=1);

namespace Drupal\Tests\navigation\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\navigation\Entity\NavigationBlock;
use Drupal\navigation\NavigationBlockRepositoryInterface;
use Drupal\navigation\Plugin\NavigationBlock\UserNavigationBlock;
use Drupal\user\Entity\User;

/**
 * Tests \Drupal\navigation\Plugin\NavigationBlock\UserNavigationBlock.
 *
 * @group navigation
 */
class UserNavigationBlockTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'system',
    'user',
    'navigation',
  ];

  /**
   * The navigation block manager service.
   *
   * @var \Drupal\navigation\NavigationBlockManagerInterface
   */
  protected $navigationBlockManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');

    $account = User::create([
      'name' => $this->randomMachineName(),
      'status' => 1,
    ]);
    $account->save();
    $this->container->get('current_user')->setAccount($account);

    $this->navigationBlockManager = $this->container->get('plugin.manager.navigation_block');
  }

  /**
   * Tests calculation of a user navigation block's config dependencies.
   */
  public function testUserNavigationBlockConfigDependencies() {
    $navigation_block = NavigationBlock::create([
      'plugin' => 'user',
      'region' => NavigationBlockRepositoryInterface::REGION_FOOTER,
      'id' => 'machine_name',
    ]);
    $dependencies = $navigation_block->calculateDependencies()->getDependencies();
    $expected = [];
    $this->assertSame($expected, $dependencies);
  }

  /**
   * Tests the build method.
   */
  public function testBuild() {
    /** @var \Drupal\navigation\Plugin\NavigationBlock\UserNavigationBlock $navigation_block */
    $navigation_block = $this->navigationBlockManager->createInstance('user', [
      'region' => NavigationBlockRepositoryInterface::REGION_FOOTER,
      'id' => 'machine_name',
    ]);
    $this->assertInstanceOf(UserNavigationBlock::class, $navigation_block);
    $build = $navigation_block->build();
    $this->assertFalse($build['#lazy_builder_preview']['#help']);
    $this->assertSame('menu_region__footer', $build['#lazy_builder_preview']['#theme']);
    $this->assertCount(2, $build['#lazy_builder_preview']['#items']);
    $this->assertSame('user', $build['#lazy_builder_preview']['#menu_name']);
    $this->assertSame('My Account', (string) $build['#lazy_builder_preview']['#title']);
  }

}
