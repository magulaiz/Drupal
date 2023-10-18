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
   * @param string|null $fromConfig
   *   (optional) A callback which should transform the value loaded from
   *   config before it gets displayed by the form. If NULL, no transformation
   *   will be done. Defaults to NULL.
   * @param string|null $toConfig
   *   (optional) A callback which should transform the value submitted by the
   *   form before it is set in the config object. If NULL, no transformation
   *   will be done. Defaults to NULL.
   */
  public function __construct(
    public readonly string $configName,
    public readonly string $propertyPath,
    public readonly ?string $fromConfig = NULL,
    public readonly ?string $toConfig = NULL,
  ) {
    if ($fromConfig) {
      assert(is_callable($fromConfig));
    }
    if ($toConfig) {
      assert(is_callable($toConfig));
    }
  }

  public static function fromString(string $target, ?string $fromConfig = NULL, ?string $toConfig = NULL): self {
    return new static(
      ...explode(':', $target, 2),
      $fromConfig,
      $toConfig,
    );
  }

}
