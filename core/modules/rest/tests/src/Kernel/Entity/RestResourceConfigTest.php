<?php

declare(strict_types=1);

namespace Drupal\Tests\rest\Kernel\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Drupal\KernelTests\KernelTestBase;
use Drupal\rest\Entity\RestResourceConfig;
use Drupal\rest\RestResourceConfigInterface;

/**
 * @group rest
 */
#[CoversClass(\Drupal\rest\Entity\RestResourceConfig::class)]
class RestResourceConfigTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'rest',
    'entity_test',
    'serialization',
    'basic_auth',
    'user',
  ];

  public function testCalculateDependencies() {
    $rest_config = RestResourceConfig::create([
      'plugin_id' => 'entity:entity_test',
      'granularity' => RestResourceConfigInterface::METHOD_GRANULARITY,
      'configuration' => [
        'GET' => [
          'supported_auth' => ['cookie'],
          'supported_formats' => ['json'],
        ],
        'POST' => [
          'supported_auth' => ['basic_auth'],
          'supported_formats' => ['json'],
        ],
      ],
    ]);

    $rest_config->calculateDependencies();
    $this->assertEquals(['module' => ['basic_auth', 'entity_test', 'serialization', 'user']], $rest_config->getDependencies());
  }

}
