<?php

declare(strict_types=1);

namespace Drupal\Tests\block\Kernel;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
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
   * @testWith ["placeBlockInDefaultTheme"]
   *   ["placeBlockInAdminTheme"]
   */
  public function testActionOnlyWorksOnBlocks(string $action): void {
    $this->enableModules(['user']);

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

  public function testPlaceBlockInDefaultTheme(): void {
  }

  public function testPlaceBlockInAdminTheme(): void {
  }

}
