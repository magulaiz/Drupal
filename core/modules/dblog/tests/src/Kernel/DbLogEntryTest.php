<?php

namespace Drupal\Tests\dblog\Kernel;

use Drupal\dblog\Entity\DblogEntry;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests for the DblogEntry class.
 *
 * @group dblog
 */
class DbLogEntryTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['dblog', 'user'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installSchema('dblog', ['watchdog']);
  }

  /**
   * Tests links with non latin characters.
   */
  public function testNonLatinCharacters() {

    $link = 'hello-
      科州的小九寨沟绝美高山湖泊酱凉拌素鸡照烧鸡黄玫瑰
      科州的小九寨沟绝美高山湖泊酱凉拌素鸡照烧鸡黄玫瑰
      科州的小九寨沟绝美高山湖泊酱凉拌素鸡照烧鸡黄玫瑰
      科州的小九寨沟绝美高山湖泊酱凉拌素鸡照烧鸡黄玫瑰
      科州的小九寨沟绝美高山湖泊酱凉拌素鸡照烧鸡黄玫瑰
      科州的小九寨沟绝美高山湖泊酱凉拌素鸡照烧鸡黄玫瑰
      科州的小九寨沟绝美高山湖泊酱凉拌素鸡照烧鸡黄玫瑰
      科州的小九寨沟绝美高山湖泊酱凉拌素鸡照烧鸡黄玫瑰
      科州的小九寨沟绝美高山湖泊酱凉拌素鸡照烧鸡黄玫瑰
      科州的小九寨沟绝美高山湖泊酱凉拌素鸡照烧鸡黄玫瑰
      科州的小九寨沟绝美高山湖泊酱凉拌素鸡照烧鸡黄玫瑰
      科州的小九寨沟绝美高山湖泊酱凉拌素鸡照烧鸡黄玫瑰
      科州的小九寨沟绝美高山湖泊酱凉拌素鸡照烧鸡黄玫瑰
      科州的小九寨沟绝美高山湖泊酱凉拌素鸡照烧鸡黄玫瑰';

    \Drupal::logger('my_module')->warning('test', ['link' => $link]);

    $log = \Drupal::database()
      ->select('watchdog', 'w')
      ->fields('w', ['link'])
      ->condition('link', '', '<>')
      ->execute()
      ->fetchField();

    $this->assertEquals($log, $link);

    $log = DblogEntry::create(['link' => $link]);
    $this->assertEquals($log->getLink(), $link);
  }

  /**
   * Tests corrupted log entries can still display available data.
   */
  public function testDbLogCorrupted() {
    $dblog_formatter = \Drupal::service('dblog.formatter');
    // Check message with properly serialized data.
    $entry = DblogEntry::create([
      'message' => 'Sample message with placeholder: @placeholder',
      'variables' => serialize(['@placeholder' => 'test placeholder']),
    ]);

    $this->assertEquals('Sample message with placeholder: test placeholder', $entry->getFormattedMessage($dblog_formatter));

    $entry->variables = 'BAD SERIALIZED DATA';
    $formatted = $entry->getFormattedMessage($dblog_formatter);
    $this->assertEquals('Log data is corrupted and cannot be unserialized: Sample message with placeholder: @placeholder', $formatted);
  }

  /**
   * Tests serializing and unserializing against the `variables` field.
   */
  public function testSerialization() {
    $foo = new Foo();
    $random = $this->randomMachineName();
    \Drupal::logger('dblog_test')->info("$random @foo", [
      '@foo' => $foo,
    ]);
    $query = \Drupal::database()->select('watchdog');
    $query->addExpression('MAX([wid])');
    $last_id = $query->execute()->fetchField();
    $dblog = DblogEntry::load($last_id);
    // Make sure it's the right entry being saved.
    $this->assertEquals("$random @foo", $dblog->get('message')->value);
    $null_byte = chr(0);
    $variables = $dblog->get('variables')->value;
    // Get `variables` from saved entry, and it should contains Null byte(s).
    $this->assertNotFalse(strpos($variables, $null_byte));
    $variables = unserialize($variables, ['allowed_classes' => TRUE]);
    $this->assertEquals(3, $variables['@foo']->public);

  }

}

/**
 * A Foo class for testing.
 */
class Foo {

  /**
   * A private property.
   *
   * @var int
   */
  private $private = 1;

  /**
   * A protected property.
   *
   * @var int
   */
  protected $protected = 2;

  /**
   * A public property.
   *
   * @var int
   */
  public $public = 3;

}
