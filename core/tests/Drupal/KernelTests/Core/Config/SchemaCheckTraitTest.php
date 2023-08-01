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

    // Test it is possible to mark any schema type as required (not nullable).
    $nulled_config_data = array_fill_keys(array_keys($config_data), NULL);
    $ret = $this->checkConfigSchema($this->typedConfig, 'config_test.types', $nulled_config_data);
    $this->assertEquals([
      // TRICKY: `_core` is added during installation even if it is absent from
      // core/modules/config/tests/config_test/config/install/config_test.dynamic.dotted.default.yml.
      // @see \Drupal\Core\Config\ConfigInstaller::createConfiguration()
      'config_test.types:_core' => 'variable type is NULL but applied schema class is Drupal\Core\Config\Schema\Mapping',
      '[_core] This value should not be null.',
      'config_test.types:array' => 'variable type is NULL but applied schema class is Drupal\Core\Config\Schema\Sequence',
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
    $ret = $this->checkConfigSchema($this->typedConfig, 'config_test.types', $config_data);
    $expected = [
      // Storage type check errors.
      // @see \Drupal\Core\Config\Schema\SchemaCheckTrait::checkValue()
      'config_test.types:new_key' => 'missing schema',
      'config_test.types:new_array' => 'missing schema',
      'config_test.types:boolean' => 'non-scalar value but not defined as an array (such as mapping or sequence)',
      // Validation constraints violations.
      // @see \Drupal\Core\TypedData\TypedDataInterface::validate()
      '0' => "[] 'new_key' is not a supported key.",
      '1' => "[] 'new_array' is not a supported key.",
      '2' => '[boolean] This value should be of the correct primitive type.',
    ];
    $this->assertEquals($expected, $ret);
  }

}
