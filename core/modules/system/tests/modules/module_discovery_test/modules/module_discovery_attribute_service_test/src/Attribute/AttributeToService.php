<?php

declare(strict_types=1);

namespace Drupal\module_discovery_attribute_service_test\Attribute;

/**
 * Service tag to autoconfigure classes or methods.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class AttributeToService {

}
