<?php

namespace Drupal\Core\Utility;

/**
 * Attribute to mark properties to be omitted from dump() CLI output.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class OmitFromDump {
}
