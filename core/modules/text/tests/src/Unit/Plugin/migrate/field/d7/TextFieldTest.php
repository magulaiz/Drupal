<?php

declare(strict_types=1);

namespace Drupal\Tests\text\Unit\Plugin\migrate\field\d7;

use PHPUnit\Framework\Attributes\CoversClass;
use Drupal\migrate\Row;
use Drupal\Tests\UnitTestCase;
use Drupal\text\Plugin\migrate\field\d7\TextField;

/**
 * @group text
 */
#[CoversClass(\Drupal\text\Plugin\migrate\field\d7\TextField::class)]
class TextFieldTest extends UnitTestCase {

  /**
   * @var \Drupal\migrate_drupal\Plugin\MigrateFieldInterface
   */
  protected $plugin;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->plugin = new TextField([], 'text', []);
  }

  /**
   * Data provider for testGetFieldFormatterType().
   */
  public static function getFieldFormatterTypeProvider() {
    return [
      ['text', 'text_plain', 'string'],
      ['text_long', 'text_default', 'basic_string'],
      ['text_long', 'text_plain', 'basic_string'],
    ];
  }

  /**
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
