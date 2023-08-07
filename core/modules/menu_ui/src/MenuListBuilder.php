<?php

namespace Drupal\menu_ui;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\menu_ui\Form\MenuFilterForm;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of menu entities.
 *
 * @see \Drupal\system\Entity\Menu
 * @see menu_entity_info()
 */
class MenuListBuilder extends ConfigEntityListBuilder {

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Constructs a new MenuListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Symfony\Component\HttpFoundation\Request $current_request
   *   The current request.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, Request $current_request, FormBuilderInterface $form_builder) {
    parent::__construct($entity_type, $storage);

    $this->currentRequest = $current_request;
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityIds() {
    $query = $this
      ->getStorage()
      ->getQuery()
      ->sort('label', 'ASC');

    $search = $this->currentRequest->query->get('search');
    if ($search) {
      $query->condition('label', $search, 'CONTAINS');
    }

    // Only add the pager if a limit is specified.
    if ($this->limit) {
      $query->pager($this->limit);
    }

    return $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['title'] = t('Title');
    $header['description'] = [
      'data' => t('Description'),
      'class' => [RESPONSIVE_PRIORITY_MEDIUM],
    ];
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['title'] = [
      'data' => $entity->label(),
      'class' => ['menu-label'],
    ];
    $row['description']['data'] = ['#markup' => $entity->getDescription()];
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);

    if (isset($operations['edit'])) {
      $operations['edit']['title'] = t('Edit menu');
      $operations['add'] = [
        'title' => t('Add link'),
        'weight' => 20,
        'url' => $entity->toUrl('add-link-form'),
        'query' => [
          'destination' => $entity->toUrl('edit-form')->toString(),
        ],
      ];
    }
    if (isset($operations['delete'])) {
      $operations['delete']['title'] = t('Delete menu');
    }
    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  protected function ensureDestination(Url $url) {
    // We don't want to add the destination URL here, as it means we get
    // redirected back to the list-builder after adding/deleting menu links from
    // a menu.
    return $url;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $keys = $this->currentRequest->query->get('search');
    $build['menu_filter_form'] = $this->formBuilder->getForm(MenuFilterForm::class, $keys);
    $build += parent::render();
    $build['#attached']['library'][] = "menu_ui/drupal.menu_ui.adminforms";
    return $build;
  }

}
