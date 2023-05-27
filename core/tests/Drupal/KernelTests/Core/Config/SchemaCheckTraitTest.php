<?php

namespace Drupal\KernelTests\Core\Config;

use Drupal\Core\Config\Schema\SchemaCheckTrait;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the functionality of SchemaCheckTrait.
 *
 * @group config
 */
class SchemaCheckTraitTest extends KernelTestBase {

  use SchemaCheckTrait;

  /**
   * The typed config manager.
   *
   * @var \Drupal\Core\Config\TypedConfigManagerInterface
   */
  protected $typedConfig;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['config_test', 'config_schema_test'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig(['config_test', 'config_schema_test']);
    $this->typedConfig = \Drupal::service('config.typed');
  }

  /**
   * Tests \Drupal\Core\Config\Schema\SchemaCheckTrait.
   */
  public function testTrait() {
    // Test a non existing schema.
    $ret = $this->checkConfigSchema($this->typedConfig, 'config_schema_test.noschema', $this->config('config_schema_test.noschema')->get());
    $this->assertFalse($ret);

    // Test an existing schema with valid data.
    $config_data = $this->config('config_test.types')->get();
    $ret = $this->checkConfigSchema($this->typedConfig, 'config_test.types', $config_data);
    $this->assertTrue($ret);

    // Test all types are nullable.
    $nulled_config_data = array_fill_keys(array_keys($config_data), NULL);
    $nulled_config_data['_core'] = $config_data['_core'];
    $ret = $this->checkConfigSchema($this->typedConfig, 'config_test.types', $nulled_config_data);
    $this->assertEquals([
      '[array] This value should not be null.',
      '[boolean] This value should not be null.',
      '[exp] This value should not be null.',
      '[float] This value should not be null.',
      '[float_as_integer] This value should not be null.',
      '[hex] This value should not be null.',
      '[int] This value should not be null.',
      '[string] This value should not be null.',
      '[string_int] This value should not be null.',
    ], $ret);

    // Add a new key, a new array and overwrite boolean with array to test the
    // error messages.
    $config_data = ['new_key' => 'new_value', 'new_array' => []] + $config_data;
    $config_data['boolean'] = [];
    // Simulate that this is not running in a test: simulate what end users
    // would see.
    // @see ::testDeprecationForNewKeysInTests()
    $config_data['_core']['test'] = FALSE;
    $ret = $this->checkConfigSchema($this->typedConfig, 'config_test.types', $config_data);
    $expected = [
      'config_test.types:new_key' => 'missing schema',
      'config_test.types:new_array' => 'missing schema',
      'config_test.types:boolean' => 'non-scalar value but not defined as an array (such as mapping or sequence)',
      0 => "[] 'new_key' is not a supported key.",
      1 => "[] 'new_array' is not a supported key.",
      2 => "[boolean] This value should be of the correct primitive type.",
    ];
    $this->assertEquals($expected, $ret);

    // Omit all data, this should trigger validation errors for required keys
    // missing. No matter whether the value is nullable or not.
    $config_data = [];
    $ret = $this->checkConfigSchema($this->typedConfig, 'config_test.types', $config_data);
    $expected = [
      "[] 'array' is a required key.",
      "[] 'boolean' is a required key.",
      "[] 'exp' is a required key.",
      "[] 'float' is a required key.",
      "[] 'float_as_integer' is a required key.",
      "[] 'hex' is a required key.",
      "[] 'int' is a required key.",
      "[] 'string' is a required key.",
      "[] 'string_int' is a required key.",
      "[] 'nullable_array' is a required key.",
      "[] 'nullable_boolean' is a required key.",
      "[] 'nullable_exp' is a required key.",
      "[] 'nullable_float' is a required key.",
      "[] 'nullable_float_as_integer' is a required key.",
      "[] 'nullable_hex' is a required key.",
      "[] 'nullable_int' is a required key.",
      "[] 'nullable_octal' is a required key.",
      "[] 'nullable_string' is a required key.",
      "[] 'nullable_string_int' is a required key.",
    ];
    $this->assertEquals($expected, $ret);
  }

  /**
   * @group legacy
   */
  public function testDeprecationForNewKeysInTests(): void {
    // Test an existing schema with valid data.
    $config_data = $this->config('config_test.types')->get();
    $ret = $this->checkConfigSchema($this->typedConfig, 'config_test.types', $config_data);
    $this->assertTrue($ret);

    // Add a new key, a new array and overwrite boolean with array to test the
    // error messages.
    $config_data = ['new_key' => 'new_value', 'new_array' => []] + $config_data;
    $config_data['boolean'] = [];

    $this->expectDeprecation("Unsilenced deprecation: The 'config_test.types' configuration contains invalid keys at the property path ''. The following keys are either invalid or only exist in newer versions of the config schema: 'new_key', 'new_array'.");
    $this->checkConfigSchema($this->typedConfig, 'config_test.types', $config_data);
    $this->assertTrue(TRUE);
  }

}
