<?php

namespace Drupal\migrate\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a migration condition plugin annotation object.
 *
 * Plugin Namespace: Plugin\migrate\condition
 *
 * @ingroup migration
 *
 * @Annotation
 */
class MigrateConditionPlugin extends Plugin {

  /**
   * A unique identifier for the condition plugin.
   *
   * @var string
   */
  public $id;

  /**
   * Array of required configuration keys.
   *
   * @var string[]
   */
  public $requires = [];

}
