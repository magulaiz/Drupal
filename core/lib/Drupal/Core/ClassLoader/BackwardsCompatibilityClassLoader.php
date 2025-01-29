<?php

declare(strict_types=1);

namespace Drupal\Core\ClassLoader;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class BackwardsCompatibilityClassLoader {

  protected ?array $movedClasses = NULL;

  public function __construct(protected ParameterBagInterface $parameterBag) {}

  /**
   * Aliases a moved class to another class, instead of actually autoloading it.
   *
   * @param $class
   *   The classname to load.
   */
  public function loadClass(string $class): void {
    if ($this->movedClasses === NULL) {
      if ($this->parameterBag->has('core.moved_classes')) {
        $this->movedClasses = $this->parameterBag->get('core.moved_classes');
      }
      if ($this->parameterBag->has('container.modules')) {
        foreach (array_keys($this->parameterBag->get('container.modules')) as $module) {
          if ($this->parameterBag->has($module . '.moved_classes')) {
            $this->movedClasses = $this->movedClasses + $this->parameterBag->get($module . '.moved_classes');
          }
        }
      }
    }

    if (isset($this->movedClasses[$class])) {
      $moved = $this->movedClasses[$class];
      if (isset($moved['deprecation_version']) && isset($moved['removed_version'])) {
        // @phpcs:ignore
        @trigger_error(sprintf('Class %s is deprecated in %s and is removed from %s. See %s', $class, $moved['deprecation_version'], $moved['removed_version'], $moved['class']), E_USER_DEPRECATED);
      }
      class_alias($moved['class'], $class, TRUE);
    }
  }

}
