<?php

namespace Drupal\Core\Command;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ExtensionDiscovery;
use Drupal\Core\Extension\InfoParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function Symfony\Component\String\u;

/**
 * Generates a new theme based on latest default markup.
 */
class GenerateTheme extends Command {

  /**
   * The path for the Drupal root.
   *
   * @var string
   */
  private $root;

  /**
   * The Symfony output decorator.
   *
   * @var SymfonyStyle
   */
  private $io;

  /**
   * The temporary directory files are stored during operations.
   *
   * @var [type]
   */
  private $tmp_dir;

  /**
   * The machine name of the source theme
   *
   * @var String
   */
  private $source_theme_name;

  /**
   * The theme to be duplicated.
   *
   * @var Extension
   */
  private $source_theme;

  /**
   * Array of filepaths, directories, or globs relative to the theme root.
   * Matching files/dirs will be removed from $this->temp_dir before other operations.
   *
   * @var String[]
   */
  private $paths_to_delete;

  /**
   * Array of filepaths, directories, or globs relative to the theme root.
   * Matching files/dirs will be removed from $this->temp_dir before other operations.
   *
   * @var String[]
   */
  private $paths_to_skip_edit;

  /**
   * Array of filepaths, directories, or globs relative to the theme root.
   * Matching files/dirs will be removed from $this->temp_dir before other operations.
   *
   * @var String[]
   */
  private $paths_to_skip_rename;

  /**
   * Key-value pairs that will be set in the new theme's *.info.yml file.
   *
   * @var []
   */
  private $info_overrides;

  /**
   * The human-readable name of the destination theme.
   *
   * @var String
   */
  private $destination_theme_label;

  /**
   * The description of the destination theme.
   *
   * @var String
   */
  private $destination_theme_description;

  /**
   * {@inheritdoc}
   */
  public function __construct(string $name = NULL) {
    parent::__construct($name);

    $this->root = dirname(__DIR__, 5);
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('generate-theme')
      ->setDescription('Generates a new theme based on latest default markup.')
      ->addArgument('machine-name', InputArgument::REQUIRED, 'The machine name of the generated theme')
      ->addOption('name', NULL, InputOption::VALUE_OPTIONAL, 'A name for the theme.')
      ->addOption('description', NULL, InputOption::VALUE_OPTIONAL, 'A description of your theme.')
      ->addOption('path', NULL, InputOption::VALUE_OPTIONAL, 'The path where your theme will be created. Defaults to: themes', 'themes')
      ->addOption('starterkit', NULL, InputOption::VALUE_OPTIONAL, 'The theme to use as the starterkit', 'starterkit_theme')
      ->addUsage('custom_theme --name "Custom Theme" --description "Custom theme generated from a starterkit theme" --path themes')
      ->addUsage('custom_theme --name "Custom Theme" --starterkit mystarterkit');
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    // Change the directory to the Drupal root.
    chdir($this->root);

    $this->io = new SymfonyStyle($input, $output);

    // Get all command args & options.
    $destination_theme = $input->getArgument('machine-name');
    $destination = trim($input->getOption('path'), '/') . '/' . $destination_theme;
    $this->source_theme_name = $input->getOption('starterkit');
    $this->destination_theme_label = $input->getOption('name') ?: $destination_theme;
    $this->destination_theme_description = $input->getOption('description');

    // Ensure source/destination themes are valid
    if (!$this->checkValidCommand($destination, $this->source_theme_name)) {
      return 1;
    }

    // Get more specific source theme details now that it's safe.
    $this->source_theme = $this->getThemeInfo($this->source_theme_name);
    $source_path = $this->source_theme->getPath();

    // Copy entire contents of source theme to tmp_dir.
    $this->tmp_dir = $this->getUniqueTmpDirPath();
    $filesystem = new Filesystem();
    $filesystem->mirror($source_path, $this->tmp_dir);

    // Load info from THEMENAME.starterkit.yml if it exists.
    $this->getStarterKitConfig();

    // Remove files marked for deletion.
    $this->removeDeletableFiles();

    // Alter THEMENAME.info.yml for new theme.
    $this->overrideThemeInfo();

    /**
     * We replace theme names with tokens that do not overlap with
     * source or destination theme names. This prevents issues where
     * similar source & destination theme names end up re-running
     * on the same file name/content items.
     */

    // Replace theme name usage in filenames.
    $this->prepareForRename();

    // Replace theme name usage in file contents.
    $this->prepareForContentEdit();

    // Replace temporary placeholder tokens with final strings.
    $this->doRenameAndEdit();

    // @todo This is specific to the starterkit_theme. We need to find a way to generalize this.
    // Readme is specific to Starterkit, so remove it from the generated theme.
    $readme_file = "$this->tmp_dir/README.md";
    if (!file_put_contents($readme_file, "$destination_theme theme, generated from $this->source_theme_name. Additional information on generating themes can be found in the [Starterkit documentation](https://www.drupal.org/docs/core-modules-and-themes/core-themes/starterkit-theme).")) {
      $this->io->getErrorStyle()->error("The readme could not be rewritten.");
      return 1;
    }
  }

  /**
   * Performs various checks to ensure command failures happen more gracefully.
   *
   * @param String $destination_theme
   * @param String $destination
   * @param String $source_theme_name
   * @return Boolean
   */
  private function checkValidCommand($destination, $source_theme_name) {
    $io = $this->io;

    if (is_dir($destination)) {
      $io->getErrorStyle()->error("Theme could not be generated because the destination directory $destination exists already.");
      return false;
    }

    if (!$source_theme = $this->getThemeInfo($source_theme_name)) {
      $io->getErrorStyle()->error("Theme source theme $source_theme_name cannot be found.");
      return false;
    }

    if (!$this->isStarterkitTheme($source_theme)) {
      $io->getErrorStyle()->error("Theme source theme $source_theme_name is not a valid starter kit.");
      return false;
    }
  }

