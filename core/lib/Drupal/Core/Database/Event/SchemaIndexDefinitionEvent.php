<?php

namespace Drupal\Core\Database\Event;

use Drupal\Core\Database\Schema\Index;

/**
 * @todo
 */
class SchemaIndexDefinitionEvent extends DatabaseEvent {

  /**
   * Constructor.
   */
  public function __construct(
    public readonly Index $index,
  ) {
    parent::__construct();
  }

}
