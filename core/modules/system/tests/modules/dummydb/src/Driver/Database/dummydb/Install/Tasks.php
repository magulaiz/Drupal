<?php

declare(strict_types=1);

namespace Drupal\dummydb\Driver\Database\dummydb\Install;

use Drupal\Core\Database\Install as CoreTasks;

/**
 * Specifies installation tasks for DummyDB test database.
 */
class Tasks extends CoreTasks {

  /**
   * {@inheritdoc}
   */
  public function name() {
    return t('DummyDB');
  }

}
