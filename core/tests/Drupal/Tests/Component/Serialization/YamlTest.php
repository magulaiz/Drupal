<?php

declare(strict_types=1);

namespace Drupal\Tests\Component\Serialization;

use Drupal\Component\Serialization\Exception\InvalidDataTypeException;
use Drupal\Component\Serialization\Yaml;

/**
 * Tests the Yaml serialization implementation.
 *
 * @group Drupal
 * @group Serialization
 * @coversDefaultClass \Drupal\Component\Serialization\Yaml
 */
class YamlTest extends YamlTestBase {

  /**
   * Tests encoding and decoding basic data structures.
   *
   * @covers ::encode
   * @covers ::decode
   * @dataProvider providerEncodeDecodeTests
   */
  public function testEncodeDecode($data) {
    $this->assertSame($data, Yaml::decode(Yaml::encode($data)));
  }

  /**
   * Tests decoding YAML node anchors.
   *
   * @covers ::decode
   * @dataProvider providerDecodeTests
   */
  public function testDecode($string, $data) {
    $this->assertSame($data, Yaml::decode($string));
  }

  /**
   * Tests our encode settings.
   *
   * @covers ::encode
   */
  public function testEncode() {
    // cSpell:disable
    $this->assertSame('foo:
  bar: \'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sapien ex, venenatis vitae nisi eu, posuere luctus dolor. Nullam convallis\'
', Yaml::encode(['foo' => ['bar' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sapien ex, venenatis vitae nisi eu, posuere luctus dolor. Nullam convallis']]));
    // cSpell:enable
  }

  /**
   * @covers ::getFileExtension
   */
  public function testGetFileExtension() {
    $this->assertSame('yml', Yaml::getFileExtension());
  }

  /**
   * Tests that invalid YAML throws an exception.
   *
   * @covers ::decode
   */
  public function testError() {
    $this->expectException(InvalidDataTypeException::class);
    Yaml::decode('foo: [ads');
  }

  /**
   * Ensures that php object support is disabled.
   *
   * @covers ::encode
   */
  public function testEncodeObjectSupportDisabled() {
    $this->expectException(InvalidDataTypeException::class);
    $this->expectExceptionMessage('Object support when dumping a YAML file has been disabled.');
    $object = new \stdClass();
    $object->foo = 'bar';
    Yaml::encode([$object]);
  }

  /**
   * Ensures that decoding PHP objects does not work in Symfony.
   *
   * @covers ::decode
   */
  public function testDecodeObjectSupportDisabled() {
    $this->expectException(InvalidDataTypeException::class);
    $this->expectExceptionMessageMatches('/^Object support when parsing a YAML file has been disabled/');
    $yaml = <<<YAML
    obj: !php/object "O:8:\"stdClass\":1:{s:3:\"foo\";s:3:\"bar\";}"
    YAML;

    Yaml::decode($yaml);
   }

  /**
   * Ensures that decoding php objects does not work in Symfony.
   *
   * @requires extension yaml
   *
   * @see \Drupal\Tests\Component\Serialization\YamlTest::testObjectSupportDisabledPecl()
   */
  public function testConstants() {
    $yaml = <<<YAML
foo:
  !php/const PHP_INT_MAX
YAML;
    $symfony = YamlSymfony::decode($yaml);
    $yaml = YamlPecl::decode($yaml);
    $this->assertSame($symfony['foo'], PHP_INT_MAX);
    $this->assertSame($yaml['foo'], PHP_INT_MAX);
  }

  /**
   * Tests that enums can be encoded by Symfony and parsed by PECL and Symfony.
   */
  public function testEnums() {
    $data = [
      'foo' => EnumValue::Yes,
      'bar' => BackedEnumValue::Maybe,
    ];
    $yaml = YamlSymfony::encode($data);

    $symfony = YamlSymfony::decode($yaml);
    $yaml = YamlPecl::decode($yaml);
    $this->assertSame($symfony['foo'], EnumValue::Yes);
    $this->assertSame($symfony['bar'], BackedEnumValue::Maybe);
    $this->assertSame($yaml['foo'], EnumValue::Yes);
    $this->assertSame($yaml['bar'], BackedEnumValue::Maybe);
  }

}
