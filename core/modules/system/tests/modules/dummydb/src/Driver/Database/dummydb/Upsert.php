<?php

declare(strict_types=1);

// cspell:ignore dummydb

namespace Drupal\dummydb\Driver\Database\dummydb;

use Drupal\Core\Database\Query\Upsert as QueryUpsert;

/**
 * DummyDB implementation of \Drupal\Core\Database\Query\Upsert.
 */
class Upsert extends QueryUpsert {}
