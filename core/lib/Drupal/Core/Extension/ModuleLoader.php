<?php

declare(strict_types = 1);

namespace Drupal\Core\Extension;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Loader for module files.
 */
class ModuleLoader implements ModuleLoaderInterface {

  /**
   * List of loaded files.
   *
   * @var array<string, true>
   *   An associative array whose keys are file paths of loaded files, relative
   *   to the application's root directory.
   */
  protected $loadedFiles;

  /**
   * Boolean indicating whether modules have been loaded.
   *
   * @var bool
   */
  protected $loaded = FALSE;

  /**
   * A list of module include file keys.
   *
   * @var array<string, string|false>
   */
  protected $includeFileKeys = [];

  /**
   * Constructor.
   *
   * @param string $root
   *   Drupal root dir.
   * @param \Drupal\Core\Extension\ActiveModuleListInterface $activeModuleList
   *   Active module list.
   */
  public function __construct(
    #[Autowire('%app.root%')]
    private readonly string $root,
    private readonly ActiveModuleListInterface $activeModuleList,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function load(string $name): bool {
    if (isset($this->loadedFiles[$name])) {
      return TRUE;
    }

    if ($module_object = $this->activeModuleList->getModule($name)) {
      $module_object->load();
      $this->loadedFiles[$name] = TRUE;
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function loadAll(): void {
    if (!$this->loaded) {
      foreach ($this->activeModuleList->getModules() as $name => $module) {
        $this->load($name);
      }
      $this->loaded = TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isLoaded(): bool {
    return $this->loaded;
  }

  /**
   * {@inheritdoc}
   */
  public function reload(): void {
    $this->loaded = FALSE;
    $this->loadAll();
  }

  /**
   * {@inheritdoc}
   */
  public function loadAllIncludes(string $type, string $name = NULL): void {
    foreach ($this->activeModuleList->getModules() as $module => $filename) {
      $this->loadInclude($module, $type, $name);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function loadInclude(string $module, string $type, string $name = NULL): string|false {
    if ($type === 'install') {
      // Make sure the installation API is available
      include_once $this->root . '/core/includes/install.inc';
    }

    $name = $name ?: $module;
    $key = $type . ':' . $module . ':' . $name;
    if (isset($this->includeFileKeys[$key])) {
      return $this->includeFileKeys[$key];
    }
    if ($module_object = $this->activeModuleList->getModule($module)) {
      $file = $this->root . '/' . $module_object->getPath() . "/$name.$type";
      if (is_file($file)) {
        require_once $file;
        $this->includeFileKeys[$key] = $file;
        return $file;
      }
      else {
        $this->includeFileKeys[$key] = FALSE;
      }
    }
    return FALSE;
  }

}
