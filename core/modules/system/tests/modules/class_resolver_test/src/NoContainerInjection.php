<?php

declare(strict_types=1);

namespace Drupal\class_resolver_test;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Provides a class for testing the creation of objects no container injection.
 */
final class NoContainerInjection {

  /**
   * Constructs a new NoContainerInjection object.
   *
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   The lock service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\class_resolver_test\NoConstructor|null $nullable
   *   The nullable property
   * @param string $defaultValue
   *   The default value property
   */
  public function __construct(
    #[Autowire(service: 'lock')]
    public readonly LockBackendInterface $lock,
    public readonly EntityTypeManagerInterface $entityTypeManager,
    public readonly ?NoConstructor $nullable,
    public readonly string $defaultValue = 'foo'
  ) {}

}
