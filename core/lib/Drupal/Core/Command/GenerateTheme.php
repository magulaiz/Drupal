<?php

namespace Drupal\Core\Command;

use Composer\Autoload\ClassLoader;
use Composer\Semver\VersionParser;
use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ExtensionDiscovery;
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
use Symfony\Component\Finder\Glob;
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
   * @var \Symfony\Component\Console\Style\SymfonyStyle
   */
  private SymfonyStyle $io;

  /**
   * The temporary directory files are stored during operations.
   *
   * @var string
   */
  private string $tmpDir;

  /**
   * The machine name of the source theme.
   *
   * @var String
   */
  private $source_theme_name;

  /**
   * The theme to be duplicated.
   *
   * @var \Drupal\Core\Extension\Extension|null
   */
  private ?Extension $source_theme;

  private $source_theme_info;

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
  private array $infoOverrides = [
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
   *  - token patterns are strings that do not contain and are not contained by
   * either old or new patterns
   *
   * @var array
   */
  private $find_and_replace_patterns = [
    'old' => [],
    'new' => [],
    'token' => [],
  ];

  /**
   * @var \Symfony\Component\Filesystem\Filesystem
   */
  private Filesystem $filesystem;

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

  protected function initialize(InputInterface $input, OutputInterface $output): void {
    $this->io = new SymfonyStyle($input, $output);
    $this->filesystem = new Filesystem();
    $this->tmpDir = $this->getUniqueTmpDirPath();

    // Change the directory to the Drupal root.
    chdir($this->root);
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {

    // Get all command args & options.
    $destination_theme = $input->getArgument('machine-name');
    $this->destination_theme = $destination_theme;

    $destination = trim($input->getOption('path'), '/') . '/' . $destination_theme;
    $this->source_theme_name = $input->getOption('starterkit');
    $this->destination_theme_label = $input->getOption('name') ?: $destination_theme;
    $this->destination_theme_description = $input->getOption('description');

    if (is_dir($destination)) {
      $this->io->getErrorStyle()->error("Theme could not be generated because the destination directory $destination exists already.");
      return 1;
    }

    $this->source_theme = $this->getThemeInfo($this->source_theme_name);
    if ($this->source_theme === NULL) {
      $this->io->getErrorStyle()->error("Theme source theme $this->source_theme_name cannot be found.");
      return 1;
    }

    $starterkit_config_file = $this->source_theme->getPath() . '/' . $this->source_theme->getName() . '.starterkit.yml';
    if (!file_exists($starterkit_config_file)) {
      $this->io->getErrorStyle()->error("Theme source theme $this->source_theme_name is not a valid starter kit.");
      return 1;
    }
    $starterkit_config = Yaml::decode(file_get_contents($starterkit_config_file));

    $this->filesystem->mkdir($this->tmpDir);


    if (isset($starterkit_config['info']) && is_array($starterkit_config['info'])) {
      $this->infoOverrides = $starterkit_config['info'];
    }

    $paths_to_ignore = [
      '/src/StarterKit.php',
      '/*.starterkit.yml',
    ];
    if (isset($starterkit_config['delete']) && is_array($starterkit_config['delete'])) {
      $paths_to_ignore = $starterkit_config['delete'];
    }
    $mirror_iterator = (new Finder)
      ->in($this->source_theme->getPath())
      ->notPath(array_map(
        [self::class, 'processPaths'],
        $paths_to_ignore
      ));

    // Copy entire contents of source theme to tmp_dir.
    $this->filesystem->mirror($this->source_theme->getPath(), $this->tmpDir, $mirror_iterator);

    if (isset($starterkit_config['no_edit']) && is_array($starterkit_config['no_edit'])) {
      $no_edit_globs = array_map([self::class, 'processPaths'], $starterkit_config['no_edit']);
      $files = self::createFilesFinder($this->tmpDir)->path($no_edit_globs);
      $this->paths_to_skip_edit = array_map(static fn ($file) => $file->getRelativePathname(), iterator_to_array($files));
      if (count($this->paths_to_skip_edit) === 0) {
        $this->io->warning('Paths were defined `no_edit` but no files found.');
      }
    }

    if (isset($starterkit_config['no_rename']) && is_array($starterkit_config['no_rename'])) {
      $no_rename_globs = array_map([self::class, 'processPaths'], $starterkit_config['no_rename']);
      $files = self::createFilesFinder($this->tmpDir)->path($no_rename_globs);
      $this->paths_to_skip_rename = array_map(static fn ($file) => $file->getRelativePathname(), iterator_to_array($files));
      if (count($this->paths_to_skip_rename) === 0) {
        $this->io->warning('Paths were defined `no_rename` but no files found.');
      }
    }

    // Get all the source/dest/token strings needed for renaming & editing.
    $this->prepareForRenameAndEdit(
      $this->source_theme_name,
      $this->source_theme_info['name'] ?? $this->source_theme_name,
      $this->destination_theme,
      $this->destination_theme_label
    );

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
    $this->filesystem->mirror($this->tmpDir, $destination);

    $output->writeln(sprintf('Theme generated successfully to %s', $destination));

    return 0;
  }

  /**
   * Overrides source *.info.yml with key/value pairs specified in
   * *.starterkit.yml.
   *
   * @return int|NULL returns an exit code or NULL to continue.
   */
  private function overrideThemeInfo() {
    $info_file = "$this->tmpDir/$this->destination_theme.info.yml";

    if ($info_contents = file_get_contents($info_file)) {
      $info = Yaml::decode($info_contents);
      $this->source_theme_info = $info;

      if (!array_key_exists('version', $info)) {
        $confirm_versionless_source_theme = new ConfirmationQuestion(sprintf('The source theme %s does not have a version specified. This makes tracking changes in the source theme difficult. Are you sure you want to continue?', $this->source_theme_name));
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

      foreach ($this->infoOverrides as $key => $value) {
        if ($value === NULL) {
          unset($info[$key]);
        }
        else {
          $info[$key] = $value;
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
   * Compiles strings from source theme that will need replaced with strings
   * from destination theme.
   */
  private function prepareForRenameAndEdit(string $source_name, string $source_label, $destination_name, $destination_label) {
    $this->find_and_replace_patterns = [
      'old' => [
        'machine_name' => $source_name,
        'label' => $source_label,
        'machine_class_name' => u($source_name)->camel()->title(),
        'label_class_name' => u($source_label)->camel()->title(),
      ],
      'new' => [
        'machine_name' => $destination_name,
        'label' => $destination_label,
        'machine_class_name' => u($destination_name)->camel()->title(),
        'label_class_name' => u($destination_label)->camel()->title(),
      ],
    ];

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
   * Replaces source strings with destination strings by way of an intermediary
   * token.
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

      $files = self::createFilesFinder($this->tmpDir)
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

      $files = self::createFilesFinder($this->tmpDir)
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

      $files = self::createFilesFinder($this->tmpDir)
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

      $files = self::createFilesFinder($this->tmpDir)
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
    $loader = new ClassLoader();
    $loader->addPsr4("Drupal\\$this->source_theme_name\\", "{$this->source_theme->getPath()}/src");
    $loader->register();

    $generator_classname = "Drupal\\$this->source_theme_name\\StarterKit";
    if (class_exists($generator_classname)) {
      if (is_a($generator_classname, StarterKitInterface::class, TRUE)) {
        $generator_classname::postProcess($this->tmpDir, $this->destination_theme, $this->destination_theme_label);
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

  private static function processPaths(string $path): string {
    if (str_starts_with($path, '**')) {
      $path = ltrim($path, '*');
    }
    return trim(Glob::toRegex($path), '/');
  }

  private static function createFilesFinder(string $dir) {
    return (new Finder)->in($dir)->files();
  }

}
