<?php

namespace Drupal\views\Plugin\views\field_views_data;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines views data for fields of type "language".
 *
 * @ViewsFieldData(
 *   id = "language",
 *   field_types = {
 *     "language",
 *   },
 *   argument = {
 *     "id" = "language",
 *   },
 *   filter = {
 *     "id" = "language",
 *   }
 * )
 */
class Language extends FieldViewsDataPluginBase {

  /**
   * The drupal entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition, $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getViewsData(FieldStorageDefinitionInterface $field_storage, $column_name) {
    $views_field = parent::getViewsData($field_storage, $column_name);

    if ($entity_type_id = $field_storage->getTargetEntityTypeId()) {
      $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
      // Apply special titles for the langcode field.
      if ($field_storage->getName() == $entity_type->getKey('langcode')) {
        if ($table = $entity_type->getDataTable() || $table = $entity_type->getRevisionDataTable()) {
          $views_field['title'] = $this->t('Translation language');
        }
        elseif ($table = $entity_type->getBaseTable() || $table = $entity_type->getRevisionTable()) {
          $views_field['title'] = $this->t('Original language');
        }
      }
    }

    return $views_field;
  }

}
