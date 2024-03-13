<?php

namespace Drupal\layout_builder;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\layout_builder\SectionStorage\SectionStorageManagerInterface;
use Drupal\layout_builder\Plugin\SectionStorage\OverridesSectionStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Methods to help with entities using the layout builder.
 *
 * @package Drupal\layout_builder.
 */
class LayoutOverrideFieldHelper implements ContainerInjectionInterface {

  use LayoutEntityHelperTrait;

  /**
   * The section storage manager.
   *
   * @var \Drupal\layout_builder\SectionStorage\SectionStorageManagerInterface
   */
  protected $sectionStorageManager;

  /**
   * The layout tempstore repository.
   *
   * @var \Drupal\layout_builder\LayoutTempstoreRepositoryInterface
   */
  protected $layoutTempstoreRepository;

  /**
   * LayoutOverrideFieldHelper constructor.
   *
   * @param \Drupal\layout_builder\SectionStorage\SectionStorageManagerInterface $section_storage_manager
   *   The section storage manager.
   * @param \Drupal\layout_builder\LayoutTempstoreRepositoryInterface $layout_tempstore_repository
   *   The layout tempstore repository.
   */
  public function __construct(SectionStorageManagerInterface $section_storage_manager, LayoutTempstoreRepositoryInterface $layout_tempstore_repository) {
    $this->sectionStorageManager = $section_storage_manager;
    $this->layoutTempstoreRepository = $layout_tempstore_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.layout_builder.section_storage'),
      $container->get('layout_builder.tempstore_repository')
    );
  }

  /**
   * Update a layout overrides's entity context when entity values change.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity with the overridden layout.
   */
  public function updateTempstoreEntityContext(EntityInterface $entity) {
    if ($section_storage = $this->getSectionStorageFromEntity($entity)) {

      // This is only necessary if there is a layout override in the tempstore.
      if ($this->layoutTempstoreRepository->has($section_storage)) {
        /** @var \Drupal\layout_builder\Plugin\SectionStorage\OverridesSectionStorage $override_temp_store */
        $override_temp_store = $this->layoutTempstoreRepository->get($section_storage);

        // Get the entity currently in the tempstore's entity context.
        $stored_entity = $override_temp_store->getContext('entity')->getContextData()->getEntity();

        // Update the tempstore entity context with a copy of the new entity,
        // but retain the value of the layout field from the tempstore.
        $updated_entity = $entity;
        $updated_entity->{OverridesSectionStorage::FIELD_NAME} = $stored_entity->{OverridesSectionStorage::FIELD_NAME};

        $override_temp_store->setContextValue('entity', $updated_entity);
        $this->layoutTempstoreRepository->set($override_temp_store);
      }
    }
  }

}