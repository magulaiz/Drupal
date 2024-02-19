<?php

declare(strict_types=1);

namespace Drupal\module_discovery_definition_template_test\ServiceDefinitionTemplate;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\module_discovery_attribute_service_test\Attribute\AttributeToService;

/**
 * Class for testing conversion to a service.
 */
#[AttributeToService]
final class TestServiceDefinitionTemplate {

  /**
   * Constructor is used to test autowire default from definition.
   */
  public function __construct(
    private readonly TimeInterface $time,
  ) {
  }

  /**
   * Get the time.
   */
  public function getTime(): string {
    return 'The time is ' . (new \DateTimeImmutable('@' . $this->time->getRequestTime()))->format('r');
  }

}
