<?php

namespace Drupal\migrate\Exception;

use Drupal\migrate\MigrateException;

/**
 * To throw when the migration status is not 'idle' when it starts.
 */
class MigrationBusyException extends MigrateException {

}
