<?php

declare(strict_types=1);

namespace Drupal\Tests\block\Kernel;

use Drupal\block\Entity\Block;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ThemeInstallerInterface;
use Drupal\Core\Recipe\RecipeRunner;
use Drupal\FunctionalTests\Core\Recipe\RecipeTestTrait;
use Drupal\KernelTests\KernelTestBase;

/**
 * @covers \Drupal\block\Plugin\ConfigAction\PlaceBlock
 * @covers \Drupal\block\Plugin\ConfigAction\PlaceBlockDeriver
 * @group block
 */
class ConfigActionsTest extends KernelTestBase {

  use RecipeTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['block', 'user', 'system'];

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

    // Delete the "powered by" blocks, which we'll restore in our tests.
    $storage = $this->container->get(EntityTypeManagerInterface::class)
      ->getStorage('block');
    $blocks = $storage->loadByProperties(['plugin' => 'system_powered_by_block']);
    $storage->delete($blocks);
  }

  /**
   * @testWith ["placeBlockInDefaultTheme"]
   *   ["placeBlockInAdminTheme"]
   */
  public function testActionOnlyWorksOnBlocks(string $action): void {
    $recipe = $this->createRecipe(<<<YAML
name: Targeting the wrong entity type
config:
  actions:
    user.role.anonymous:
      $action: {}
YAML
    );
    $this->expectException(PluginNotFoundException::class);
    $this->expectExceptionMessage("The \"$action\" plugin does not exist.");
    RecipeRunner::processRecipe($recipe);
  }

  /**
   * @testWith ["placeBlockInDefaultTheme", "olivero"]
   *   ["placeBlockInAdminTheme", "claro"]
   */
  public function testPlaceBlockInTheme(string $action, string $expected_theme): void {
    $recipe = $this->createRecipe(<<<YAML
name: Placing a block
config:
  actions:
    block.block.test_block:
      $action:
        plugin: system_powered_by_block
        region: header
YAML
    );
    RecipeRunner::processRecipe($recipe);

    $block = Block::load('test_block');
    $this->assertInstanceOf(Block::class, $block);
    $this->assertSame('system_powered_by_block', $block->getPluginId());
    $this->assertSame($expected_theme, $block->getTheme());
  }

}
