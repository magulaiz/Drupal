<?php

namespace Drupal\dblog\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\dblog\DblogEntryStorageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\dblog\DblogFormatterInterface;

/**
 * Controller to display most frequent log errors by type.
 */
class TopDblogMessagesController extends ControllerBase {

  /**
   * The dblog entry storage.
   */
  protected DblogEntryStorageInterface $dblogStorage;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('dblog.formatter'),
    );
  }

  /**
   * Constructs a TopDblogMessagesController object.
   *
   * @param \Symfony\Component\HttpFoundation\Request $currentRequest
   *   The current request.
   * @param \Drupal\dblog\DblogFormatterInterface $dblogFormatter
   *   The dblog formatter service.
   */
  public function __construct(protected Request $currentRequest, protected DblogFormatterInterface $dblogFormatter) {
    $this->dblogStorage = $this->entityTypeManager()->getStorage('dblog');
  }

  /**
   * Shows the most frequent log messages of a given event type.
   *
   * Messages are not truncated on this page because events detailed herein do
   * not have links to a detailed view.
   *
   * @param string $type
   *   Type of database log events to display (e.g., 'search').
   *
   * @return array
   *   A build array in the format expected by
   *   \Drupal\Core\Render\RendererInterface::render().
   */
  public function list(string $type) {
    $occurrences = $this->dblogStorage->mostFrequentLogEntries($type);

    $rows = [];
    foreach ($occurrences as $occurrence) {
      $rows[] = [
        $occurrence['count'],
        $occurrence['entry']->getFormattedMessage($this->dblogFormatter),
      ];
    }

    if (mb_strtolower($this->currentRequest->query->get('sort', 'desc')) == 'asc') {
      $rows = array_reverse($rows);
    }

    $header = [
      ['data' => $this->t('Count'), 'field' => 'count', 'sort' => 'desc'],
      ['data' => $this->t('Message')],
    ];
    $build['dblog_top_table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No log messages available.'),
    ];

    return $build;
  }

}
