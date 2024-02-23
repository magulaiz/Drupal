<?php

namespace Drupal\Core\Command;

use Composer\Autoload\ClassLoader;
use Composer\Semver\VersionParser;
use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ExtensionDiscovery;
use Drupal\Core\Extension\InfoParser;
use Drupal\Core\Theme\StarterKitInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
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
   * @var Symfony\Component\Console\Style\SymfonyStyle
   */
  private $io;

  /**
   * The temporary directory files are stored during operations.
   *
   * @var [type]
   */
  private $tmp_dir;

  /**
   * The machine name of the source theme.
   *
   * @var String
   */
  private $source_theme_name;

  /**
   * The theme to be duplicated.
   *
   * @var Drupal\Core\Extension\Extension
   */
  private $source_theme;

  private $source_theme_info;

  /**
   * Paths to delete.
   *
   * Array of filepaths, directories, or globs relative to the theme root.
   * Matching files/dirs will be removed from $this->temp_dir before other operations.
   *
   * @var String[]
   */
  private $paths_to_delete = [
    '/src/StarterKit.php',
    '/*.starterkit.yml',
  ];

  /**
   * Paths to skip editing.
   *
   * Array of filepaths, directories, or globs relative to the theme root.
   * Matching files/dirs will not have their contents edited.
   *
   * @var String[]
   */
  private $paths_to_skip_edit = [];

  /**
   * Paths to skip renaming.
   *
   * Array of filepaths, directories, or globs relative to the theme root.
   * Matching files/dirs will not be renamed.
   *
   * @var String[]
   */
  private $paths_to_skip_rename = [];

  /**
   * Key-value pairs that will be set in the new theme's *.info.yml file.
   *
   * @var []
   */
  private $info_overrides = [
    'hidden' => NULL,
    'starterkit' => NULL,
    'version' => '1.0.0',
  ];

  /**
   * The machine name of the destination theme.
   *
   * @var String
   */
  private $destination_theme;

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
   * Storage of find-and-replace strings.
   *
   *  - old patterns point to the source theme
   *  - new patterns point to the destination theme
   *  - token patterns are strings that do not contain and are not contained by either old or new patterns
   *
   * @var array
   */
  private $find_and_replace_patterns = [
    'old' => [],
    'new' => [],
    'token' => [],
  ];

  /**
   * {@inheritdoc}
   */
  public function __construct(string $name = NULL, ?string $root = NULL) {
    parent::__construct($name);

    $this->root = $root ?? dirname(__DIR__, 5);
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
    $this->destination_theme = $destination_theme;

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

    // Load info from THEMENAME.starterkit.yml if it exists.
    $this->getStarterKitConfig();

    $mirror_iterator = new Finder();
    $mirror_iterator
      ->in($source_path)
      ->notPath(array_map(
        static fn ($path) => trim($path, '/'),
        $this->paths_to_delete
      ));

    // Copy entire contents of source theme to tmp_dir.
    $this->tmp_dir = $this->getUniqueTmpDirPath();
    $filesystem = new Filesystem();
    $filesystem->mirror($source_path, $this->tmp_dir, $mirror_iterator);

    // Get all the source/dest/token strings needed for renaming & editing.
    $this->prepareForRenameAndEdit();

    // Replace temporary placeholder tokens with final strings.
    $this->doRenameAndEdit();

    // Alter THEMENAME.info.yml for new theme.
    if (!is_null($exit_code = $this->overrideThemeInfo())) {
      return $exit_code;
    }

    // Let source theme define additional tasks.
    if (!$this->doPostProcess()) {
      return 1;
    }

    // Move altered theme to final destination.
    $filesystem->mirror($this->tmp_dir, $destination);

    $output->writeln(sprintf('Theme generated successfully to %s', $destination));

    return 0;
  }

  /**
   * Performs various checks to ensure command failures happen more gracefully.
   *
   * @param string $destination
   *   Path of the destination theme.
   * @param string $source_theme_name
   *   Path of the source theme.
   *
   * @return bool
   */
  private function checkValidCommand($destination, $source_theme_name) {
    $io = $this->io;

    if (is_dir($destination)) {
      $io->getErrorStyle()->error("Theme could not be generated because the destination directory $destination exists already.");
      return FALSE;
    }

    if (!$source_theme = $this->getThemeInfo($source_theme_name)) {
      $io->getErrorStyle()->error("Theme source theme $source_theme_name cannot be found.");
      return FALSE;
    }

    if (!$this->isStarterkitTheme($source_theme)) {
      $io->getErrorStyle()->error("Theme source theme $source_theme_name is not a valid starter kit.");
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Reads THEMENAME.starterkit.yml.
   *
   * @return void
   */
  private function getStarterKitConfig() {
    $source_path = $this->source_theme->getPath();
    $themename = $this->source_theme_name;
    $config_file = $source_path . '/' . $themename . '.starterkit.yml';

    if (is_file($config_file) && $config_file = file_get_contents($config_file)) {
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
    }
  }

  /**
   * Overrides source *.info.yml with key/value pairs specified in *.starterkit.yml.
   *
   * @return int|NULL returns an exit code or NULL to continue.
   */
  private function overrideThemeInfo() {
    $info_overrides = $this->info_overrides;
    $source_theme_name = $this->source_theme_name;
    $theme = $this->destination_theme;
    $tmp_dir = $this->tmp_dir;
    $info_file = "$tmp_dir/$theme.info.yml";

    if ($info_contents = file_get_contents($info_file)) {
      $info = Yaml::decode($info_contents);
      $this->source_theme_info = $info;

      if (!array_key_exists('version', $info)) {
        $confirm_versionless_source_theme = new ConfirmationQuestion(sprintf('The source theme %s does not have a version specified. This makes tracking changes in the source theme difficult. Are you sure you want to continue?', $source_theme_name));
        if (!$this->io->askQuestion($confirm_versionless_source_theme)) {
          return 0;
        }
      }

      $source_version = $info['version'] ?? 'unknown-version';
      if ($source_version === 'VERSION') {
        $source_version = \Drupal::VERSION;
      }

      // A version in the generator string like "9.4.0-dev" is not very helpful.
      // When this occurs, generate a version string that points to a commit.
      if (VersionParser::parseStability($source_version) === 'dev') {
        $git_check = Process::fromShellCommandline('git --help');
        $git_check->run();
        if ($git_check->getExitCode()) {
          $this->io->error(sprintf('The source theme %s has a development version number (%s). Determining a specific commit is not possible because git is not installed. Either install git or use a tagged release to generate a theme.', $this->source_theme->getName(), $source_version));
          return 1;
        }

        // Get the git commit for the source theme.
        $source_path = $this->source_theme->getPath();
        $git_get_commit = Process::fromShellCommandline("git rev-list --max-count=1 --abbrev-commit HEAD -C $source_path");
        $git_get_commit->run();
        if ($git_get_commit->getOutput() === '') {
          $confirm_packaged_dev_release = new ConfirmationQuestion(sprintf('The source theme %s has a development version number (%s). Because it is not a git checkout, a specific commit could not be identified. This makes tracking changes in the source theme difficult. Are you sure you want to continue?', $this->source_theme->getName(), $source_version));
          if (!$this->io->askQuestion($confirm_packaged_dev_release)) {
            return 0;
          }
          $source_version .= '#unknown-commit';
        }
        else {
          $source_version .= '#' . trim($git_get_commit->getOutput());
        }
      }

      // Create the generator string before doing *.info.yml overrides.
      $generator_string = "$this->source_theme_name:$source_version";

      if (isset($info_overrides) && is_array($info_overrides) && !empty($info_overrides)) {
        foreach ($info_overrides as $key => $value) {
          if ($value === NULL) {
            unset($info[$key]);
          }
          else {
            $info[$key] = $value;
          }
        }
      }

      // Insert generator string, theme label, and description from command after overrides.
      $info['generator'] = $generator_string;

      if ($this->destination_theme_label) {
        $info['name'] = $this->destination_theme_label;
      }

      if ($this->destination_theme_description) {
        $info['description'] = $this->destination_theme_description;
      }

      // Set a default core version requirement to the current major version of Drupal.
      if (!isset($info['core_version_requirement'])) {
        $info['core_version_requirement'] = '^' . explode('.', \Drupal::VERSION)[0];
      }

      $info_contents = Yaml::encode($info);
      file_put_contents($info_file, $info_contents);
    }

    return NULL;
  }

  /**
   * Compiles strings from source theme that will need replaced with strings from destination theme.
   */
  private function prepareForRenameAndEdit() {
    $old_machine_name = $this->source_theme_name;
    $old_label = $this->source_theme_info['name'] ?? $this->source_theme_name;
    $new_machine_name = $this->destination_theme;
    $new_label = $this->destination_theme_label;

    $this->find_and_replace_patterns = [
      'old' => [
        'machine_name' => $old_machine_name,
        'label' => $old_label,
        'machine_class_name' => u($old_machine_name)->camel()->title(),
        'label_class_name' => u($old_label)->camel()->title(),
      ],
      'new' => [
        'machine_name' => $new_machine_name,
        'label' => $new_label,
        'machine_class_name' => u($new_machine_name)->camel()->title(),
        'label_class_name' => u($new_label)->camel()->title(),
      ],
    ];

    $this->generateFindAndReplaceTokens();
  }

  /**
   * Generates intermediary tokens.
   *
   * Generates tokens that do not contain, and are not contained
   * within the source or destination theme strings. This prevents issues where
   * source/destination string overlaps result in recursive renaming.
   *
   * @return void
   */
  private function generateFindAndReplaceTokens() {
    $old_strings = $this->find_and_replace_patterns['old'];
    $new_strings = $this->find_and_replace_patterns['new'];

    $this->find_and_replace_patterns['token'] = [];

    foreach ($old_strings as $key => $string) {
      if (isset($new_strings[$key])) {
        $token = NULL;
        $token_needs_generated = TRUE;
        while ($token_needs_generated) {
          $token = uniqid('sk');

          $token_needs_generated = (
            str_contains($token, $old_strings[$key]) ||
            str_contains($token, $new_strings[$key]) ||
            str_contains($old_strings[$key], $token) ||
            str_contains($new_strings[$key], $token)
          );
        }

        $this->find_and_replace_patterns['token'][$key] = $token;
      }
    }
  }

  /**
   * Replaces source strings with destination strings by way of an intermediary token.
   */
  private function doRenameAndEdit() {
    $fs = new Filesystem();

    $patterns = $this->find_and_replace_patterns;

    /**
     * Replace source strings with tokens, then tokens with destination strings.
     * File contents must be changed first so Finder filter is given the correct paths.
     */

    // Replace source patterns with tokens in file contents
    foreach ($patterns['token'] as $pattern_id => $token) {
      $old_str = $patterns['old'][$pattern_id];

      $finder = new Finder();
      $files = $finder
        ->in($this->tmp_dir)
        ->files()
        ->contains("/$old_str/")
        ->filter(fn ($file) => !in_array($file->getRelativePathname(), $this->paths_to_skip_edit));

      foreach ($files as $file) {
        $contents = file_get_contents($file->getRealPath());
        $contents = str_replace($old_str, $token, $contents);
        file_put_contents($file->getRealPath(), $contents);
      }
    }

    // Replace token with destination patterns in file contents
    foreach ($patterns['token'] as $pattern_id => $token) {
      $new_str = $patterns['new'][$pattern_id];

      $finder = new Finder();
      $files = $finder
        ->in($this->tmp_dir)
        ->files()
        ->contains("/$token/")
        ->filter(fn ($file) => !in_array($file->getRelativePathname(), $this->paths_to_skip_edit));

      foreach ($files as $file) {
        $contents = file_get_contents($file->getRealPath());
        $contents = str_replace($token, $new_str, $contents);
        file_put_contents($file->getRealPath(), $contents);
      }
    }

    // Replace source patterns with tokens in filenames
    foreach ($patterns['token'] as $pattern_id => $token) {
      $old_str = $patterns['old'][$pattern_id];

      $finder = new Finder();
      $files = $finder
        ->in($this->tmp_dir)
        ->files()
        ->name("/$old_str/")
        ->filter(fn ($file) => !in_array($file->getRelativePathname(), $this->paths_to_skip_rename));

      foreach ($files as $file) {
        $filepath_segments = explode('/', $file->getRealPath());
        $filename = array_pop($filepath_segments);
        $filename = str_replace($old_str, $token, $filename);
        $filepath_segments[] = $filename;
        $fs->rename($file->getRealPath(), implode('/', $filepath_segments));
      }
    }

    // Replace tokens with destination patterns in filenames
    foreach ($patterns['token'] as $pattern_id => $token) {
      $new_str = $patterns['new'][$pattern_id];

      $finder = new Finder();
      $files = $finder
        ->in($this->tmp_dir)
        ->files()
        ->name("/$token/")
        ->filter(fn ($file) => !in_array($file->getRelativePathname(), $this->paths_to_skip_rename));

      foreach ($files as $file) {
        $filepath_segments = explode('/', $file->getRealPath());
        $filename = array_pop($filepath_segments);
        $filename = str_replace($token, $new_str, $filename);
        $filepath_segments[] = $filename;
        $fs->rename($file->getRealPath(), implode('/', $filepath_segments));
      }
    }
  }

  private function doPostProcess() {
    $theme_name = $this->source_theme_name;
    $theme_path = $this->source_theme->getPath();
    $loader = new ClassLoader();
    $loader->addPsr4("Drupal\\$theme_name\\", "$theme_path/src");
    $loader->register();

    $generator_classname = "Drupal\\$this->source_theme_name\\StarterKit";
    if (class_exists($generator_classname)) {
      if (is_a($generator_classname, StarterKitInterface::class, TRUE)) {
        $generator_classname::postProcess($this->tmp_dir, $this->destination_theme, $this->destination_theme_label);
      }
      else {
        $this->io->getErrorStyle()->error("The $generator_classname does not implement \Drupal\Core\Theme\StarterKitInterface and cannot perform post-processing.");
        return FALSE;
      }
    }
    return TRUE;
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
