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
  public function testEncodeDecode(array $data): void {
    $this->assertSame($data, Yaml::decode(Yaml::encode($data)));
  }

  /**
   * Tests decoding YAML node anchors.
   *
   * @covers ::decode
   * @dataProvider providerDecodeTests
   */
  public function testDecode($string, $data): void {
    $this->assertSame($data, Yaml::decode($string));
  }

  /**
   * Tests our encode settings.
   *
   * @covers ::encode
   */
  public function testEncode(): void {
    // cSpell:disable
    $this->assertSame('foo:
  bar: \'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sapien ex, venenatis vitae nisi eu, posuere luctus dolor. Nullam convallis\'
', Yaml::encode(['foo' => ['bar' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus sapien ex, venenatis vitae nisi eu, posuere luctus dolor. Nullam convallis']]));
    // cSpell:enable
  }

  /**
   * @covers ::getFileExtension
   */
  public function testGetFileExtension(): void {
    $this->assertSame('yml', Yaml::getFileExtension());
  }

  /**
   * Tests that invalid YAML throws an exception.
   *
   * @covers ::decode
   */
  public function testError(): void {
    $this->expectException(InvalidDataTypeException::class);
    Yaml::decode('foo: [ads');
  }

  /**
   * Ensures that php object support is disabled.
   *
   * @covers ::encode
   */
  public function testEncodeObjectSupportDisabled(): void {
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
  public function testDecodeObjectSupportDisabled(): void {
    $this->expectException(InvalidDataTypeException::class);
    $this->expectExceptionMessageMatches('/^Object support when parsing a YAML file has been disabled/');
    $yaml = <<<YAML
    obj: !php/object "O:8:\"stdClass\":1:{s:3:\"foo\";s:3:\"bar\";}"
    YAML;

    Yaml::decode($yaml);
  }

  /**
   * Tests decoding PHP constants.
   *
   * @see \Drupal\Tests\Component\Serialization\YamlTest::testObjectSupportDisabledPecl()
   */
  public function testConstants() {
    $yaml = <<<YAML
      foo:
        !php/const PHP_INT_MAX
      YAML;
    $this->assertSame(Yaml::decode($yaml)['foo'], PHP_INT_MAX);
  }

  /**
   * Tests that missing constants cause exception.
   */
  public function testUnDefinedConstant() {
    $this->expectExceptionMessage('The constant "DOES_NOT_EXIST" is not defined');
    $yaml = <<<YAML
      foo:
        !php/const DOES_NOT_EXIST
      YAML;
    Yaml::decode($yaml);
  }

  /**
   * Tests that enums can be encoded and parsed.
   */
  public function testEnums() {
    $data = [
      'foo' => EnumValue::Yes,
      'bar' => BackedEnumValue::Maybe,
    ];
    $yaml = Yaml::encode($data);

    $this->assertSame(<<<YAML
foo: !php/enum Drupal\Tests\Component\Serialization\EnumValue::Yes
bar: !php/enum Drupal\Tests\Component\Serialization\BackedEnumValue::Maybe

YAML, $yaml);

    // Test decoding of `!php/enum`-encoded enums.
    $decoded = Yaml::decode($yaml);
    $this->assertSame($decoded['foo'], EnumValue::Yes);
    $this->assertSame($decoded['bar'], BackedEnumValue::Maybe);

    // Test decoding of `!php/const`-encoded enums.
    $yaml_alternative = <<<YAML
foo: !php/const Drupal\Tests\Component\Serialization\EnumValue::Yes
bar: !php/const Drupal\Tests\Component\Serialization\BackedEnumValue::Maybe

YAML;
    $decoded = Yaml::decode($yaml_alternative);
    $this->assertSame($decoded['foo'], EnumValue::Yes);
    $this->assertSame($decoded['bar'], BackedEnumValue::Maybe);

    // Test decoding of `!php/enum`-encoded arrow values.
    $yaml_alternative = <<<YAML
bar: !php/enum Drupal\Tests\Component\Serialization\BackedEnumValue::Maybe->value

YAML;
    $decoded = Yaml::decode($yaml_alternative);
    $this->assertSame($decoded['bar'], BackedEnumValue::Maybe->value);
  }

}
