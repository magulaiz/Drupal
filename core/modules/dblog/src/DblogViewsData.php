<?php

namespace Drupal\dblog;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the dblog entries entity type.
 */
class DblogViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['watchdog']['table']['wizard_id'] = 'watchdog';

    $data['watchdog']['type'] = [
      'title' => $this->t('Type'),
      'help' => $this->t('The type of the log entry, for example "user" or "page not found".'),
      'field' => [
        'id' => 'standard',
      ],
      'argument' => [
        'id' => 'string',
      ],
      'filter' => [
        'id' => 'dblog_types',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ];

    $data['watchdog']['message'] = [
      'title' => $this->t('Message'),
      'help' => $this->t('The actual message of the log entry.'),
      'field' => [
        'id' => 'dblog_message',
      ],
      'argument' => [
        'id' => 'string',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ];

    $data['watchdog']['variables'] = [
      'title' => $this->t('Variables'),
      'help' => $this->t('The variables of the log entry in a serialized format.'),
      'field' => [
        'id' => 'serialized',
        'click sortable' => FALSE,
      ],
      'argument' => [
        'id' => 'string',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ];

    $data['watchdog']['severity'] = [
      'title' => $this->t('Severity level'),
      'help' => $this->t('The severity level of the event; ranges from 0 (Emergency) to 7 (Debug).'),
      'field' => [
        'id' => 'standard',
        'options callback' => 'Drupal\Core\Logger\RfcLogLevel::getLevels',
      ],
      'filter' => [
        'id' => 'in_operator',
        'options callback' => 'Drupal\Core\Logger\RfcLogLevel::getLevels',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ];

    $data['watchdog']['link'] = [
      'title' => $this->t('Operations'),
      'help' => $this->t('Operation links for the event.'),
      'field' => [
        'id' => 'dblog_operations',
      ],
      'argument' => [
        'id' => 'string',
      ],
      'filter' => [
        'id' => 'string',
      ],
      'sort' => [
        'id' => 'standard',
      ],
    ];

    return $data;
  }

}
