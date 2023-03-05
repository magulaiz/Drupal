<?php

namespace Drupal\Tests\migrate_drupal\Unit\field\d7;

use Drupal\migrate\Row;
use Drupal\Tests\UnitTestCase;
use Drupal\migrate_drupal\Plugin\migrate\field\d7\TextField;

/**
 * @coversDefaultClass \Drupal\migrate_drupal\Plugin\migrate\field\d7\TextField
 * @group migrate_drupal
 */
class TextFieldTest extends UnitTestCase {

  /**
   * @var \Drupal\migrate_drupal\Plugin\MigrateFieldInterface
   */
  protected $plugin;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->plugin = new TextField([], 'text', []);
  }

  /**
   * Data provider for testGetFieldFormatterType().
   */
  public function getFieldFormatterTypeProvider() {
    return [
      ['text', 'text_plain', 'string'],
      ['text_long', 'text_default', 'basic_string'],
      ['text_long', 'text_plain', 'basic_string'],
    ];
  }

  /**
   * @covers ::getFieldFormatterType
   * @covers ::getFieldType
   * @dataProvider getFieldFormatterTypeProvider
   */
  public function testGetFieldFormatterType($type, $formatter_type, $expected) {
    $row = new Row();
    $row->setSourceProperty('type', $type);
    $row->setSourceProperty('formatter/type', $formatter_type);
    $row->setSourceProperty('instances', [
      [
        'data' => serialize([
          'settings' => [
            'text_processing' => '0',
          ],
        ]),
      ],
    ]);
    $this->assertEquals($expected, $this->plugin->getFieldFormatterType($row));
  }

}
