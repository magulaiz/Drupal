<?php

declare(strict_types=1);

namespace Drupal\Core\Database\Schema;

use Drupal\Core\Database\SchemaDefinition\SchemaDefinitionInterface;

/**
 * @todo
 */
abstract class SchemaElementBase implements SchemaElementInterface {

  final public function __construct(
    public readonly SchemaDefinitionInterface $definition,
  ) {
  }

}
