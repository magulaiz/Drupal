<?php

declare(strict_types=1);

namespace Drupal\Tests\Component\Plugin;

use Drupal\Component\Plugin\Definition\PluginDefinition;
use Drupal\Component\Plugin\PluginBase;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

/**
 * @group Plugin
 * @coversDefaultClass \Drupal\Component\Plugin\PluginInspectionTrait
 */
class PluginInspectionTraitTest extends TestCase {

  use ExpectDeprecationTrait;

  /**
   * @covers ::getPluginDefinition
   * @group legacy
   *
   * @dataProvider providerTestDeprecated
   */
  public function testDeprecated($plugin_definition, $deprecation_message) {
    $this->expectDeprecation($deprecation_message);
    $this->getMockForAbstractClass(PluginBase::class, [
      [],
      'plugin_id',
      $plugin_definition,
    ]);
  }

  /**
   * Provides data for testDeprecated.
   */
  public function providerTestDeprecated() {
    $message = 'This is a deprecation message for PluginInspectionTraitTest.';
    $plugin_definition = $this->getMockBuilder(LegacyPluginDefinition::class)
      ->getMock();
    $plugin_definition->deprecationMessage = $message;

    $definition_with_additional = $this->getMockBuilder(LegacyPluginDefinitionAdditional::class)
      ->onlyMethods(['get'])
      ->getMockForAbstractClass();
    $definition_with_additional->method('get')
      ->with('additional')
      ->willReturn(['deprecation_message' => $message]);

    return [
      'definition is an array' => [
        ['value', ['key' => 'value'], 'deprecation_message' => $message],
        $message,
      ],
      'definition is an object' => [
        $plugin_definition,
        $message,
      ],
      'definition is an object with additional' => [
        $definition_with_additional,
        $message,
      ],
    ];
  }

}

class LegacyPluginDefinition extends PluginDefinition {
  public ?string $deprecationMessage;

}

class LegacyPluginDefinitionAdditional extends PluginDefinition {
  public array $additional = [];

  public function get($property) {
    if (property_exists($this, $property)) {
      $value = $this->{$property} ?? NULL;
    }
    else {
      $value = $this->additional[$property] ?? NULL;
    }
    return $value;
  }

}
