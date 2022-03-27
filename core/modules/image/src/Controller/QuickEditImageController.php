<?php

namespace Drupal\image\Controller;

@trigger_error(__NAMESPACE__ . '\QuickEditImageController is deprecated in drupal:9.4.0 and is removed from drupal:10.0.0. Instead, use \Drupal\quickedit\QuickEditImageController. See https://www.drupal.org/node/3271848', E_USER_DEPRECATED);

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for our image routes.
 */
class QuickEditImageController extends ControllerBase {

  /**
   * Stores The Quick Edit tempstore.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  protected $tempStore;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The image factory.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

  /**
   * The entity display repository service.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Constructs a new QuickEditImageController.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Core\Image\ImageFactory $image_factory
   *   The image factory.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository service.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system.
   */
  public function __construct(RendererInterface $renderer, ImageFactory $image_factory, PrivateTempStoreFactory $temp_store_factory, EntityDisplayRepositoryInterface $entity_display_repository, FileSystemInterface $file_system) {
    $this->renderer = $renderer;
    $this->imageFactory = $image_factory;
    $this->tempStore = $temp_store_factory->get('quickedit');
    $this->entityDisplayRepository = $entity_display_repository;
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer'),
      $container->get('image.factory'),
      $container->get('tempstore.private'),
      $container->get('entity_display.repository'),
      $container->get('file_system')
    );
  }

  /**
   * Returns JSON representing the new file upload, or validation errors.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity of which an image field is being rendered.
   * @param string $field_name
   *   The name of the (image) field that is being rendered
   * @param string $langcode
   *   The language code of the field that is being rendered.
   * @param string $view_mode_id
   *   The view mode of the field that is being rendered.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   */
  public function upload(EntityInterface $entity, $field_name, $langcode, $view_mode_id) {
    $controller = new QuickEditImageController(
      $this->renderer,
      $this->imageFactory,
      $this->tempStore,
      $this->entityDisplayRepository,
      $this->fileSystem
    );

    return $controller->upload($entity, $field_name, $langcode, $view_mode_id);
  }

  /**
   * Returns JSON representing an image field's metadata.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity of which an image field is being rendered.
   * @param string $field_name
   *   The name of the (image) field that is being rendered
   * @param string $langcode
   *   The language code of the field that is being rendered.
   * @param string $view_mode_id
   *   The view mode of the field that is being rendered.
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   *   The JSON response.
   */
  public function getInfo(EntityInterface $entity, $field_name, $langcode, $view_mode_id) {
    $controller = new QuickEditImageController(
      $this->renderer,
      $this->imageFactory,
      $this->tempStore,
      $this->entityDisplayRepository,
      $this->fileSystem
    );

    return $controller->getInfo($entity, $field_name, $langcode, $view_mode_id);
  }

  /**
   * Returns JSON representing the current state of the field.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity of which an image field is being rendered.
   * @param string $field_name
   *   The name of the (image) field that is being rendered
   * @param string $langcode
   *   The language code of the field that is being rendered.
   *
   * @return \Drupal\image\Plugin\Field\FieldType\ImageItem
   *   The field for this request.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   *   Throws an exception if the request is invalid.
   */
  protected function getField(EntityInterface $entity, $field_name, $langcode) {
    $controller = new QuickEditImageController(
      $this->renderer,
      $this->imageFactory,
      $this->tempStore,
      $this->entityDisplayRepository,
      $this->fileSystem
    );

    return $controller->getField($entity, $field_name, $langcode);
  }

}
