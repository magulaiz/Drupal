<?php

declare(strict_types=1);

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

    $destination = trim($input->getOption('path'), '/') . '/' . $destination_theme;
    $this->source_theme_name = $input->getOption('starterkit');
    $theme_label = $input->getOption('name');

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
      $source_version = self::determineSourceVersion(
        $this->source_theme,
        $this->io
      );
    }
    catch (\Exception $e) {
      $this->io->getErrorStyle()->error($e->getMessage());
      return 1;
    }

    try {
      $starterkit_config = self::loadStarterKitConfig(
        $this->source_theme,
        $source_version,
        $theme_label,
        $input->getOption('description')
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
      ->notName($starterkit_config['ignore'])
      ->notPath($starterkit_config['ignore']);

    $this->filesystem->mirror($this->source_theme->getPath(), $this->tmpDir, $mirror_iterator);

    // verify files match the patterns
    // @todo could this go into loading of the config logic
    if (count($starterkit_config['no_edit']) > 0) {
      $files = self::createFilesFinder($this->tmpDir)->path($starterkit_config['no_edit']);
      $starterkit_config['no_edit'] = array_map(static fn ($file) => $file->getRelativePathname(), iterator_to_array($files));
      if (count($starterkit_config['no_edit']) === 0) {
        $this->io->warning('Paths were defined `no_edit` but no files found.');
      }
    }
    // verify files match the patterns
    // @todo could this go into loading of the config logic
    if (count($starterkit_config['no_rename']) > 0) {
      $files = self::createFilesFinder($this->tmpDir)->path($starterkit_config['no_rename']);
      $starterkit_config['no_rename'] = array_map(static fn ($file) => $file->getRelativePathname(), iterator_to_array($files));
      if (count($starterkit_config['no_rename']) === 0) {
        $this->io->warning('Paths were defined `no_rename` but no files found.');
      }
    }

    $patterns = [
      'old' => [
        'machine_name' => $this->source_theme_name,
        'label' => $this->source_theme->getName(),
        'machine_class_name' => (string) u($this->source_theme_name)->camel()->title(),
        'label_class_name' => (string) u($this->source_theme->getName())->camel()->title(),
      ],
      'new' => [
        'machine_name' => $destination_theme,
        'label' => $theme_label,
        'machine_class_name' => (string) u($destination_theme)->camel()->title(),
        'label_class_name' => (string) u($theme_label)->camel()->title(),
      ],
    ];
    $filesToEdit = self::createFilesFinder($this->tmpDir)
      ->contains(array_values($patterns['old']))
      ->notPath($starterkit_config['no_edit']);
    foreach ($filesToEdit as $file) {
      $contents = file_get_contents($file->getRealPath());
      $contents = str_replace($patterns['old'], $patterns['new'], $contents);
      file_put_contents($file->getRealPath(), $contents);
    }

    $filesToRename = self::createFilesFinder($this->tmpDir)
      ->name(array_map(static fn (string $pattern) => "*$pattern*", array_values($patterns['old'])))
      ->notPath($starterkit_config['no_rename']);
    foreach ($filesToRename as $file) {
      $filepath_segments = explode('/', $file->getRealPath());
      $filename = array_pop($filepath_segments);
      $filename = str_replace($patterns['old'], $patterns['new'], $filename);
      $filepath_segments[] = $filename;
      $this->filesystem->rename($file->getRealPath(), implode('/', $filepath_segments));
    }

    $info_file = "$this->tmpDir/$destination_theme.info.yml";
    $info = Yaml::decode(file_get_contents($info_file));
    $info = array_filter(array_merge($info, $starterkit_config['info']));
    file_put_contents($info_file, Yaml::encode($info));

    $loader = new ClassLoader();
    $loader->addPsr4("Drupal\\$this->source_theme_name\\", "{$this->source_theme->getPath()}/src");
    $loader->register();

    $generator_classname = "Drupal\\$this->source_theme_name\\StarterKit";
    if (class_exists($generator_classname)) {
      if (is_a($generator_classname, StarterKitInterface::class, TRUE)) {
        $generator_classname::postProcess($this->tmpDir, $destination_theme, $theme_label);
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
    string $version,
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
        'generator' => "{$theme->getName()}:$version",
      ],
      'ignore' => [
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

    foreach (['ignore', 'no_edit', 'no_rename'] as $key) {
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

  private static function determineSourceVersion(
    Extension $theme,
    SymfonyStyle $io
  ): string {
    $info = Yaml::decode(file_get_contents($theme->getPathname()));
    $source_version = $info['version'] ?? '';
    if ($source_version === '') {
      $confirm = new ConfirmationQuestion(sprintf(
        'The source theme %s does not have a version specified. This makes tracking changes in the source theme difficult. Are you sure you want to continue?',
        $theme->getName()
      ));
      if (!$io->askQuestion($confirm)) {
        throw new \RuntimeException('source version could not be determined');
      }
      $source_version = 'unknown-version';
    }
    if ($source_version === 'VERSION') {
      $source_version = \Drupal::VERSION;
    }

    // A version in the generator string like "9.4.0-dev" is not very helpful.
    // When this occurs, generate a version string that points to a commit.
    if (VersionParser::parseStability($source_version) === 'dev') {
      $git_check = Process::fromShellCommandline('git --help');
      $git_check->run();
      if ($git_check->getExitCode()) {
        throw new \RuntimeException(
          sprintf(
            'The source theme %s has a development version number (%s). Determining a specific commit is not possible because git is not installed. Either install git or use a tagged release to generate a theme.',
            $theme->getName(),
            $source_version
          )
        );
      }

      // Get the git commit for the source theme.
      $git_get_commit = Process::fromShellCommandline("git rev-list --max-count=1 --abbrev-commit HEAD -C {$theme->getPath()}");
      $git_get_commit->run();
      if (!$git_get_commit->isSuccessful() || $git_get_commit->getOutput() === '') {
        $confirm = new ConfirmationQuestion(sprintf(
          'The source theme %s has a development version number (%s). Because it is not a git checkout, a specific commit could not be identified. This makes tracking changes in the source theme difficult. Are you sure you want to continue?',
          $theme->getName(),
          $source_version
        ));
        if (!$io->askQuestion($confirm)) {
          throw new \RuntimeException('source version could not be determined');
        }
        $source_version .= '#unknown-commit';
      }
      else {
        $source_version .= '#' . trim($git_get_commit->getOutput());
      }
    }
    return $source_version;
  }

}
