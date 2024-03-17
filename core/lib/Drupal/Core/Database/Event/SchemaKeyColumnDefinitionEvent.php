<?php

namespace Drupal\Core\Database\Event;

use Drupal\Core\Database\Schema\KeyColumn;

/**
 * @todo
 */
class SchemaKeyColumnDefinitionEvent extends DatabaseEvent {

  /**
   * Constructor.
   */
  public function __construct(
    public readonly KeyColumn $keyColumn,
  ) {
    parent::__construct();
  }

}
