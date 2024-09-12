<?php

declare(strict_types=1);

namespace Drupal\Core\Recipe;

use Drupal\Component\Serialization\Json;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Finder\Finder;

/**
 * This class facilitates discovering recipes.
 *
 * Discovery means scanning a single directory of your choosing, and core's
 * recipes. It won't discover any recipes anywhere else in the filesystem by
 * design, differing it intentionally from ExtensionDiscovery despite sometimes
 * borrowing from it in name and some method naming conventions.
 */
final class RecipeDiscovery {
  use LoggerAwareTrait;
  use StringTranslationTrait;

  /**
   * Array of directories we will search.
   */
  private const string CORE_RECIPE_DIR = 'core/recipes';

  /**
   * The directories to search.
   *
   * @var array|string[]
   */
  protected array $directoriesToSearch = [];

  /**
   * Constructs a recipe discovery object.
   *
   * @param string|null $path
   *   (OPTIONAL) path should be a folder containing directories that contain a
   *   recipe.yml file There will be no traversal further into the directory
   *   structure. You will want to pass an absolute value where recipes are
   *   expected to be installed by Composer; for example
   *   \Drupal::root() . . '/recipes'.
   * @param bool $include_core_recipes
   *   (optional) Whether or not to include core recipes. If you're requesting
   *   a specific path, you may not want to include core recipes.
   */
  public function __construct(?string $path = NULL, bool $include_core_recipes = TRUE) {
    $path ??= self::getComposerRecipePath();

    if ($include_core_recipes) {
      $this->directoriesToSearch[] = \Drupal::root() . '/' . self::CORE_RECIPE_DIR;
    }

    // In the absence of a path (FALSE) the user will just get core recipes.
    if ($path !== FALSE) {
      if (is_dir($path)) {
        $this->directoriesToSearch[] = $path;
      }
    }

  }

  /**
   * Gets the path at which Composer has installed recipes.
   *
   * @return string|bool
   *   Path to Composer recipes, FALSE if no composer path.
   */
  private static function getComposerRecipePath(): string|bool {
    if (file_exists(\Drupal::root() . '/composer.json')) {
      $composer_values = Json::decode(file_get_contents(\Drupal::root() . '/composer.json'));
      $installer_types_and_paths = $composer_values['extra']['installer-paths'];

      foreach ($installer_types_and_paths as $path => $installer_type) {
        if (reset($installer_type) == Recipe::COMPOSER_PROJECT_TYPE) {
          $recipe_path = explode('/', $path);
          array_pop($recipe_path);
          $recipe_path = implode('/', $recipe_path);
          return DRUPAL_ROOT . '/' . $path;
        }
      }
    }

    return FALSE;
  }

  /**
   * Scans the site for recipes in the specified search directories.
   *
   * @return array
   *   An array of recipe paths, as discovered in the provided paths.
   */
  public function getAllRecipePaths(): array {
    $recipes = [];

    foreach ($this->directoriesToSearch as $search_dir) {
      $recipes = array_merge($recipes, $this->scanDirectory($search_dir));
    }

    return $recipes;
  }

  /**
   * Scans the site for recipes and returns an array of loaded Recipe objects.
   *
   * WARNING: Recipes can be sizable so be careful using this method!
   *
   * @return Recipe[]
   *   An array of recipe objects.
   */
  public function getAllRecipes() : array {
    $valid_recipes = [];

    foreach ($this->getAllRecipePaths() as $recipePath) {
      try {
        $valid_recipes[$recipePath] = Recipe::createFromDirectory($recipePath);
      }
      catch (RecipeFileException $e) {
        // Invalid recipes aren't really our problem, but are worth logging.
        $this->logger?->debug(
          $this->t("Attempted to load recipe at path '@path', but got a RecipeFileException of: @message",
            ['@path' => $recipePath, '@message' => $e->getMessage()]
        ));
      }
    }

    return $valid_recipes;
  }

  /**
   * Scans a directory and returns all found recipes by path.
   *
   * @param string $directory
   *   The directory to search.
   *
   * @return array
   *   All recipes found, by path.
   */
  protected function scanDirectory(string $directory) : array {
    $recipes = [];

    $finder = Finder::create()
      ->files()
      ->name('recipe.yml')
      ->depth(1)
      ->followLinks()
      ->in($directory);

    foreach ($finder as $file) {
      $recipes[] = $file->getPath();
    }

    return $recipes;
  }

}
