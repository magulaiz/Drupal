<?php

declare(strict_types=1);

namespace Drupal\path\EventSubscriber;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\path\Event\EntityPathsEvent;
use Drupal\path\Event\PathVariantEvent;
use Drupal\path\PathVariant\PathVariant;
use Drupal\path\PathVariant\PathVariantRepositoryInterface;
use Drupal\path\PathVariant\PlaceHolderInternalPath;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Responds with internal paths provided by core.
 */
final class EntityPathsEventSubscriber implements EventSubscriberInterface {

  /**
   * Constructs an event subscriber for entity paths.
   */
  public function __construct(
    private EntityTypeManagerInterface $entityTypeManager,
    private PathVariantRepositoryInterface $pathVariantRepository,
  ) {
  }

  /**
   * Subscriber for canonical internal path.
   */
  public function canonicalInternalPath(EntityPathsEvent $event): void {
    $event->addInternalPath(
      $event->getEntity()->isNew() ? PlaceHolderInternalPath::create() : '/' . $event->getEntity()->toUrl(rel: 'canonical')->getInternalPath(),
      PathVariant::createDefault(),
    );
  }

  /**
   * Subscriber for computing internal paths for entity view modes.
   */
  public function entityViewModeInternalPaths(EntityPathsEvent $event): void {
    $entity = $event->getEntity();

    foreach ($this->pathVariantRepository->getPathVariantsForEntity($entity) as $variant) {
      $variantStringed = $variant->getVariantStringed();
      if (FALSE === \str_starts_with($variantStringed, 'view_mode:')) {
        continue;
      }

      [$entityTypeId, $mode] = \explode('.', \substr($variantStringed, 10), 2);

      if ($mode === 'full') {
        // Full mode is handled by canonicalInternalPath().
        continue;
      }

      /** @var \Drupal\Core\Entity\EntityViewModeInterface $entityViewMode */
      $entityViewMode = $this->entityViewModeStorage()->load(sprintf('%s.%s', $entityTypeId, $mode)) ?? throw new \LogicException(sprintf('Mode is expected to exist since it came from %s', PathVariantEvent::class));

      $event->addInternalPath(
        $entity->isNew() ? PlaceHolderInternalPath::create() : '/' . $entity->toUrl()->getInternalPath() . '/' . $entityViewMode->getPath(),
        $variant,
      );
    }
  }

  /**
   * Get entity storage for `entity_view_mode` config entities.
   */
  private function entityViewModeStorage(): ConfigEntityStorageInterface {
    /** @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface */
    return $this->entityTypeManager->getStorage('entity_view_mode');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      EntityPathsEvent::class => [
        ['canonicalInternalPath'],
        ['entityViewModeInternalPaths'],
      ],
    ];
  }

}
