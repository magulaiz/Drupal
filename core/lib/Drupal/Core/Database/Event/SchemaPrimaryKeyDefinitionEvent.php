<?php

namespace Drupal\Core\Database\Event;

use Drupal\Core\Database\Schema\PrimaryKey;

/**
 * @todo
 */
class SchemaPrimaryKeyDefinitionEvent extends DatabaseEvent {

  /**
   * Constructor.
   */
  public function __construct(
    public readonly PrimaryKey $primaryKey,
  ) {
    parent::__construct();
  }

}
