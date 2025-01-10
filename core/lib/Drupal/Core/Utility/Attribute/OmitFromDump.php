<?php

namespace Drupal\Core\Utility\Attribute;

/**
 * Attribute to mark properties to be omitted from debug output.
 *
 * This works with the \Drupal\Core\Utility\VarDumper handler for the Symfony
 * VarDumper component. In PHPUnit tests, this is enabled automatically. Outside
 * of tests, this needs to be enabled with the 'setup_var_dumper' setting in
 * settings.php.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class OmitFromDump {
}
