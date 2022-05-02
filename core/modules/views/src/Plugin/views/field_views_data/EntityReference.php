<?php

namespace Drupal\views\Plugin\views\field_views_data;

use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines views data for fields of type "entity_reference".
 *
 * @ViewsFieldData(
 *   id = "entity_reference",
 *   argument = {
 *     "id" = "string",
 *   },
 *   filter = {
 *     "id" = "string",
 *   }
 * )
 */
class EntityReference extends FieldViewsDataPluginBase {

  /**
   * The Drupal type entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Drupal field entity manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Creates a new EntityReference instance.
   *
   * @param array $configuration
   *   The configuration for this specific instance.
   * @param string $plugin_id
   *   The id of the plugin to create.
   * @param array $plugin_definition
   *   The definition of the plugin's available options.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The entity field manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, EntityFieldManagerInterface $entityFieldManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
    $this->entityFieldManager = $entityFieldManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getViewsData(FieldStorageDefinitionInterface $field_storage, $column_name) {
    // @todo Should the actual field handler respect that this just renders a
    //   number?
    // @todo Create an optional entity field handler, that can render the
    //   entity.
    // @see https://www.drupal.org/node/2322949
    $views_field = parent::getViewsData($field_storage, $column_name);

    $target_entity_type_id = $field_storage->getTargetEntityTypeId();
    if (!$this->entityTypeManager->hasHandler($target_entity_type_id, 'views_data')) {
      return [];
    }
    /** @var \Drupal\views\EntityViewsDataInterface $views_data */
    $views_data = $this->entityTypeManager->getHandler($target_entity_type_id, 'views_data');

    if ($entity_type_id = $field_storage->getSetting('target_type')) {
      $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);

      // Only add relationships for content entities.
      if ($entity_type instanceof ContentEntityType) {
        $views_field['relationship'] = [
          'base' => $views_data->getViewsTableForEntityType($entity_type),
          'base field' => $entity_type->getKey('id'),
          'label' => $entity_type->getLabel(),
          'title' => $entity_type->getLabel(),
          'id' => 'standard',
        ];

        $entity_type_id_key = $entity_type->getKey('id');
        $field_definitions = $this->entityFieldManager
          ->getBaseFieldDefinitions($entity_type->id());
        $entity_type_id_definition = $field_definitions[$entity_type_id_key];

        if ($entity_type_id_definition->getType() === 'integer') {
          $views_field['argument']['id'] = 'numeric';
          $views_field['filter']['id'] = 'numeric';
        }
      }
    }

    if ($field_storage->getName() == $this->entityTypeManager->getDefinition($target_entity_type_id)->getKey('bundle')) {
      $views_field['filter']['id'] = 'bundle';
    }

    return $views_field;
  }

}
