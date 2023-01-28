<?php

namespace Drupal\dblog\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\dblog\DblogEntryStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the database logging filter form.
 *
 * @internal
 */
class DblogFilterForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dblog_filter_form';
  }

  /**
   * Constructs a DblogFilterForm object.
   *
   * @param \Drupal\dblog\DblogEntryStorageInterface
   *   The dblog entry storage.
   */
  public function __construct(protected DblogEntryStorageInterface $dblogStorage) {}

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
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['filters'] = [
      '#type' => 'details',
      '#title' => $this->t('Filter log messages'),
      '#open' => TRUE,
    ];
    $session_filters = $this->getRequest()->getSession()->get('dblog_overview_filter', []);

    $types = $this->dblogStorage->messageTypes();
    $form['filters']['status']['type'] = [
      '#title' => $this->t('Type'),
      '#type' => 'select',
      '#multiple' => TRUE,
      '#size' => 8,
      '#options' => $types,
      '#access' => !empty($types),
    ];
    $form['filters']['status']['severity'] = [
      '#title' => $this->t('Severity'),
      '#type' => 'select',
      '#multiple' => TRUE,
      '#size' => 8,
      '#options' => RfcLogLevel::getLevels(),
    ];

    foreach (['type', 'severity'] as $key) {
      if (!empty($session_filters[$key])) {
        $form['filters']['status'][$key]['#default_value'] = $session_filters[$key];
      }
    }

    $form['filters']['actions'] = [
      '#type' => 'actions',
      '#attributes' => ['class' => ['container-inline']],
    ];
    $form['filters']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Filter'),
    ];
    if (!empty($session_filters)) {
      $form['filters']['actions']['reset'] = [
        '#type' => 'submit',
        '#value' => $this->t('Reset'),
        '#limit_validation_errors' => [],
        '#submit' => ['::resetForm'],
      ];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->isValueEmpty('type') && $form_state->isValueEmpty('severity')) {
      $form_state->setErrorByName('type', $this->t('You must select something to filter by.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $session_filters = $this->getRequest()->getSession()->get('dblog_overview_filter', []);
    foreach (['severity', 'type'] as $name) {
      if ($form_state->hasValue($name)) {
        $session_filters[$name] = $form_state->getValue($name);
      }
    }
    $this->getRequest()->getSession()->set('dblog_overview_filter', $session_filters);
  }

  /**
   * Resets the filter form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function resetForm(array &$form, FormStateInterface $form_state) {
    $this->getRequest()->getSession()->remove('dblog_overview_filter');
  }

}
