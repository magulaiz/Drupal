<?php

namespace Drupal\migrate\Exception;

use Drupal\migrate\MigrateException;

/**
 * To throw when migrate's status is not 'idle' when the migration starts.
 */
class MigrationBusyException extends MigrateException {

}
