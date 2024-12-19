<?php

declare(strict_types=1);

namespace Drupal\path\PathVariant;

use Drupal\Core\Entity\EntityInterface;
use Drupal\path\Event\EntityPathsEvent;
use Drupal\path\Event\PathVariantEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Constructs the path variant repository.
 */
final class PathVariantRepository implements PathVariantRepositoryInterface {

  /**
   * Constructs a PathVariantRepository.
   */
  public function __construct(
    public EventDispatcherInterface $dispatcher,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function getInternalPaths(EntityInterface $entity): iterable {
    $this->dispatcher->dispatch($event = EntityPathsEvent::create($entity));
    return $event->getInternalPaths();
  }

  /**
   * {@inheritdoc}
   */
  public function getInternalPathByPathVariant(EntityInterface $entity, PathVariant $pathVariant): string|PlaceHolderInternalPath {
    foreach ($this->getInternalPaths($entity) as [$internalPath, $variant]) {
      if ($variant->getVariant() === $pathVariant->getVariant()) {
        return $internalPath;
      }
    }

    throw new \InvalidArgumentException('Unknown path variant.');
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultPathVariant(EntityInterface $entity): PathVariant {
    foreach ($this->getInternalPaths($entity) as [1 => $variant]) {
      // Return the first.
      // @todo this can be improved in a future issue.
      return $variant;
    }

    throw new \InvalidArgumentException('Entity has no path variants.');
  }

  /**
   * {@inheritdoc}
   */
  public function getPathVariantsForEntity(EntityInterface $entity): iterable {
    $this->dispatcher->dispatch($event = PathVariantEvent::createFromEntity($entity));
    return $event->getVariants();
  }

}
