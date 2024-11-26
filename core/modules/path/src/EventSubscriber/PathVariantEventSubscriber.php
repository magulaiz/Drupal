<?php

declare(strict_types=1);

namespace Drupal\path\EventSubscriber;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\path\Event\PathVariantEvent;
use Drupal\path\PathVariant\PathVariant;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Responds with variants provided by core.
 */
final class PathVariantEventSubscriber implements EventSubscriberInterface {

  /**
   * Constructs an event subscriber for path variants.
   */
  public function __construct(
    public EntityTypeManagerInterface $entityTypeManager,
  ) {
  }

  /**
   * The default variant.
   */
  public function defaultVariant(PathVariantEvent $event): void {
    // Full mode is always present.
    $event->addVariant(PathVariant::createDefault());
  }

  /**
   * Subscriber adding path variants for entity view modes.
   */
  public function entityViewModePathVariants(PathVariantEvent $event): void {
    $entityTypeId = $event->getEntityTypeId();

    // Determine view modes with a path.
    $viewModeIds = $this->entityViewModeStorage()
      ->getQuery()
      ->exists('path')
      ->condition('id', sprintf('%s.', $entityTypeId), 'STARTS_WITH')
      ->condition('id', sprintf('%s.%s', $entityTypeId, 'full'), '<>')
      ->accessCheck(FALSE)
      ->execute();

    // Determine which bundles have a disabled page display:
    $hiddenDisplayIds = $this->entityViewDisplayStorage()->getQuery()
      ->accessCheck(FALSE)
      ->condition('targetEntityType', $entityTypeId)
      ->condition('bundle', $event->getBundle())
      ->condition('pageDisplay', FALSE)
      ->execute();

    // Remove disabled view modes:
    $viewModeIds = \array_diff($viewModeIds, \array_map(
      static fn (string $view_display_id) => \sprintf('%s.%s', $entityTypeId, \explode('.', $view_display_id)[2]),
      $hiddenDisplayIds,
    ));

    foreach ($this->entityViewModeStorage()->loadMultiple($viewModeIds) as $viewMode) {
      $event->addVariant(PathVariant::create(
        sprintf('view_mode:%s.%s', $entityTypeId, $viewMode->getMode()),
        new TranslatableMarkup('@label view mode', ['@label' => $viewMode->label()]),
      ));
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      PathVariantEvent::class => [
        ['defaultVariant'],
        ['entityViewModePathVariants'],
      ],
    ];
  }

  /**
   * Get entity storage for `entity_view_display` config entities.
   */
  private function entityViewDisplayStorage(): ConfigEntityStorageInterface {
    /** @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface */
    return $this->entityTypeManager->getStorage('entity_view_display');
  }

  /**
   * Get entity storage for `entity_view_mode` config entities.
   */
  private function entityViewModeStorage(): ConfigEntityStorageInterface {
    /** @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface */
    return $this->entityTypeManager->getStorage('entity_view_mode');
  }

}
