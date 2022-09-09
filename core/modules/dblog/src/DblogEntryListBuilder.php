<?php

namespace Drupal\dblog;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Link;
use Drupal\dblog\Form\DblogFilterForm;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a class to build a listing of dblog entities.
 *
 * @see \Drupal\dblog\Entity\DblogEntry
 */
class DblogEntryListBuilder extends EntityListBuilder {

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
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The dblog formatter service.
   *
   * @var \Drupal\dblog\DblogFormatterInterface
   */
  protected $dblogFormatter;

  /**
   * Constructs a new DblogEntryListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Symfony\Component\HttpFoundation\Request $current_request
   *   The current request.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\dblog\DblogFormatterInterface $dblog_formatter
   *   The dblog formatter service.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\Core\Entity\EntityStorageInterface $user_storage
   *   The user storage service.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, Request $current_request, DateFormatterInterface $date_formatter, DblogFormatterInterface $dblog_formatter, FormBuilderInterface $form_builder, EntityStorageInterface $user_storage) {
    parent::__construct($entity_type, $storage);

    $this->currentRequest = $current_request;
    $this->formBuilder = $form_builder;
    $this->dateFormatter = $date_formatter;
    $this->dblogFormatter = $dblog_formatter;
    $this->userStorage = $user_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('date.formatter'),
      $container->get('dblog.formatter'),
      $container->get('form_builder'),
      $container->get('entity_type.manager')->getStorage('user'),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityIds() {
    $query = $this->getStorage()->getQuery()->accessCheck(TRUE);

    // Only add the pager if a limit is specified.
    if ($this->limit) {
      $query->pager($this->limit);
    }

    $session_filters = $this->currentRequest->getSession()->get('dblog_overview_filter', []);
    foreach (['type', 'severity'] as $filter) {
      if (!empty($session_filters[$filter])) {
        $query->condition($filter, $session_filters[$filter], 'IN');
      }
    }

    if ($this->currentRequest->query->has('order')) {
      // Allow the entity query to sort using the table header.
      $header = $this->buildHeader();
      $query->tableSort($header);
    }
    else {
      $query->sort('wid', 'DESC');
    }

    return $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [
      'icon' => [
        'data' => '',
        'specifier' => 'wid',
      ],
      'type' => [
        'data' => $this->t('Type'),
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
        'field' => 'type',
        'specifier' => 'type',
      ],
      'wid' => [
        'data' => $this->t('Date'),
        'field' => 'wid',
        'sort' => 'desc',
        'initial_click_sort' => 'desc',
        'class' => [RESPONSIVE_PRIORITY_LOW],
        'specifier' => 'wid',
      ],
      'message' => [
        'data' => $this->t('Message'),
        'specifier' => 'message',
      ],
      'uid' => [
        'data' => $this->t('User'),
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
        'specifier' => 'uid',
        'field' => 'uid',
      ],
    ];

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {

    $message = $entity->getFormattedMessage($this->dblogFormatter);
    $title = Unicode::truncate(Html::decodeEntities(strip_tags($message)), 256, TRUE, TRUE);
    $log_text = Unicode::truncate($title, 56, TRUE, TRUE);
    // The link generator will escape any unsafe HTML entities in the final
    // text.
    $message = Link::fromTextAndUrl($log_text, $entity->toUrl('canonical', [
      'attributes' => [
        // Provide a title for the link for useful hover hints. The
        // Attribute object will escape any unsafe HTML entities in the
        // final text.
        'title' => $title,
      ],
    ]))->toString();
    $username = [
      '#theme' => 'username',
      '#account' => $this->userStorage->load($entity->getUid()),
    ];

    $row['data']['icon'] = ['class' => ['severity-' . $entity->getSeverity()]];
    $row['data']['type']['data'] = $entity->getType();
    $row['data']['wid']['data'] = $this->dateFormatter->format($entity->getTimestamp(), 'short');
    $row['data']['message']['data'] = $message;
    $row['data']['uid']['data'] = $username;
    $row['data']['operations']['data'] = ['#markup' => $entity->getLink()];
    $row['class'] = [
      Html::getClass('dblog-' . $entity->getType()),
      Html::getClass('severity-' . $entity->getSeverity()),
    ];

    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build['dblog_filter_form'] = $this->formBuilder->getForm(DblogFilterForm::class);
    $build += parent::render();
    $build['table']['#attributes'] = ['id' => ['admin-dblog']];
    $build['table']['#empty'] = $this->t('No log messages available.');
    $build['table']['#attached']['library'] = ['dblog/drupal.dblog'];

    return $build;
  }

}
