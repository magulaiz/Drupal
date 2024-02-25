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
  protected function configure(): void {
    $this->setName('generate-theme')
      ->setDescription('Generates a new theme based on latest default markup.')
      ->addArgument('machine-name', InputArgument::REQUIRED, 'The machine name of the generated theme')
      ->addOption('name', NULL, InputOption::VALUE_OPTIONAL, 'A name for the theme.')
      ->addOption('description', NULL, InputOption::VALUE_OPTIONAL, 'A description of your theme.', '')
      ->addOption('path', NULL, InputOption::VALUE_OPTIONAL, 'The path where your theme will be created. Defaults to: themes', 'themes')
      ->addOption('starterkit', NULL, InputOption::VALUE_OPTIONAL, 'The theme to use as the starterkit', 'starterkit_theme')
      ->addUsage('custom_theme --name "Custom Theme" --description "Custom theme generated from a starterkit theme" --path themes')
      ->addUsage('custom_theme --name "Custom Theme" --starterkit mystarterkit');
  }

  protected function initialize(InputInterface $input, OutputInterface $output): void {
    $this->io = new SymfonyStyle($input, $output);
    $this->filesystem = new Filesystem();
    $this->tmpDir = $this->getUniqueTmpDirPath();

    if ($input->getOption('name') === NULL) {
      $input->setOption('name', $input->getArgument('machine-name'));
    }

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
    $this->destination_theme_label = $input->getOption('name');
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

    try {
      $starterkit_config = self::loadStarterKitConfig(
        $this->source_theme,
        $this->destination_theme_label,
        $this->destination_theme_description
      );
    }
    catch (\Exception $e) {
      $this->io->getErrorStyle()->error($e->getMessage());
      return 1;
    }

    $this->filesystem->mkdir($this->tmpDir);

    $mirror_iterator = (new Finder)
      ->in($this->source_theme->getPath())
      ->files()
      ->notName($starterkit_config['delete'])
      ->notPath($starterkit_config['delete']);

    $this->filesystem->mirror($this->source_theme->getPath(), $this->tmpDir, $mirror_iterator);

    if (count($starterkit_config['no_edit']) > 0) {
      $files = self::createFilesFinder($this->tmpDir)->path($starterkit_config['no_edit']);
      $this->paths_to_skip_edit = array_map(static fn ($file) => $file->getRelativePathname(), iterator_to_array($files));
      if (count($this->paths_to_skip_edit) === 0) {
        $this->io->warning('Paths were defined `no_edit` but no files found.');
      }
    }

    if (count($starterkit_config['no_rename']) > 0) {
      $files = self::createFilesFinder($this->tmpDir)->path($starterkit_config['no_rename']);
      $this->paths_to_skip_rename = array_map(static fn ($file) => $file->getRelativePathname(), iterator_to_array($files));
      if (count($this->paths_to_skip_rename) === 0) {
        $this->io->warning('Paths were defined `no_rename` but no files found.');
      }
    }

    // Replace temporary placeholder tokens with final strings.
    $this->doRenameAndEdit(
      $this->source_theme_name,
      $this->source_theme_info['name'] ?? $this->source_theme_name,
      $this->destination_theme,
      $this->destination_theme_label
    );

    // Alter THEMENAME.info.yml for new theme.
    if (!is_null($exit_code = $this->overrideThemeInfo($starterkit_config['info']))) {
      return $exit_code;
    }

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
        return 1;
      }
    }

    // Move altered theme to final destination.
    $this->filesystem->mirror($this->tmpDir, $destination);

    $this->io->writeln(sprintf('Theme generated successfully to %s', $destination));

    return 0;
  }

  /**
   * Overrides source *.info.yml with key/value pairs.
   *
   * @return int|NULL returns an exit code or NULL to continue.
   */
  private function overrideThemeInfo(array $info_overrides) {
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

      foreach ($info_overrides as $key => $value) {
        if ($value === NULL) {
          unset($info[$key]);
        }
        else {
          $info[$key] = $value;
        }
      }

      // Insert generator string, theme label, and description from command after overrides.
      $info['generator'] = $generator_string;

      $info_contents = Yaml::encode($info);
      file_put_contents($info_file, $info_contents);
    }

    return NULL;
  }

  /**
   * Replaces source strings with destination strings.
   */
  private function doRenameAndEdit(string $source_name, string $source_label, $destination_name, $destination_label) {
    $patterns = [
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

    $old_strings = $patterns['old'];
    $new_strings = $patterns['new'];

    $patterns['token'] = [];

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

        $patterns['token'][$key] = $token;
      }
    }

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
        $this->filesystem->rename($file->getRealPath(), implode('/', $filepath_segments));
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
        $this->filesystem->rename($file->getRealPath(), implode('/', $filepath_segments));
      }
    }
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
    return Glob::toRegex(trim($path, '/'));
  }

  private static function createFilesFinder(string $dir): Finder {
    return (new Finder)->in($dir)->files();
  }

  private static function loadStarterKitConfig(
    Extension $theme,
    string $name,
    string $description
  ): array {
    $starterkit_config_file = $theme->getPath() . '/' . $theme->getName() . '.starterkit.yml';
    if (!file_exists($starterkit_config_file)) {
      throw new \RuntimeException("Theme source theme {$theme->getName()} is not a valid starter kit.");
    }
    $starterkit_config_defaults = [
      'info' => [
        'name' => $name,
        'description' => $description,
        'core_version_requirement' => '^' . explode('.', \Drupal::VERSION)[0],
        'hidden' => NULL,
        'starterkit' => NULL,
        'version' => '1.0.0',
      ],
      'delete' => [
        '/src/StarterKit.php',
        '/*.starterkit.yml',
      ],
      'no_edit' => [],
      'no_rename' => [],
    ];
    $starterkit_config = Yaml::decode(file_get_contents($starterkit_config_file));
    if (!is_array($starterkit_config)) {
      throw new \RuntimeException('Starterkit config is was not able to be parsed.');
    }
    if (!isset($starterkit_config['info'])) {
      $starterkit_config['info'] = [];
    }
    $starterkit_config['info'] = array_merge($starterkit_config_defaults['info'], $starterkit_config['info']);

    foreach (['delete', 'no_edit', 'no_rename'] as $key) {
      if (!isset($starterkit_config[$key])) {
        $starterkit_config[$key] = $starterkit_config_defaults[$key];
      }
      if (!is_array($starterkit_config[$key])) {
        throw new \RuntimeException("$key in starterkit.yml must be an array");
      }
      $starterkit_config[$key] = array_map([self::class, 'processPaths'], $starterkit_config[$key]);
    }

    return $starterkit_config;
  }

}
