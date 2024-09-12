<?php

declare(strict_types=1);

namespace Drupal\Core\Recipe;

use Composer\InstalledVersions;
use Symfony\Component\Finder\Finder;

/**
 * This class facilitates discovering recipes.
 *
 * Discovery means scanning a single directory of your choosing, and core's
 * recipes. It won't discover any recipes anywhere else in the filesystem by
 * design, differing it intentionally from ExtensionDiscovery despite sometimes
 * borrowing from it in name and some method naming conventions.
 */
final class RecipeDiscovery implements \IteratorAggregate {

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
    if ($include_core_recipes) {
      $this->directoriesToSearch[] = \Drupal::root() . '/core/recipes';
    }

    // If there are Composer-installed recipes, we will only find core recipes.
    $path ??= self::getRecipesPathFromComposer();
    if ($path) {
      assert(is_dir($path));
      $this->directoriesToSearch[] = $path;
    }
  }

  /**
   * Gets the path at which Composer has installed recipes.
   *
   * @return string|false
   *   The path where Composer has installed recipes, or FALSE if no recipes
   *   are installed.
   */
  private static function getRecipesPathFromComposer(): string|false {
    $installed_recipes = InstalledVersions::getInstalledPackagesByType(Recipe::COMPOSER_PROJECT_TYPE);
    if ($installed_recipes) {
      $name = reset($installed_recipes);
      $path = InstalledVersions::getInstallPath($name);
      return dirname($path);
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getIterator(): iterable {
    $finder = Finder::create()
      ->files()
      ->name('recipe.yml')
      ->depth(1)
      ->followLinks()
      ->in($this->directoriesToSearch);

    /** @var \Symfony\Component\Finder\SplFileInfo $file */
    foreach ($finder as $file) {
      yield Recipe::createFromDirectory($file->getPath());
    }
  }

}
