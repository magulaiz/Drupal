<?php

declare(strict_types=1);

namespace Drupal\class_resolver_test;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a class for testing container injection.
 */
final class ContainerInjection implements ContainerInjectionInterface {

  /**
   * Constructs a new ContainerInjection object.
   *
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   The lock service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(
    public readonly LockBackendInterface $lock,
    public readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('lock'),
      $container->get(EntityTypeManagerInterface::class),
    );
  }

}
