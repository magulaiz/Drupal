<?php

declare(strict_types=1);

namespace Drupal\module_discovery_attribute_method_service_test\AttributeToService;

use Drupal\module_discovery_attribute_service_test\Attribute\AttributeToService;

/**
 * Class for testing conversion to a service.
 *
 * Multiple methods are used to test only one service is created.
 */
final class TestMethodService {

  #[AttributeToService]
  public function foo(): void {
  }

  #[AttributeToService]
  public function bar(): void {
  }

}
