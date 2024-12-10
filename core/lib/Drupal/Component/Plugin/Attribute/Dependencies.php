<?php

namespace Drupal\Component\Plugin\Attribute;

/**
 * Defines a Dependencies attribute object.
 *
 * Plugin classes can depend on code in modules other than the provider of the
 * plugin type and the dependencies declared in the .info.yml of the module
 * the plugin class is defined in. Examples include MigrateSource plugins, where
 * the plugin type is provided by the migrate module, but a source plugin that
 * is in another module like taxonomy extends the DrupalSqBase class in
 * migrate_drupal.
 *
 * In order to prevent fatal errors or exceptions being thrown on discovery,
 * plugin classes with implicit dependencies like this should use the attribute
 * to define the modules they depend on.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Dependencies {

  /**
   * Constructs a dependencies attribute object.
   *
   * @param string[] $modules
   *   List of modules that the plugin class depends on. The plugin type
   *   provider, the module the plugin class is in, and modules listed in the
   *   module's .info.yml dependencies do not need to be listed here. Note: this
   *   attribute is parsed statically by a method other than Reflection classes.
   *   The 'modules' argument should be set as an array of module machine names
   *   as string literals, and not references to class constants or other
   *   expressions.
   */
  public function __construct(
    protected readonly array $modules,
  ) {}

  /**
   * Gets the list of module dependencies.
   *
   * @return string []
   *   The list of module dependencies.
   */
  public function getModules(): array {
    return $this->modules;
  }

}
