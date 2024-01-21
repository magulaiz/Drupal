<?php

namespace Drupal\user;

use Drupal\Core\Config\Entity\DraggableListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\user\Event\RoleFilterEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Defines a class to build a listing of user role entities.
 *
 * @see \Drupal\user\Entity\Role
 */
class RoleListBuilder extends DraggableListBuilder {

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * RoleListBuilder constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entityType
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   */
  public function __construct(EntityTypeInterface $entityType,
                              EntityStorageInterface $storage,
                              MessengerInterface $messenger,
                              EventDispatcherInterface $event_dispatcher) {
    parent::__construct($entityType, $storage);
    $this->messenger = $messenger;
    if (is_null($event_dispatcher)) {
      @trigger_error('Calling ' . __METHOD__ . '() without the $eventDispatcher argument is deprecated in drupal:10.2.0 and will be required in drupal:11.0.0', E_USER_DEPRECATED);
      $event_dispatcher = \Drupal::service('event_dispatcher');
    }
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('messenger'),
      $container->get('event_dispatcher')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'user_admin_roles_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);

    if ($entity->hasLinkTemplate('edit-permissions-form')) {
      $operations['permissions'] = [
        'title' => t('Edit permissions'),
        'weight' => 20,
        'url' => $entity->toUrl('edit-permissions-form'),
      ];
    }
    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->messenger->addStatus($this->t('The role settings have been updated.'));
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    $roles = parent::load();
    $event = new RoleFilterEvent($roles);
    $this->eventDispatcher->dispatch($event);
    return $event->getRoles();
  }

}
