<?php

declare(strict_types=1);

namespace Drupal\dummydb\Driver\Database\dummydb\Install;

use Drupal\Core\Database\Install\Tasks as CoreTasks;

/**
 * Specifies fake installation tasks for test.
 */
class Tasks extends CoreTasks {

  /**
   * {@inheritdoc}
   */
  public function name() {
    return t('DummyDB');
  }

}
