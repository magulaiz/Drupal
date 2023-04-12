<?php

namespace Drupal\field_layout\Form;

use Drupal\Component\Plugin\PluginManagerBase;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\Core\Layout\LayoutPluginManagerInterface;
use Drupal\field_ui\Form\EntityFormDisplayEditForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Edit form for the EntityFormDisplay entity type.
 *
 * @internal
 */
class FieldLayoutEntityFormDisplayEditForm extends EntityFormDisplayEditForm {

  use FieldLayoutEntityDisplayFormTrait;

  /**
   * FieldLayoutEntityFormDisplayEditForm constructor.
   *
   * @param \Drupal\Core\Field\FieldTypePluginManagerInterface $field_type_manager
   *   The field type manager.
   * @param \Drupal\Component\Plugin\PluginManagerBase $plugin_manager
   *   The widget plugin manager.
   * @param \Drupal\Core\Layout\LayoutPluginManagerInterface $layout_plugin_manager
   *   The layout plugin manager.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display_repository.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info service.
   */
  public function __construct(FieldTypePluginManagerInterface $field_type_manager, PluginManagerBase $plugin_manager, LayoutPluginManagerInterface $layout_plugin_manager, EntityDisplayRepositoryInterface $entity_display_repository = NULL, EntityFieldManagerInterface $entity_field_manager = NULL, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL) {
    if (is_null($entity_type_bundle_info)) {
      @trigger_error('Calling ' . __METHOD__ . ' without the $entity_type_bundle_info argument is deprecated in drupal:10.1.0 and will be required in drupal:11.0.0. See https://www.drupal.org/node/3350276', E_USER_DEPRECATED);
      $entity_type_bundle_info = \Drupal::service('entity_type.bundle.info');
    }
    parent::__construct($field_type_manager, $plugin_manager, $entity_display_repository, $entity_field_manager, $entity_type_bundle_info);
    $this->layoutPluginManager = $layout_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.field.field_type'),
      $container->get('plugin.manager.field.widget'),
      $container->get('plugin.manager.core.layout'),
      $container->get('entity_display.repository'),
      $container->get('entity_field.manager'),
      $container->get('entity_type.bundle.info')
    );
  }

}
