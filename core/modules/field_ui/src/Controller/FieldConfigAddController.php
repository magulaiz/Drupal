<?php

namespace Drupal\field_ui\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller for building the field config instance form.
 */
class FieldConfigAddController extends ControllerBase {

  /**
   * The tempstore object.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStore;

  /**
   * FieldConfigAddController constructor.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   * @param \Drupal\Core\Field\FieldTypePluginManagerInterface $fieldTypeManager
   *   The field type plugin manager.
   * @param Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface $selectionManager
   *   The entity reference selection plugin manager.
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, protected FieldTypePluginManagerInterface $fieldTypeManager, protected SelectionPluginManagerInterface $selectionManager) {
    $this->tempStore = $temp_store_factory->get('field_ui');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
      $container->get('plugin.manager.field.field_type'),
      $container->get('plugin.manager.entity_reference_selection')
    );
  }

  /**
   * Build the field config instance form.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $field_name
   *   The name of the field to create.
   *
   * @return array
   *   The field storage instance edit form.
   */
  public function fieldConfigAddConfigureForm($entity_type, $field_name) {
    $temp_storage = $this->tempStore->get($this->currentUser()->id() . ':' . $entity_type . ':' . $field_name);
    if (!$temp_storage) {
      throw new NotFoundHttpException();
    }

    $field_storage_config = [
      'entity_type' => $entity_type,
      'field_name' => $field_name,
      'type' => $temp_storage['field_storage']->getType(),
    ];
    if ($temp_storage['field_storage']->getSettings()) {
      $field_storage_config['settings'] = $temp_storage['field_storage']->getSettings();
    }
    $field_storage_entity = $this->entityTypeManager()->getStorage('field_storage_config')->create($field_storage_config);

    $temp_storage['field_storage'] = $field_storage_entity;
    $this->tempStore->set($this->currentUser()->id() . ':' . $entity_type . ':' . $field_name, $temp_storage);
    /** @var \Drupal\Core\Field\FieldConfigInterface $entity */
    $entity = $this->entityTypeManager()->getStorage('field_config')->create([
      ...$temp_storage['field_values'],
      'field_storage' => $temp_storage['field_storage'],
    ]);
    $entity->setSettings($entity->getSettings() + $this->fieldTypeManager->getDefaultFieldSettings($temp_storage['field_storage']->getType()));

    // Act on all sub-types of the entity_reference field type.
    // @see field_field_config_presave
    /** @var \Drupal\Core\Field\FieldTypePluginManager $field_type_manager */
    $item_class = 'Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem';
    $class = $this->fieldTypeManager->getPluginClass($entity->getType());
    if ($class === $item_class || is_subclass_of($class, $item_class)) {
      // Make sure the selection handler plugin is the correct derivative for the
      // target entity type when new entity is created.
      $target_type = $entity->getFieldStorageDefinition()->getSetting('target_type');

      [$current_handler] = explode(':', $entity->getSetting('handler'), 2);
      $entity->setSetting('handler', $this->selectionManager->getPluginId($target_type, $current_handler));
    }

    return $this->entityFormBuilder()->getForm($entity, 'edit');
  }

}
