<?php

namespace Drupal\Core\Form;

/**
 * Represents the mapping of a config property to a form element.
 */
final class ConfigTarget {

  /**
   * The name of the form element which maps to this config property.
   *
   * @var string
   *
   * @internal
   *   This property is for internal use only.
   */
  public string $elementName;

  /**
   * The parents of the form element which maps to this config property.
   *
   * @var array
   *
   * @internal
   *   This property is for internal use only.
   */
  public array $elementParents;

  /**
   * Constructs a ConfigTarget object.
   *
   * @param string $configName
   *   The name of the config object being read from or written to, e.g.
   *   `system.site`.
   * @param string $propertyPath
   *   The property path being read or written, e.g., `page.front`.
   * @param array|string|null $fromConfig
   *   (optional) A callback which should transform the value loaded from
   *   config before it gets displayed by the form. If NULL, no transformation
   *   will be done. Defaults to NULL.
   * @param array|string|null $toConfig
   *   (optional) A callback which should transform the value submitted by the
   *   form before it is set in the config object. If NULL, no transformation
   *   will be done. Defaults to NULL.
   */
  private function __construct(
    public readonly string $configName,
    public readonly string $propertyPath,
    public readonly array|string|null $fromConfig = NULL,
    public readonly array|string|null $toConfig = NULL,
  ) {}

  /**
   * Creates a ConfigTarget object.
   *
   * @param string $target
   *   The config object and property path that should be read from and written
   *   to, in the format `CONFIG_NAME:PROPERTY_PATH`. For example,
   *   `system.site:page.front`.
   * @param array|string|null $fromConfig
   *   (optional) A callback which should transform the value loaded from
   *   config before it gets displayed by the form. If NULL, no transformation
   *   will be done. Defaults to NULL.
   * @param array|string|null $toConfig
   *   (optional) A callback which should transform the value submitted by the
   *   form before it is set in the config object. If NULL, no transformation
   *   will be done. Defaults to NULL.
   *
   * @return self
   *   An instance of this class.
   */
  public static function create(string $target, array|string|null $fromConfig = NULL, array|string|null $toConfig = NULL) {
    [$config_name, $property_path] = explode(':', $target, 2);
    return new self($config_name, $property_path, $fromConfig, $toConfig);
  }

}
