<?php

namespace Drupal\workspaces\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for the workspace edit forms.
 */
class WorkspaceForm extends ContentEntityForm implements WorkspaceFormInterface {

  /**
   * The workspace entity.
   *
   * @var \Drupal\workspaces\WorkspaceInterface
   */
  protected $entity;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a new WorkspaceForm.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(EntityRepositoryInterface $entity_repository, MessengerInterface $messenger, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('messenger'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $workspace = $this->entity;

    if ($this->operation == 'edit') {
      $form['#title'] = $this->t('Edit workspace %label', ['%label' => $workspace->label()]);
    }

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $workspace = $this->entity;
    $workspace->setNewRevision(TRUE);
    $status = $workspace->save();

    $info = ['%info' => $workspace->label()];
    $context = ['@type' => $workspace->bundle(), '%info' => $workspace->label()];
    $logger = $this->logger('workspaces');

    if ($status == SAVED_UPDATED) {
      $logger->notice('@type: updated %info.', $context);
      $this->messenger->addMessage($this->t('Workspace %info has been updated.', $info));
    }
    else {
      $logger->notice('@type: added %info.', $context);
      $this->messenger->addMessage($this->t('Workspace %info has been created.', $info));
    }

    if ($workspace->id()) {
      $form_state->setValue('id', $workspace->id());
      $form_state->set('id', $workspace->id());

      $collection_url = $workspace->toUrl('collection');
      $redirect = $collection_url->access() ? $collection_url : Url::fromRoute('<front>');
      $form_state->setRedirectUrl($redirect);
    }
    else {
      $this->messenger->addError($this->t('The workspace could not be saved.'));
      $form_state->setRebuild();
    }
  }

}
