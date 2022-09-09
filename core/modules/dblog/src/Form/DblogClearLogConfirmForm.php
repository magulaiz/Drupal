<?php

namespace Drupal\dblog\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\dblog\DblogEntryStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a confirmation form before clearing out the logs.
 *
 * @internal
 */
class DblogClearLogConfirmForm extends ConfirmFormBase {

  /**
   * The dblog entry storage service.
   *
   * @var \Drupal\dblog\DblogEntryStorageInterface
   */
  protected $dblogStorage;

  /**
   * Constructs a new DblogClearLogConfirmForm.
   *
   * @param \Drupal\dblog\DblogEntryStorageInterface $storage
   *   The dblog entry storage service.
   */
  public function __construct(DblogEntryStorageInterface $storage) {
    $this->dblogStorage = $storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('dblog')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dblog_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the recent logs?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('dblog.overview');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->getRequest()->getSession()->remove('dblog_overview_filter');
    $this->dblogStorage->deleteAll();
    $this->messenger()->addStatus($this->t('Database log cleared.'));
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
