<?php

declare(strict_types=1);

namespace Drupal\Tests\block\Kernel;

use Drupal\block\Entity\Block;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Config\Action\ConfigActionException;
use Drupal\Core\Config\Action\ConfigActionManager;
use Drupal\Core\Extension\ThemeInstallerInterface;
use Drupal\KernelTests\KernelTestBase;

/**
 * @covers \Drupal\block\Plugin\ConfigAction\PlaceBlock
 * @covers \Drupal\block\Plugin\ConfigAction\PlaceBlockDeriver
 * @group block
 */
class ConfigActionsTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['block', 'user', 'system'];

  private readonly ConfigActionManager $configActionManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->container->get(ThemeInstallerInterface::class)->install([
      'olivero',
      'claro',
    ]);
    $this->config('system.theme')
      ->set('default', 'olivero')
      ->set('admin', 'claro')
      ->save();

    $this->configActionManager = $this->container->get('plugin.manager.config_action');
  }

  /**
   * @testWith ["placeBlockInDefaultTheme"]
   *   ["placeBlockInAdminTheme"]
   */
  public function testActionOnlyWorksOnBlocks(string $action): void {
    $this->expectException(PluginNotFoundException::class);
    $this->expectExceptionMessage("The \"$action\" plugin does not exist.");
    $this->configActionManager->applyAction($action, 'user.role.anonymous', []);
  }

  public function testBlockCannotAlreadyExist(): void {
    $this->expectException(ConfigActionException::class);
    $this->expectExceptionMessage('Entity block.block.olivero_powered exists');
    $this->configActionManager->applyAction('placeBlockInDefaultTheme', 'block.block.olivero_powered', []);
  }

  /**
   * @testWith ["placeBlockInDefaultTheme", "olivero"]
   *   ["placeBlockInAdminTheme", "claro"]
   */
  public function testPlaceBlockInTheme(string $action, string $expected_theme): void {
    $this->configActionManager->applyAction($action, 'block.block.test_block', [
      'plugin' => 'system_powered_by_block',
      'region' => 'header',
    ]);

    $block = Block::load('test_block');
    $this->assertInstanceOf(Block::class, $block);
    $this->assertSame('system_powered_by_block', $block->getPluginId());
    $this->assertSame($expected_theme, $block->getTheme());
  }

}
