<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Recipe;

use ColinODell\PsrTestLogger\TestLogger;
use Drupal\Component\FileSystem\FileSystem;
use Drupal\Core\Recipe\Recipe;
use Drupal\Core\Recipe\RecipeDiscovery;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\Core\Recipe\RecipeDiscovery
 * @group Recipe
 */
class RecipeDiscoveryTest extends KernelTestBase {

  /**
   * The recipe fixture path, including DRUPAL_ROOT.
   *
   * @var string
   */
  private string $recipeFixturePath = DRUPAL_ROOT . '/core/tests/fixtures/recipes';

  /**
   * An array of invalid test recipe paths. IE: Throw exceptions on load.
   *
   * @var string[]
   */
  private array $invalidExpectedRecipePaths = [
    DRUPAL_ROOT . "/core/tests/fixtures/recipes/config_rollback_exception",
  ];

  /**
   * Array of expected test recipe paths. IE: doesn't throw exceptions.
   *
   * @var string[]
   */
  private array $validExpectedRecipePaths = [
    DRUPAL_ROOT . "/core/tests/fixtures/recipes/recipe_depend_on_invalid_config_and_valid_modules",
    DRUPAL_ROOT . "/core/tests/fixtures/recipes/unmet_config_dependencies",
    DRUPAL_ROOT . "/core/tests/fixtures/recipes/config_from_module_and_recipe",
    DRUPAL_ROOT . "/core/tests/fixtures/recipes/no_extensions",
    DRUPAL_ROOT . "/core/tests/fixtures/recipes/config_actions",
    DRUPAL_ROOT . "/core/tests/fixtures/recipes/recipe_depend_on_invalid",
    DRUPAL_ROOT . "/core/tests/fixtures/recipes/config_from_module",
    DRUPAL_ROOT . "/core/tests/fixtures/recipes/install_node_with_config",
    DRUPAL_ROOT . "/core/tests/fixtures/recipes/invalid_config",
    DRUPAL_ROOT . "/core/tests/fixtures/recipes/install_two_modules",
    DRUPAL_ROOT . "/core/tests/fixtures/recipes/recipe_include",
    DRUPAL_ROOT . "/core/tests/fixtures/recipes/config_wildcard",
    DRUPAL_ROOT . "/core/tests/fixtures/recipes/base_theme_and_views",
    DRUPAL_ROOT . "/core/tests/fixtures/recipes/theme_with_module_dependencies",
  ];

  /**
   * Check that core recipe paths are present.
   *
   * @covers ::getAllRecipePaths
   */
  public function testRecipeDiscoveryGetAllRecipePathsCore(): void {
    $recipeDiscovery = new RecipeDiscovery();
    $found_recipe_paths = $recipeDiscovery->getAllRecipePaths();
    $this->assertNotEmpty($found_recipe_paths);
  }

  /**
   * Check that all recipe paths in our custom path are as expected.
   *
   * NOTE: Does not check if recipes are valid.
   *
   * @covers ::getAllRecipePaths
   */
  public function testRecipeDiscoveryGetAllRecipePathsTestData(): void {
    $recipeDiscovery = new RecipeDiscovery(
      $this->recipeFixturePath,
       FALSE
    );
    $found_recipe_paths = $recipeDiscovery->getAllRecipePaths();
    $expected_recipes = array_merge($this->invalidExpectedRecipePaths, $this->validExpectedRecipePaths);

    $this->assertSame(
      ksort($expected_recipes),
      ksort($found_recipe_paths)
    );
  }

  /**
   * Checks that recipe objects are returned from test recipes.
   *
   * NOTE: Checks an invalid recipe is removed.
   * NOTE: Confirms array keys (paths) and that values are Recipe objects.
   *
   * @covers ::getAllRecipePaths
   */
  public function testRecipeDiscoveryGetAllRecipes(): void {
    $recipeDiscovery = new RecipeDiscovery(
      $this->recipeFixturePath,
      FALSE
    );

    $found_recipes = $recipeDiscovery->getAllRecipes();
    $found_recipe_keys = array_keys($found_recipes);
    $this->assertSame(
      ksort($this->validExpectedRecipePaths),
      ksort($found_recipe_keys)
    );

    foreach ($found_recipes as $found_recipe) {
      $this->assertTrue($found_recipe instanceof Recipe);
    }
  }

  /**
   * Tests finding core recipes only.
   */
  public function testFindCoreRecipes(): void {
    $discovered = static::toPathList(new RecipeDiscovery());

    $this->assertNotEmpty($discovered);
    $not_in_core = preg_grep('/\/core\/recipes\//', $discovered, PREG_GREP_INVERT);
    $this->assertEmpty($not_in_core);
  }

  /**
   * Tests skipping over recipes that have validation errors.
   */
  public function testSkipInvalidRecipes(): void {
    $cookbook_dir = uniqid(FileSystem::getOsTemporaryDirectory() . '/');
    mkdir($cookbook_dir);

    mkdir($cookbook_dir . '/a');
    mkdir($cookbook_dir . '/b');
    mkdir($cookbook_dir . '/c');

    file_put_contents($cookbook_dir . '/a/recipe.yml', 'name: A valid recipe');
    // B is not a valid recipe, because it has no name.
    file_put_contents($cookbook_dir . '/b/recipe.yml', 'nom nom nom');
    file_put_contents($cookbook_dir . '/c/recipe.yml', 'name: C valid recipe');

    $discovery = new RecipeDiscovery($cookbook_dir, FALSE, TRUE);
    $logger = new TestLogger();
    $discovery->setLogger($logger);
    $expected_recipes = [
      $cookbook_dir . '/a',
      $cookbook_dir . '/c',
    ];
    $this->assertSame($expected_recipes, static::toPathList($discovery));
    $this->assertTrue($logger->hasWarningThatContains("Validation errors were found in $cookbook_dir/b/recipe.yml:"));
  }

  private static function toPathList(RecipeDiscovery $discovery): array {
    return array_map(
      fn (Recipe $recipe) => $recipe->path,
      iterator_to_array($discovery),
    );
  }

}
