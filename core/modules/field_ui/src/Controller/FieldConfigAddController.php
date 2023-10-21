<?php

declare(strict_types=1);

namespace Drupal\field_ui\Controller;

use Drupal\Core\Ajax\AjaxHelperTrait;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\Core\TempStore\PrivateTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller for building the field instance form.
 *
 * @internal
 */
final class FieldConfigAddController extends ControllerBase {
  use AjaxHelperTrait;
  /**
   * The field type plugin manager.
   *
   * @var \Drupal\Core\Field\FieldTypePluginManagerInterface
   */
  protected FieldTypePluginManagerInterface $fieldTypePluginManager;

  /**
   * FieldConfigAddController constructor.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStore $tempStore
   *   The private tempstore.
   * @param \Drupal\Core\Field\FieldTypePluginManagerInterface $field_type_plugin_manager
   *   The field type plugin manager.
   */
  public function __construct(
    protected readonly PrivateTempStore $tempStore,
    FieldTypePluginManagerInterface $field_type_plugin_manager,
  ) {
    $this->fieldTypePluginManager = $field_type_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private')->get('field_ui'),
      $container->get('plugin.manager.field.field_type'),
    );
  }

  /**
   * Builds the field config instance form.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $field_name
   *   The name of the field to create.
   *
   * @return array
   *   The field instance edit form.
   */
  public function fieldConfigAddConfigureForm(string $entity_type, string $field_name): array {
    $temp_storage = $this->tempStore->get($entity_type . ':' . $field_name);
    if (!$temp_storage) {
      throw new NotFoundHttpException();
    }

    /** @var \Drupal\Core\Field\FieldConfigInterface $entity */
    $entity = $this->entityTypeManager()->getStorage('field_config')->create([
      ...$temp_storage['field_config_values'],
      'field_storage' => $temp_storage['field_storage'],
    ]);

    $edit_form = $this->entityFormBuilder()->getForm($entity, 'default', [
      'default_options' => $temp_storage['default_options'],
    ]);

    $edit_form['new_storage_wrapper']['label']['#value'] = $temp_storage['label_machine']['label'];
    $edit_form['new_storage_wrapper']['field_name']['#value'] = $temp_storage['label_machine']['machine_name'];

    return $edit_form;
  }

}
