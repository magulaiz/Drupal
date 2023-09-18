<?php

declare(strict_types=1);

namespace Drupal\Tests\Core\TypedData;

use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\MapDataDefinition;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\Core\TypedData\DataDefinition
 * @group TypedData
 * @group legacy
 */
class DataDefinitionLegacyTest extends UnitTestCase {

  /**
   * Tests the BC layer for deprecated ArrayAccess methods.
   *
   * @covers ::offsetExists
   * @covers ::offsetGet
   * @covers ::offsetSet
   * @covers ::offsetUnset
   */
  public function testArrayAccessBC(): void {
    $definition = new DataDefinition(['label' => 'Label']);
    $this->expectDeprecation('Using array access method Drupal\Core\TypedData\DataDefinition::offsetExists(label) is deprecated in drupal:11.1.0 and is removed from drupal:12.0.0. Use the relevant getter or toArray() instead. See https://www.drupal.org/node/3388070');
    $this->expectDeprecation('Using array access method Drupal\Core\TypedData\DataDefinition::offsetGet(label) is deprecated in drupal:11.1.0 and is removed from drupal:12.0.0. Use the relevant getter or toArray() instead. See https://www.drupal.org/node/3388070');
    $this->assertTrue(isset($definition['label']));
    $this->assertEquals($definition->getLabel(), $definition['label']);

    $mapping = [
      'nullable' => TRUE,
    ];
    $definition = new MapDataDefinition([
      'mapping' => $mapping,
    ]);
    $this->expectDeprecation('Using array access method Drupal\Core\TypedData\MapDataDefinition::offsetExists(mapping) is deprecated in drupal:11.1.0 and is removed from drupal:12.0.0. Use the relevant getter or toArray() instead. See https://www.drupal.org/node/3388070');
    $this->expectDeprecation('Using array access method Drupal\Core\TypedData\MapDataDefinition::offsetUnset(mapping) is deprecated in drupal:11.1.0 and is removed from drupal:12.0.0. No direct replacement. See https://www.drupal.org/node/3388070');
    $this->expectDeprecation('Using array access method Drupal\Core\TypedData\MapDataDefinition::offsetSet(mapping) is deprecated in drupal:11.1.0 and is removed from drupal:12.0.0. Use the relevant getter or setRawDefinition() instead. See https://www.drupal.org/node/3388070');
    $this->expectDeprecation('Using array access method Drupal\Core\TypedData\MapDataDefinition::offsetGet(mapping) is deprecated in drupal:11.1.0 and is removed from drupal:12.0.0. Use the relevant getter or toArray() instead. See https://www.drupal.org/node/3388070');
    $this->assertTrue(isset($definition['mapping']));
    unset($definition['mapping']);
    $this->assertArrayNotHasKey('mapping', $definition->toArray());
    $definition['mapping'] = $mapping;
    $this->assertArrayHasKey('nullable', $definition['mapping']);
  }

}
