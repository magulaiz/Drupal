<?php

namespace Drupal\Core\Entity\Query\Sql\pgsql;

use Drupal\pgsql\EntityQuery\Condition as PgsqlCondition;

@trigger_error('\Drupal\Core\Entity\Query\Sql\pgsql\Condition is deprecated in drupal:11.2.0 and is removed from drupal:12.0.0. The PostgreSQL override of the entity query has been moved to the pgsql module. See https://www.drupal.org/node/3488580', E_USER_DEPRECATED);

/**
 * Implements entity query conditions for PostgreSQL databases.
 *
 * @deprecated in drupal:11.2.0 and is removed from drupal:12.0.0. The
 *   PostgreSQL override of the entity query has been moved to the pgsql module.
 *
 * @see https://www.drupal.org/node/3488580
 */
class Condition extends PgsqlCondition {}
