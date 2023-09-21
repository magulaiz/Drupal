<?php

declare(strict_types=1);

namespace Drupal\field_ui\Controller;

use Drupal\Core\Ajax\AjaxHelperTrait;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\field_ui\Form\FieldStorageAddForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for creating a field storage entity and setting the temp store.
 *
 * @internal
 */
final class FieldTempStoreDeleteController extends ControllerBase {
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
   * @return AjaxResponse
   *   The field instance edit form.
   */
  public function deleteTempStore($entity_type, $field_name, $bundle) {
    // Delete field storage.
    // FieldStorageConfig::loadByName($entity_type, $field_instance_id)->delete();
    // delete field
    //    FieldConfig::loadByName($entity_type, 'article', $field_instance_id)->delete();
    // delete temp store.
    $form = $this->formBuilder()->getForm(FieldStorageAddForm::class, $entity_type, $bundle);
    // Delete stored field data in case user changes field type.
    $this->tempStore->delete($entity_type . ":" . $field_name);
    //    if ($this->isAjax()) {.
    $response = new AjaxResponse();
    $response->addCommand(new OpenModalDialogCommand('Create a new field', $form));
    return $response;
  }

}
