<?php

namespace Drupal\migrate\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a migration process condition plugin annotation object.
 *
 * Plugin Namespace: Plugin\migrate\process\condition
 *
 * @ingroup migration
 *
 * @Annotation
 */
class MigrateProcessConditionPlugin extends Plugin {

  /**
   * A unique identifier for the process condition plugin.
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
