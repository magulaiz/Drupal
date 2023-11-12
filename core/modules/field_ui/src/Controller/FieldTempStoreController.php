<?php

declare(strict_types=1);

namespace Drupal\field_ui\Controller;

use Drupal\Core\Ajax\AjaxHelperTrait;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\Core\TempStore\PrivateTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for creating a field storage entity and setting the temp store.
 *
 * @internal
 */
final class FieldTempStoreController extends ControllerBase {
  use AjaxHelperTrait;
  /**
   * The field type plugin manager.
   *
   * @var \Drupal\Core\Field\FieldTypePluginManagerInterface
   */
  protected FieldTypePluginManagerInterface $fieldTypePluginManager;

  /**
   * FieldTempStorageController constructor.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStore $tempStore
   *   The private tempstore.
   * @param \Drupal\Core\Field\FieldTypePluginManagerInterface $field_type_plugin_manager
   *   The field type plugin manager.
   */
  public function __construct(
    protected PrivateTempStore $tempStore,
    FieldTypePluginManagerInterface $field_type_plugin_manager,
  ) {
    $this->fieldTypePluginManager = $field_type_plugin_manager;
    if ($this->tempStore === NULL) {
      @trigger_error('Calling FieldTempStoreController::__construct() without the $tempStore argument is deprecated in drupal:10.2.0 and will be required in drupal:11.0.0. See https://www.drupal.org/node/3383719', E_USER_DEPRECATED);
      $this->tempStore = \Drupal::service('tempstore.private')->get('field_ui');
    }
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
   * Creates a dummy field to set in temp store in order to build the edit form.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   The field instance edit form.
   */
  public function setTempStore($entity_type, $field_storage_type, $field_instance_id, $bundle) {
    $temp_field_name = $field_instance_id;
    $field_values = [
      'entity_type' => $entity_type,
      'bundle' => $bundle,
    ];
    $default_options = [];
    // Check if we're dealing with a preconfigured field.
    if (strpos($field_storage_type, 'field_ui:') === 0) {
      [, $field_type, $preset_key] = explode(':', $field_storage_type, 3);
      $default_options = $this->getNewFieldDefaults($field_type, $preset_key);
    }
    else {
      $field_type = $field_storage_type;
    }
    $field_values += [
      ...$default_options['field_config'] ?? [],
      'field_name' => $temp_field_name,
      // Field translatability should be explicitly enabled by the users.
      'translatable' => FALSE,
    ];

    $field_storage_values = [
      ...$default_options['field_storage_config'] ?? [],
      'field_name' => $temp_field_name,
      'type' => $field_type,
      'entity_type' => $entity_type,
    ];

    try {
      $field_storage_entity = \Drupal::entityTypeManager()->getStorage('field_storage_config')->create($field_storage_values);
    }
    catch (\Exception $e) {
      $this->messenger()->addError($this->t('There was a problem creating field %label: @message'));
      exit;
    }

    // Save field and field storage values in tempstore.
    $this->tempStore->set($entity_type . ':' . $temp_field_name, [
      'field_storage' => $field_storage_entity,
      'field_config_values' => $field_values,
      'default_options' => $default_options,
    ]);
    $route_parameters = [
      'entity_type' => $entity_type,
      'field_name' => $temp_field_name,
      'node_type' => $bundle,
    ];
    if ($this->isAjax()) {
      return $this->redirectAsAjax("field_ui.field_add_{$entity_type}", $route_parameters);
    }
    return $this->redirect("field_ui.field_add_{$entity_type}", $route_parameters);
  }

  /**
   * Get default options from preconfigured options for a new field.
   *
   * @param string $field_name
   *   The machine name of the field.
   * @param string $preset_key
   *   A key in the preconfigured options array for the field.
   *
   * @return array
   *   An array of settings with keys 'field_storage_config', 'field_config',
   *   'entity_form_display', and 'entity_view_display'.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *
   * @see \Drupal\Core\Field\PreconfiguredFieldUiOptionsInterface::getPreconfiguredOptions()
   */
  protected function getNewFieldDefaults(string $field_name, string $preset_key): array {
    $field_type_definition = $this->fieldTypePluginManager->getDefinition($field_name);
    $options = $this->fieldTypePluginManager->getPreconfiguredOptions($field_type_definition['id']);
    $field_options = $options[$preset_key] ?? [];

    $default_options = [];
    // Merge in preconfigured field storage options.
    if (isset($field_options['field_storage_config'])) {
      foreach (['cardinality', 'settings'] as $key) {
        if (isset($field_options['field_storage_config'][$key])) {
          $default_options['field_storage_config'][$key] = $field_options['field_storage_config'][$key];
        }
      }
    }

    // Merge in preconfigured field options.
    if (isset($field_options['field_config'])) {
      foreach (['required', 'settings'] as $key) {
        if (isset($field_options['field_config'][$key])) {
          $default_options['field_config'][$key] = $field_options['field_config'][$key];
        }
      }
    }

    // Preconfigured options only apply to the default display modes.
    foreach (['entity_form_display', 'entity_view_display'] as $key) {
      if (isset($field_options[$key])) {
        $default_options[$key] = [
          'default' => array_intersect_key($field_options[$key], ['type' => '', 'settings' => []]),
        ];
      }
      else {
        $default_options[$key] = ['default' => []];
      }
    }

    return $default_options;
  }

}
