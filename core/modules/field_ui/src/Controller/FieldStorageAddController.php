<?php

namespace Drupal\field_ui\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller for building the field storage instance form.
 */
class FieldStorageAddController extends ControllerBase {

  /**
   * The tempstore object.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStore;

  /**
   * FieldStorageAddController constructor.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory) {
    $this->tempStore = $temp_store_factory->get('field_ui');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private')
    );
  }

  /**
   * Build the field storage instance form.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $field_name
   *   The name of the field to create.
   * @param string $bundle
   *   The bundle where the field is being created.
   *
   * @return array
   *   The field storage instance form.
   */
  public function storageAddConfigureForm($entity_type, $field_name, $bundle) {
    $temp_storage = $this->tempStore->get($this->currentUser()->id() . ':' . $entity_type . ':' . $field_name);
    if (!$temp_storage) {
      throw new NotFoundHttpException();
    }

    return $this->entityFormBuilder()->getForm($temp_storage['field_storage'], 'edit', [
      'entity_type_id' => $entity_type,
      'bundle' => $bundle,
    ]);
  }

}
