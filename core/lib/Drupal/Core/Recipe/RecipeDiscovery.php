<?php

declare(strict_types=1);

namespace Drupal\Core\Recipe;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;

/**
 * This class facilitates discovering recipes.
 *
 * Borrows method naming convention from:
 * Drupal\Core\Extension\ExtensionDiscovery.
 */
final class RecipeDiscovery {
  use LoggerChannelTrait;
  use StringTranslationTrait;

  /**
   * Array of directories we will search.
   */
  const string CORE_RECIPE_DIR = 'core/recipes';

  /**
   * The directories to search.
   *
   * @var array|string[]
   */
  protected array $directoriesToSearch = [];

  /**
   * Logger for storing messages.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected LoggerInterface $logger;

  /**
   * Constructs a recipe discovery object.
   *
   * @param string|null $path
   *   (OPTIONAL) path should be a folder containing directories that contain a
   *   recipe.yml file There will be no traversal further into the directory
   *   structure. Core recipe will be added. Ideally you'll pass a value,
   *   relative to Drupal core, where all recipes are expected to be installed
   *   by Composer; for example DRUPAL_ROOT . '/recipes'.
   * @param bool $include_core_recipes
   *   (optional) Whether or not to include core recipes. If you're requesting
   *   a specific path, you may not want to include core recipes.
   */
  public function __construct(?string $path = NULL, bool $include_core_recipes = TRUE) {
    $path ??= self::getComposerRecipePath();

    if ($include_core_recipes) {
      $this->directoriesToSearch[] = DRUPAL_ROOT . '/' . self::CORE_RECIPE_DIR;
    }

    if ($path !== FALSE) {
      if (is_dir($path)) {
        $this->directoriesToSearch[] = $path;
      }
      else {
        throw new DirectoryNotFoundException();
      }
    }

    $this->logger = $this->getLogger('recipe');

  }

  /**
   * Gets the path at which composer has installed recipes.
   *
   * This will work when Drupal core composer has an install-paths
   * value of type:drupal-recipe.
   *
   * @todo Can composer recipe path come from InstalledVersions or similar?
   *
   * @return string|bool
   *   Path to composer recipes, FALSE if no composer path.
   */
  private static function getComposerRecipePath(): string|bool {
    $composer_values = Json::decode(file_get_contents(DRUPAL_ROOT . '/composer.json'));
    $installer_types_and_paths = $composer_values['extra']['installer-paths'];

    foreach ($installer_types_and_paths as $path => $installer_type) {
      if (reset($installer_type) == 'type:drupal-recipe') {
        $recipe_path = explode('/', $path);
        array_pop($recipe_path);
        $recipe_path = implode('/', $recipe_path);
        return DRUPAL_ROOT . '/' . $path;
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
        $this->logger->warning(
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
      ->in($directory);

    foreach ($finder as $file) {
      $recipes[] = $file->getPath();
    }

    return $recipes;
  }

}
