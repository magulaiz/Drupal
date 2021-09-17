<?php

namespace Drupal\field\EventSubscriber;

use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Config\ConfigImporterEvent;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\field\EntityDisplayRebuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Rebuilds entity form/view displays when new field config is imported.
 */
class ConfigImportDisplayRebuildSubscriber implements EventSubscriberInterface {

  /**
   * Priority of the subscriber.
   */
  const PRIORITY = 50;

  /**
   * The class resolver.
   *
   * @var \Drupal\Core\DependencyInjection\ClassResolverInterface
   */
  protected $classResolver;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The constructor.
   */
  public function __construct(
    ClassResolverInterface $class_resolver,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->classResolver = $class_resolver;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];

    $events[ConfigEvents::IMPORT][] = ['onConfigImport', static::PRIORITY];

    return $events;
  }

  /**
   * Handles the config import event.
   *
   * @param \Drupal\Core\Config\ConfigImporterEvent $event
   *   The event to handle.
   */
  public function onConfigImport(ConfigImporterEvent $event) {
    /** @var \Drupal\Core\Config\Entity\ConfigEntityTypeInterface $field_config_type */
    $field_config_type = $this->entityTypeManager->getDefinition('field_config');
    $prefix = $field_config_type->getConfigPrefix() . '.';

    // Check if some field configs were created by the import.
    $created_configs = $event->getChangelist('create');
    $created_field_configs = preg_grep(
      '@^' . preg_quote($prefix, '@') . '@',
      $created_configs
    );
    if (empty($created_field_configs)) {
      return;
    }

    // Collect all the entity type + bundle combinations where fields were added.
    $affected_bundles = [];
    $prefix_length = mb_strlen($prefix);
    foreach ($created_field_configs as $config_name) {
      $field_config_id = mb_substr($config_name, $prefix_length);
      $parts = explode('.', $field_config_id, 3);
      if (count($parts) < 3) {
        throw new \UnexpectedValueException(sprintf(
          'The %s field config name has unexpected format.',
          $config_name
        ));
      }

      [$entity_type_id, $bundle_id] = $parts;
      $affected_bundles[$entity_type_id][$bundle_id] = TRUE;
    }

    // Rebuild form/view displays on affected bundles to make sure they include
    // new fields if for some reason the import didn't add them to the displays.
    /** @var \Drupal\field\EntityDisplayRebuilder $display_rebuilder */
    $display_rebuilder = $this->classResolver
      ->getInstanceFromDefinition(EntityDisplayRebuilder::class);
    foreach ($affected_bundles as $entity_type_id => $bundles) {
      foreach (array_keys($bundles) as $bundle_id) {
        $display_rebuilder->rebuildEntityTypeDisplays($entity_type_id, $bundle_id);
      }
    }
  }

}
