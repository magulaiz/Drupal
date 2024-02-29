<?php

namespace Drupal\Core\Config;

use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\Extension\ThemeExtensionList;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\StringTranslation\TranslationManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Factory class to create config importer objects.
 *
 * This class is declared as final because the ConfigImporter class is not
 * intended to be swappable.
 */
final class ConfigImporterFactory {

  /**
   * Creates a ConfigImporterFactory instance.
   *
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher service.
   * @param \Drupal\Core\Config\ConfigManagerInterface $config_manager
   *   The config manager.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   The lock backend to ensure multiple imports do not occur at the same time.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typed_config_manager
   *   The typed config manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $module_installer
   *   The module installer service.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler service.
   * @param \Drupal\Core\StringTranslation\TranslationManager $string_translation
   *   The string translation service.
   * @param \Drupal\Core\Extension\ModuleExtensionList $module_extension_list
   *   The module extension list service.
   * @param \Drupal\Core\Extension\ThemeExtensionList $theme_extension_list
   *   The theme extension list service.
   */
  public function __construct(
    protected EventDispatcherInterface $eventDispatcher,
    protected ConfigManagerInterface $configManager,
    protected LockBackendInterface $lock,
    protected TypedConfigManagerInterface $typedConfigManager,
    protected ModuleHandlerInterface $moduleHandler,
    protected ModuleInstallerInterface $moduleInstaller,
    protected ThemeHandlerInterface $themeHandler,
    protected TranslationManager $stringTranslation,
    protected ModuleExtensionList $moduleExtensionList,
    protected ThemeExtensionList $themeExtensionList
  ) { }

  /**
   * Creates a ConfigImporter instance.
   *
   * @param \Drupal\Core\Config\StorageComparer $storage_comparer
   *   The storage comparer object. The type is the class and not
   *   StorageComparerInterface because that is due to be removed: see
   *   https://www.drupal.org/project/drupal/issues/3410037.
   *
   * @return \Drupal\Core\Config\ConfigImporter
   *   A config importer instance.
   */
  public function createConfigImporter(StorageComparer $storage_comparer): ConfigImporter {
    return new ConfigImporter(
      $storage_comparer,
      $this->eventDispatcher,
      $this->configManager,
      $this->lock,
      $this->typedConfigManager,
      $this->moduleHandler,
      $this->moduleInstaller,
      $this->themeHandler,
      $this->stringTranslation,
      $this->moduleExtensionList,
      $this->themeExtensionList,
    );
  }

}
