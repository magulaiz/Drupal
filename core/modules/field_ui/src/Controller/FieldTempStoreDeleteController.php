<?php

declare(strict_types=1);

namespace Drupal\field_ui\Controller;

use Drupal\Core\Ajax\AjaxHelperTrait;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Controller\ControllerBase;
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
   * FieldTempStorageController constructor.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStore $tempStore
   *   The private tempstore.
   */
  public function __construct(
    protected PrivateTempStore $tempStore,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private')->get('field_ui'),
    );
  }

  /**
   * Creates a dummy field to set in temp store in order to build the edit form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The field instance edit form.
   */
  public function deleteTempStore($entity_type, $field_name, $bundle) {
    $form = $this->formBuilder()->getForm(FieldStorageAddForm::class, $entity_type, $bundle);
    // Delete stored field data in case user changes field type.
    $this->tempStore->delete($entity_type . ":" . $field_name);
    if ($this->isAjax()) {
      $response = new AjaxResponse();
      $response->addCommand(new OpenModalDialogCommand('Create a new field', $form, ['width' => '85vw']));
    }
    else {
      $response = $form;
    }
    return $response;
  }

}
