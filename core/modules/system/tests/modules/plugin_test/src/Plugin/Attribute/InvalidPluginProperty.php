<?php

declare(strict_types=1);

namespace Drupal\plugin_test\Plugin\Attribute;

use Drupal\Component\Plugin\Attribute\PluginProperty;

/**
 * Example of an invalid plugin property because it is in a module.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class InvalidPluginProperty extends PluginProperty {}
