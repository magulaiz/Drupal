<?php

declare(strict_types = 1);

namespace Drupal\Core\Form;

/**
 * Represents the mapping of multiple config properties to one form element.
 */
final class ConfigMultiTarget {

  /**
   * The parents of the form element which maps to this config property.
   *
   * @var array
   *
   * @see \Drupal\Core\Form\ConfigFormBase::storeConfigKeyToFormElementMap()
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
   * @param string[] $propertyPaths
   *   The property paths being read or written, e.g., `page.front`.
   * @param string $fromConfig
   *   A callback which should transform the multiple values loaded from config
   *   before it gets displayed by the single form element.
   * @param string $toConfig
   *   A callback which should transform the single value submitted by the
   *   form before it is set to the multiple properties in the config object.
   */
  public function __construct(
    public readonly string $configName,
    public readonly array $propertyPaths,
    public readonly string $fromConfig,
    public readonly string $toConfig,
  ) {
    assert(is_callable($fromConfig));
    assert(is_callable($toConfig));
  }

}