  /**
   * Reads THEMENAME.starterkit.yml
   *
   * @return void
   */
  private function getStarterKitConfig() {
    $source_path = $this->source_theme->getPath();
    $themename = $this->source_theme_name;

    if ($config_file = file_get_contents($source_path . '/' . $themename . 'starterkit.yml')) {
      $config = Yaml::decode($config_file);

      if (isset($config['delete']) && is_array($config['delete'])) {
        $this->paths_to_delete = $config['delete'];
      }

      if (isset($config['no_edit']) && is_array($config['no_edit'])) {
        $paths = [];
        foreach ($config['no_edit'] as $glob) {
          $finder = new Finder();
          $files = $finder->in($this->tmp_dir)->files()->name($glob);
          $paths = array_merge($paths, array_map(fn ($file) => $file->getRelativePathname(), iterator_to_array($files)));
        }
        $this->paths_to_skip_edit = $paths;
      }

      if (isset($config['no_rename']) && is_array($config['no_rename'])) {
        $paths = [];
        foreach ($config['no_rename'] as $glob) {
          $finder = new Finder();
          $files = $finder->in($this->tmp_dir)->files()->name($glob);
          $paths = array_merge($paths, array_map(fn ($file) => $file->getRelativePathname(), iterator_to_array($files)));
        }
        $this->paths_to_skip_rename = $paths;
      }

      if (isset($config['info']) && is_array($config['info'])) {
        $this->info_overrides = $config['info'];
      }
    } else {
      // @todo: set defaults
    }
  }

  /**
   * Removes files marked for deletion from $this->tmp_dir.
   *
   * @return void
   */
  private function removeDeletableFiles() {
    $paths = $this->paths_to_delete;
    if (isset($paths) && is_array($paths) && !empty($paths)) {
      $finder = new Finder();
      $filesystem = new Filesystem();
      foreach ($paths as $path) {
        if (is_string($path)) {
          $files = $finder->in($this->tmp_dir)->name($path);
          $filesystem->remove($files);
        }
      }
    }
  }

  /**
   * Overrides source *.info.yml with key/value pairs specified in *.starterkit.yml.
   *
   * @return void
   */
  private function overrideThemeInfo() {
    $info_overrides = $this->info_overrides;
    if (isset($info_overrides) && is_array($info_overrides) && !empty($info_overrides)) {
      $theme = $this->source_theme_name;
      $tmp_dir = $this->tmp_dir;
      $source_info_file = "$tmp_dir/$theme.info.yml";

      if ($source_info_contents = file_get_contents($source_info_file)) {
        $source_info = Yaml::decode($source_info_contents);

        foreach ($info_overrides as $key => $value) {
          if ($value === NULL) {
            unset($source_info[$key]);
          } else {
            $source_info[$key] = $value;
          }
        }

        $source_info_contents = Yaml::encode($source_info);
        file_put_contents($source_info_file, $source_info_contents);
      }
    }
  }

  private function prepareForRename() {
    $machine_name = $this->source_theme_name;
    $class_name = u($machine_name)->camel()->title();
    $label = $this->source_theme->info['name'];

    $finder = new Finder();
    $files = $finder
      ->in($this->tmp_dir)
      ->files()
      ->name($machine_name)
      ->filter(fn ($file) => !in_array($file->getRelativePathname(), $this->paths_to_skip_rename));

    foreach ($files as $file) {
      // @todo Replace source string with token
    }
  }

  private function prepareForContentEdit() {
    $machine_name = $this->source_theme_name;
    $class_name = u($machine_name)->camel()->title();
    $label = $this->source_theme->info['name'];

    $finder = new Finder();
    $files = $finder
      ->in($this->tmp_dir)
      ->files()
      ->contains($machine_name)
      ->filter(fn ($file) => !in_array($file->getRelativePathname(), $this->paths_to_skip_edit));

    foreach ($files as $file) {
      // @todo Replace source string with token
    }
  }

  private function doRenameAndEdit() {
    // @todo Replace token with destination string
  }

  /**
   * Generates a path to a temporary location.
   *
   * @return string
   */
  private function getUniqueTmpDirPath(): string {
    return sys_get_temp_dir() . '/drupal-starterkit-theme-' . uniqid(md5(microtime()), TRUE);
  }

  /**
   * Gets theme info using the theme name.
   *
   * @param string $theme
   *   The machine name of the theme.
   *
   * @return \Drupal\Core\Extension\Extension|null
   */
  private function getThemeInfo(string $theme): ? Extension {
    $extension_discovery = new ExtensionDiscovery($this->root, FALSE, []);
    $themes = $extension_discovery->scan('theme');

    if (!isset($themes[$theme])) {
      return NULL;
    }

    return $themes[$theme];
  }

  /**
   * Checks if the theme is a starterkit theme.
   *
   * @param \Drupal\Core\Extension\Extension $theme
   *   The theme extension.
   *
   * @return bool
   */
  private function isStarterkitTheme(Extension $theme): bool {
    $info_parser = new InfoParser($this->root);
    $info = $info_parser->parse($theme->getPathname());

    return $info['starterkit'] ?? FALSE === TRUE;
  }
}
