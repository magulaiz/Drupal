<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Schema;

use Drupal\Core\Database\SchemaDefinition\KeyColumn as KeyColumnDefinition;

/**
 * @todo
 */
class KeyColumn extends SchemaElementBase {

  public string $name;
  public ?int $length;

}
