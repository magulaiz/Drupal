<?php

declare(strict_types=1);

namespace Drupal\Core\Condition\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;

/**
 * Defines a condition plugin attribute.
 *
 * Condition plugins provide generalized conditions for use in other
 * operations, such as conditional block placement.
 *
 * Plugin Namespace: Plugin\Condition
 *
 * For a working example, see \Drupal\user\Plugin\Condition\UserRole.
 *
 * @see \Drupal\Core\Condition\ConditionManager
 * @see \Drupal\Core\Condition\ConditionInterface
 * @see \Drupal\Core\Condition\ConditionPluginBase
 * @see block_api
 *
 * @ingroup plugin_api
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Condition extends Plugin {


}
