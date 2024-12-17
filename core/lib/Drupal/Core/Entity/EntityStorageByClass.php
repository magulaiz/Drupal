<?php

declare(strict_types = 1);

namespace Drupal\Core\Entity;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;

class EntityStorageByClass implements EntityStorageByClassInterface {

  protected array $map;

  public function __construct(
    protected readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function getStorageByClass(string $class, ?string $entity_type_id = NULL): EntityStorageInterface {
    $this->map ??= $this->generateTypeMap();
    $entity_type_id = $this->map[$class] ?? NULL;
    if ($entity_type_id === NULL) {
      throw new PluginNotFoundException("No entity type found for class '$class'.");
    }
    return $this->entityTypeManager->getStorage($entity_type_id);
  }

  /**
   * Builds a map of entity type ids by class or interface name.
   *
   * @return array<class-string<\Drupal\Core\Entity\EntityInterface>, string>
   *   Map of entity types by class or interface name.
   */
  protected function generateTypeMap(): array {
    $map = [];
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $definition) {
      $classes = [$definition->getClass(), $definition->getOriginalClass()];
      foreach ($classes as $class) {
        if (isset($map[$class])) {
          $map[$class] = FALSE;
        }
        else {
          $map[$class] = $entity_type_id;
        }
      }
    }
    $map = array_filter($map);
    foreach ($map as $class => $entity_type_id) {
      try {
        $interfaces = (new \ReflectionClass($class))->getInterfaceNames();
      }
      catch (\ReflectionException) {
        unset($map[$class]);
        continue;
      }
      foreach ($interfaces as $interface) {
        if (isset($map[$interface])) {
          $map[$interface] = FALSE;
        }
        else {
          $map[$interface] = $entity_type_id;
        }
      }
    }
    $map = array_filter($map);
    return $map;
  }

}
