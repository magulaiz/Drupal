<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Recipe;

use ColinODell\PsrTestLogger\TestLogger;
use Drupal\Core\Recipe\Recipe;
use Drupal\Core\Recipe\RecipeDiscovery;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\Core\Recipe\RecipeDiscovery
 * @group Recipe
 */
class RecipeDiscoveryTest extends KernelTestBase {

  /**
   * @testWith [true, true]
   *   [true, false]
   *   [false, true]
   */
  public function testFindRecipes(bool $include_test_recipes, bool $include_core_recipes): void {
    $drupal_root = $this->getDrupalRoot();

    $dir = $include_test_recipes
      ? $drupal_root . '/core/tests/fixtures/recipes'
      : NULL;

    // Certain test recipes have validation errors on purpose, but we want to
    // skip those.
    $discovery = new RecipeDiscovery($dir, $include_core_recipes, TRUE);
    $logger = new TestLogger();
    $discovery->setLogger($logger);

    $paths = array_map(
      fn (Recipe $recipe) => $recipe->path,
      iterator_to_array($discovery),
    );

    $test_recipes = preg_grep('/\/core\/tests\/fixtures\/recipes\//', $paths);
    if ($include_test_recipes) {
      $this->assertNotEmpty($test_recipes);

      // An invalid recipe should have been skipped.
      $invalid_recipes = preg_grep('/\/config_rollback_exception$/', $paths);
      $this->assertEmpty($invalid_recipes);
      $this->assertTrue($logger->hasWarningThatContains("Validation errors were found in $drupal_root/core/tests/fixtures/recipes/config_rollback_exception/recipe.yml:"));
    }
    else {
      $this->assertEmpty($test_recipes);
    }

    $core_recipes = preg_grep('/\/core\/recipes\//', $paths);
    if ($include_core_recipes) {
      $this->assertNotEmpty($core_recipes);
    }
    else {
      $this->assertEmpty($include_core_recipes);
    }
  }

}
