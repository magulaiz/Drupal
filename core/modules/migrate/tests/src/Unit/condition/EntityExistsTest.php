<?php

namespace Drupal\Tests\migrate\Unit\condition;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\migrate\condition\EntityExists;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the entity_exists condition plugin.
 *
 * @group migrate
 * @coversDefaultClass \Drupal\migrate\Plugin\migrate\condition\EntityExists
 */
class EntityExistsTest extends UnitTestCase {

  /**
   * @covers ::row
   * @covers ::process
   * @dataProvider providerTestEvaluate
   */
  public function testEvaluate($source, $load, $configuration, $expected) {
    $row = $this->getMockBuilder('Drupal\migrate\Row')
      ->disableOriginalConstructor()
      ->getMock();
    $storage = $this->getMockBuilder('\Drupal\Core\Entity\EntityStorageInterface')
      ->getMock();
    $storage->expects($this->once())
      ->method('load')
      ->willReturnMap([[$source, $load]]);
    $entity_type_manager = $this->getMockBuilder('\Drupal\Core\Entity\EntityTypeManagerInterface')
      ->getMock();
    $entity_type_manager->expects($this->once())
      ->method('getStorage')
      ->willReturnMap([[$configuration['entity_type'], $storage]]);

    $condition = new EntityExists($configuration, 'entity_exists', [], $entity_type_manager);
    $this->assertSame($expected, $condition->evaluate($source, $row));
  }

  /**
   * Data provider for ::testEvaluate().
   */
  public function providerTestEvaluate() {
    $mock_entity = $this->getMockBuilder('\Drupal\Core\Entity\EntityInterface')->getMock();
    return [
      [
        'source' => 123,
        'load' => NULL,
        'configuration' => [
          'entity_type' => 'foo',
        ],
        'expected' => FALSE,
      ],
      [
        'source' => 123,
        'load' => $mock_entity,
        'configuration' => [
          'entity_type' => 'foo',
        ],
        'expected' => TRUE,
      ],
    ];
  }

  /**
   * @covers ::evaluate
   */
  public function testEvaluateExceptions() {
    $row = $this->getMockBuilder('Drupal\migrate\Row')
      ->disableOriginalConstructor()
      ->getMock();
    $storage = $this->getMockBuilder('\Drupal\Core\Entity\EntityStorageInterface')
      ->getMock();
    $entity_type_manager = $this->getMockBuilder('\Drupal\Core\Entity\EntityTypeManagerInterface')
      ->getMock();
    $entity_type_manager->expects($this->once())
      ->method('getStorage')
      ->willReturn($storage);

    $configuration = [
      'entity_type' => 'foo',
    ];
    $condition = new EntityExists($configuration, 'entity_exists', [], $entity_type_manager);

    $this->expectException(MigrateException::class);
    $this->expectExceptionMessage("The source value for entity_exists must be an integer or a string.");
    $condition->evaluate(['an array'], $row);
  }

  /**
   * @covers ::__construct
   */
  public function testConstructorExceptions() {
    $row = $this->getMockBuilder('Drupal\migrate\Row')
      ->disableOriginalConstructor()
      ->getMock();
    $entity_type_manager = $this->getMockBuilder('\Drupal\Core\Entity\EntityTypeManagerInterface')
      ->getMock();
    $entity_type_manager->expects($this->once())
      ->method('getStorage')
      ->willThrowException(new PluginNotFoundException('foo'));

    $configuration = [
      'entity_type' => 'foo',
    ];

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('The entity_type configured for entity_exists could not be loaded.');
    $condition = new EntityExists($configuration, 'entity_exists', [], $entity_type_manager);
  }

}
