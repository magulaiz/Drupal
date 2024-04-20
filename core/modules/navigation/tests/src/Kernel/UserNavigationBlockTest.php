<?php

declare(strict_types=1);

namespace Drupal\Tests\navigation\Kernel;

use Drupal\block\Entity\Block;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\navigation\NavigationBlockRepositoryInterface;
use Drupal\user\Entity\User;

/**
 * Tests \Drupal\block\Plugin\NavigationBlock\UserNavigationBlock.
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
   * The block manager service.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected BlockManagerInterface $blockManager;

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

    $this->blockManager = $this->container->get('plugin.manager.block');
  }

  /**
   * Tests calculation of a user navigation block's config dependencies.
   */
  public function testUserNavigationBlockConfigDependencies() {
    $block = Block::create([
      'plugin' => 'navigation_user',
      'region' => NavigationBlockRepositoryInterface::REGION_FOOTER,
      'id' => 'machine_name',
    ]);
    $dependencies = $block->calculateDependencies()->getDependencies();
    $expected = [];
    $this->assertSame($expected, $dependencies);
  }

}
