<?php

namespace Drupal\Tests\mongodb\Kernel;

use Drupal\Core\Database\Database;
use Drupal\Core\Database\SchemaException;
use Drupal\Core\Database\SchemaObjectDoesNotExistException;
use Drupal\Core\Database\SchemaObjectExistsException;

/**
 * Tests MongoDB base/embedded table creation and modification via the schema API.
 *
 * @group MongoDB
 * @coversDefaultClass \Drupal\mongodb\Driver\Schema
 */
class SchemaTableTest extends SchemaTestBase {

  /**
   * An array with the validation data for base table test_table1 and the embedded table test_table2.
   *
   * @var array
   */
  protected $embedded_validation_base_1_embedded_2 = ['$and' => [
    ['id' => ['$type' => 'int']],
    ['id' => ['$exists' => TRUE]],
    ['test_field' => ['$type' => 'int']],
    ['test_field' => ['$exists' => TRUE]],
    ['test_field_string' => ['$type' => 'string']],
    ['test_field_string' => ['$exists' => TRUE]],
    ['$or' => [
      ['test_field_string_ascii' => ['$type' => 'string']],
      ['test_field_string_ascii' => ['$exists' => FALSE]]
    ]],
    ['$or' => [
      ['$and' => [
        ['test_table2.id' => ['$exists' => FALSE]],
        ['test_table2.test_field_int_null' => ['$exists' => FALSE]],
        ['test_table2.test_field_int_not_null' => ['$exists' => FALSE]],
        ['test_table2.test_field_int_default' => ['$exists' => FALSE]],
        ['test_table2.test_field_float_null' => ['$exists' => FALSE]],
        ['test_table2.test_field_float_not_null' => ['$exists' => FALSE]],
        ['test_table2.test_field_float_default' => ['$exists' => FALSE]],
        ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]],
        ['test_table2.test_field_numeric_not_null' => ['$exists' => FALSE]],
        ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]],
      ]],
      ['$and' => [
        ['test_table2.id' => ['$type' => 'int']],
        ['test_table2.id' => ['$exists' => TRUE]],
        ['test_table2.id' => ['$gte' => 0]],
        ['$or' => [
          ['test_table2.test_field_int_null' => ['$type' => 'int']],
          ['test_table2.test_field_int_null' => ['$exists' => FALSE]]
        ]],
        ['test_table2.test_field_int_not_null' => ['$type' => 'int']],
        ['test_table2.test_field_int_not_null' => ['$exists' => TRUE]],
        ['$or' => [
          ['test_table2.test_field_int_default' => ['$type' => 'int']],
          ['test_table2.test_field_int_default' => ['$exists' => FALSE]]
        ]],
        ['$or' => [
          ['test_table2.test_field_float_null' => ['$type' => 'double']],
          ['test_table2.test_field_float_null' => ['$exists' => FALSE]]
        ]],
        ['test_table2.test_field_float_not_null' => ['$type' => 'double']],
        ['test_table2.test_field_float_not_null' => ['$exists' => TRUE]],
        ['$or' => [
          ['test_table2.test_field_float_default' => ['$type' => 'double']],
          ['test_table2.test_field_float_default' => ['$exists' => FALSE]]
        ]],
        ['$or' => [
          ['test_table2.test_field_numeric_null' => ['$type' => 'decimal']],
          ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]]
        ]],
        ['test_table2.test_field_numeric_not_null' => ['$type' => 'decimal']],
        ['test_table2.test_field_numeric_not_null' => ['$exists' => TRUE]],
        ['$or' => [
          ['test_table2.test_field_numeric_default' => ['$type' => 'decimal']],
          ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]]
        ]],
      ]],
    ]],
  ]];

  /**
   * An array with the validation data for base table test_table1 and the embedded table test_table3.
   *
   * @var array
   */
  protected $embedded_validation_base_1_embedded_3 = ['$and' => [
    ['id' => ['$type' => 'int']],
    ['id' => ['$exists' => TRUE]],
    ['test_field' => ['$type' => 'int']],
    ['test_field' => ['$exists' => TRUE]],
    ['test_field_string' => ['$type' => 'string']],
    ['test_field_string' => ['$exists' => TRUE]],
    ['$or' => [
      ['test_field_string_ascii' => ['$type' => 'string']],
      ['test_field_string_ascii' => ['$exists' => FALSE]]
    ]],
    ['$or' => [
      ['$and' => [
        ['test_table3.id' => ['$exists' => FALSE]],
        ['test_table3.test_field_varchar_null' => ['$exists' => FALSE]],
        ['test_table3.test_field_varchar_not_null' => ['$exists' => FALSE]],
        ['test_table3.test_field_varchar_default' => ['$exists' => FALSE]],
        ['test_table3.test_field_text_null' => ['$exists' => FALSE]],
        ['test_table3.test_field_text_not_null' => ['$exists' => FALSE]],
        ['test_table3.test_field_text_default' => ['$exists' => FALSE]],
      ]],
      ['$and' => [
        ['test_table3.id' => ['$type' => 'int']],
        ['test_table3.id' => ['$exists' => TRUE]],
        ['test_table3.id' => ['$gte' => 0]],
        ['$or' => [
          ['test_table3.test_field_varchar_null' => ['$type' => 'string']],
          ['test_table3.test_field_varchar_null' => ['$exists' => FALSE]]
        ]],
        ['test_table3.test_field_varchar_not_null' => ['$type' => 'string']],
        ['test_table3.test_field_varchar_not_null' => ['$exists' => TRUE]],
        ['$or' => [
          ['test_table3.test_field_varchar_default' => ['$type' => 'string']],
          ['test_table3.test_field_varchar_default' => ['$exists' => FALSE]]
        ]],
        ['$or' => [
          ['test_table3.test_field_text_null' => ['$type' => 'string']],
          ['test_table3.test_field_text_null' => ['$exists' => FALSE]]
        ]],
        ['test_table3.test_field_text_not_null' => ['$type' => 'string']],
        ['test_table3.test_field_text_not_null' => ['$exists' => TRUE]],
        ['$or' => [
          ['test_table3.test_field_text_default' => ['$type' => 'string']],
          ['test_table3.test_field_text_default' => ['$exists' => FALSE]]
        ]],
      ]],
    ]],
  ]];

  /**
   * An array with the validation data for base table test_table1 and the embedded tables test_table2 and test_table3.
   *
   * @var array
   */
  protected $embedded_validation_base_1_embedded_2_and_3 = ['$and' => [
    ['id' => ['$type' => 'int']],
    ['id' => ['$exists' => TRUE]],
    ['test_field' => ['$type' => 'int']],
    ['test_field' => ['$exists' => TRUE]],
    ['test_field_string' => ['$type' => 'string']],
    ['test_field_string' => ['$exists' => TRUE]],
    ['$or' => [
      ['test_field_string_ascii' => ['$type' => 'string']],
      ['test_field_string_ascii' => ['$exists' => FALSE]]
    ]],
    ['$or' => [
      ['$and' => [
        ['test_table2.id' => ['$exists' => FALSE]],
        ['test_table2.test_field_int_null' => ['$exists' => FALSE]],
        ['test_table2.test_field_int_not_null' => ['$exists' => FALSE]],
        ['test_table2.test_field_int_default' => ['$exists' => FALSE]],
        ['test_table2.test_field_float_null' => ['$exists' => FALSE]],
        ['test_table2.test_field_float_not_null' => ['$exists' => FALSE]],
        ['test_table2.test_field_float_default' => ['$exists' => FALSE]],
        ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]],
        ['test_table2.test_field_numeric_not_null' => ['$exists' => FALSE]],
        ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]],
      ]],
      ['$and' => [
        ['test_table2.id' => ['$type' => 'int']],
        ['test_table2.id' => ['$exists' => TRUE]],
        ['test_table2.id' => ['$gte' => 0]],
        ['$or' => [
          ['test_table2.test_field_int_null' => ['$type' => 'int']],
          ['test_table2.test_field_int_null' => ['$exists' => FALSE]]
        ]],
        ['test_table2.test_field_int_not_null' => ['$type' => 'int']],
        ['test_table2.test_field_int_not_null' => ['$exists' => TRUE]],
        ['$or' => [
          ['test_table2.test_field_int_default' => ['$type' => 'int']],
          ['test_table2.test_field_int_default' => ['$exists' => FALSE]]
        ]],
        ['$or' => [
          ['test_table2.test_field_float_null' => ['$type' => 'double']],
          ['test_table2.test_field_float_null' => ['$exists' => FALSE]]
        ]],
        ['test_table2.test_field_float_not_null' => ['$type' => 'double']],
        ['test_table2.test_field_float_not_null' => ['$exists' => TRUE]],
        ['$or' => [
          ['test_table2.test_field_float_default' => ['$type' => 'double']],
          ['test_table2.test_field_float_default' => ['$exists' => FALSE]]
        ]],
        ['$or' => [
          ['test_table2.test_field_numeric_null' => ['$type' => 'decimal']],
          ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]]
        ]],
        ['test_table2.test_field_numeric_not_null' => ['$type' => 'decimal']],
        ['test_table2.test_field_numeric_not_null' => ['$exists' => TRUE]],
        ['$or' => [
          ['test_table2.test_field_numeric_default' => ['$type' => 'decimal']],
          ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]]
        ]],
      ]],
    ]],
    ['$or' => [
      ['$and' => [
        ['test_table3.id' => ['$exists' => FALSE]],
        ['test_table3.test_field_varchar_null' => ['$exists' => FALSE]],
        ['test_table3.test_field_varchar_not_null' => ['$exists' => FALSE]],
        ['test_table3.test_field_varchar_default' => ['$exists' => FALSE]],
        ['test_table3.test_field_text_null' => ['$exists' => FALSE]],
        ['test_table3.test_field_text_not_null' => ['$exists' => FALSE]],
        ['test_table3.test_field_text_default' => ['$exists' => FALSE]],
      ]],
      ['$and' => [
        ['test_table3.id' => ['$type' => 'int']],
        ['test_table3.id' => ['$exists' => TRUE]],
        ['test_table3.id' => ['$gte' => 0]],
        ['$or' => [
          ['test_table3.test_field_varchar_null' => ['$type' => 'string']],
          ['test_table3.test_field_varchar_null' => ['$exists' => FALSE]]
        ]],
        ['test_table3.test_field_varchar_not_null' => ['$type' => 'string']],
        ['test_table3.test_field_varchar_not_null' => ['$exists' => TRUE]],
        ['$or' => [
          ['test_table3.test_field_varchar_default' => ['$type' => 'string']],
          ['test_table3.test_field_varchar_default' => ['$exists' => FALSE]]
        ]],
        ['$or' => [
          ['test_table3.test_field_text_null' => ['$type' => 'string']],
          ['test_table3.test_field_text_null' => ['$exists' => FALSE]]
        ]],
        ['test_table3.test_field_text_not_null' => ['$type' => 'string']],
        ['test_table3.test_field_text_not_null' => ['$exists' => TRUE]],
        ['$or' => [
          ['test_table3.test_field_text_default' => ['$type' => 'string']],
          ['test_table3.test_field_text_default' => ['$exists' => FALSE]]
        ]],
      ]],
    ]],
  ]];

  /**
   * An array with the validation data for base table test_table1 and the embedded table test_table2 and the test_table3 embedded on test_table2.
   *
   * @var array
   */
  protected $embedded_validation_base_1_embedded_2_and_3_on_2 = ['$and' => [
    ['$or' => [
      ['id' => ['$type' => 'int']],
      ['id' => ['$exists' => FALSE]]
    ]],
    ['test_field' => ['$type' => 'int']],
    ['test_field' => ['$exists' => TRUE]],
    ['test_field_string' => ['$type' => 'string']],
    ['test_field_string' => ['$exists' => TRUE]],
    ['$or' => [
      ['test_field_string_ascii' => ['$type' => 'string']],
      ['test_field_string_ascii' => ['$exists' => FALSE]]
    ]],
    ['$or' => [
      ['$and' => [
        ['test_table2.id' => ['$exists' => FALSE]],
        ['test_table2.test_field_int_null' => ['$exists' => FALSE]],
        ['test_table2.test_field_int_not_null' => ['$exists' => FALSE]],
        ['test_table2.test_field_int_default' => ['$exists' => FALSE]],
        ['test_table2.test_field_float_null' => ['$exists' => FALSE]],
        ['test_table2.test_field_float_not_null' => ['$exists' => FALSE]],
        ['test_table2.test_field_float_default' => ['$exists' => FALSE]],
        ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]],
        ['test_table2.test_field_numeric_not_null' => ['$exists' => FALSE]],
        ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]],
      ]],
      ['$and' => [
        ['test_table2.id' => ['$type' => 'int']],
        ['test_table2.id' => ['$exists' => TRUE]],
        ['test_table2.id' => ['$gte' => 0]],
        ['$or' => [
          ['test_table2.test_field_int_null' => ['$type' => 'int']],
          ['test_table2.test_field_int_null' => ['$exists' => FALSE]]
        ]],
        ['test_table2.test_field_int_not_null' => ['$type' => 'int']],
        ['test_table2.test_field_int_not_null' => ['$exists' => TRUE]],
        ['$or' => [
          ['test_table2.test_field_int_default' => ['$type' => 'int']],
          ['test_table2.test_field_int_default' => ['$exists' => FALSE]]
        ]],
        ['$or' => [
          ['test_table2.test_field_float_null' => ['$type' => 'double']],
          ['test_table2.test_field_float_null' => ['$exists' => FALSE]]
        ]],
        ['test_table2.test_field_float_not_null' => ['$type' => 'double']],
        ['test_table2.test_field_float_not_null' => ['$exists' => TRUE]],
        ['$or' => [
          ['test_table2.test_field_float_default' => ['$type' => 'double']],
          ['test_table2.test_field_float_default' => ['$exists' => FALSE]]
        ]],
        ['$or' => [
          ['test_table2.test_field_numeric_null' => ['$type' => 'int']],
          ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]]
        ]],
        ['test_table2.test_field_numeric_not_null' => ['$type' => 'int']],
        ['test_table2.test_field_numeric_not_null' => ['$exists' => TRUE]],
        ['$or' => [
          ['test_table2.test_field_numeric_default' => ['$type' => 'int']],
          ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]]
        ]],
      ]],
    ]],
    ['$or' => [
      ['$and' => [
        ['test_table2.test_table3.id' => ['$exists' => FALSE]],
        ['test_table2.test_table3.test_field_varchar_null' => ['$exists' => FALSE]],
        ['test_table2.test_table3.test_field_varchar_not_null' => ['$exists' => FALSE]],
        ['test_table2.test_table3.test_field_varchar_default' => ['$exists' => FALSE]],
        ['test_table2.test_table3.test_field_text_null' => ['$exists' => FALSE]],
        ['test_table2.test_table3.test_field_text_not_null' => ['$exists' => FALSE]],
        ['test_table2.test_table3.test_field_text_default' => ['$exists' => FALSE]],
      ]],
      ['$and' => [
        ['test_table2.test_table3.id' => ['$type' => 'int']],
        ['test_table2.test_table3.id' => ['$exists' => TRUE]],
        ['test_table2.test_table3.id' => ['$gte' => 0]],
        ['$or' => [
          ['test_table2.test_table3.test_field_varchar_null' => ['$type' => 'string']],
          ['test_table2.test_table3.test_field_varchar_null' => ['$exists' => FALSE]]
        ]],
        ['test_table2.test_table3.test_field_varchar_not_null' => ['$type' => 'string']],
        ['test_table2.test_table3.test_field_varchar_not_null' => ['$exists' => TRUE]],
        ['$or' => [
          ['test_table2.test_table3.test_field_varchar_default' => ['$type' => 'string']],
          ['test_table2.test_table3.test_field_varchar_default' => ['$exists' => FALSE]]
        ]],
        ['$or' => [
          ['test_table2.test_table3.test_field_text_null' => ['$type' => 'string']],
          ['test_table2.test_table3.test_field_text_null' => ['$exists' => FALSE]]
        ]],
        ['test_table2.test_table3.test_field_text_not_null' => ['$type' => 'string']],
        ['test_table2.test_table3.test_field_text_not_null' => ['$exists' => TRUE]],
        ['$or' => [
          ['test_table2.test_table3.test_field_text_default' => ['$type' => 'string']],
          ['test_table2.test_table3.test_field_text_default' => ['$exists' => FALSE]]
        ]],
      ]],
    ]],
  ]];

  /**
   * An array with the validation data for base table test_table1 and the embedded table test_table3 and the test_table2 embedded on test_table3.
   *
   * @var array
   */
  protected $embedded_validation_base_1_embedded_3_and_2_on_3 = ['$and' => [
    ['$or' => [
      ['id' => ['$type' => 'int']],
      ['id' => ['$exists' => FALSE]]
    ]],
    ['test_field' => ['$type' => 'int']],
    ['test_field' => ['$exists' => TRUE]],
    ['test_field_string' => ['$type' => 'string']],
    ['test_field_string' => ['$exists' => TRUE]],
    ['$or' => [
      ['test_field_string_ascii' => ['$type' => 'string']],
      ['test_field_string_ascii' => ['$exists' => FALSE]]
    ]],
    ['$or' => [
      ['$and' => [
        ['test_table3.id' => ['$exists' => FALSE]],
        ['test_table3.test_field_varchar_null' => ['$exists' => FALSE]],
        ['test_table3.test_field_varchar_not_null' => ['$exists' => FALSE]],
        ['test_table3.test_field_varchar_default' => ['$exists' => FALSE]],
        ['test_table3.test_field_text_null' => ['$exists' => FALSE]],
        ['test_table3.test_field_text_not_null' => ['$exists' => FALSE]],
        ['test_table3.test_field_text_default' => ['$exists' => FALSE]],
      ]],
      ['$and' => [
        ['test_table3.id' => ['$type' => 'int']],
        ['test_table3.id' => ['$exists' => TRUE]],
        ['test_table3.id' => ['$gte' => 0]],
        ['$or' => [
          ['test_table3.test_field_varchar_null' => ['$type' => 'string']],
          ['test_table3.test_field_varchar_null' => ['$exists' => FALSE]]
        ]],
        ['test_table3.test_field_varchar_not_null' => ['$type' => 'string']],
        ['test_table3.test_field_varchar_not_null' => ['$exists' => TRUE]],
        ['$or' => [
          ['test_table3.test_field_varchar_default' => ['$type' => 'string']],
          ['test_table3.test_field_varchar_default' => ['$exists' => FALSE]]
        ]],
        ['$or' => [
          ['test_table3.test_field_text_null' => ['$type' => 'string']],
          ['test_table3.test_field_text_null' => ['$exists' => FALSE]]
        ]],
        ['test_table3.test_field_text_not_null' => ['$type' => 'string']],
        ['test_table3.test_field_text_not_null' => ['$exists' => TRUE]],
        ['$or' => [
          ['test_table3.test_field_text_default' => ['$type' => 'string']],
          ['test_table3.test_field_text_default' => ['$exists' => FALSE]]
        ]],
      ]],
    ]],
    ['$or' => [
      ['$and' => [
        ['test_table3.test_table2.id' => ['$exists' => FALSE]],
        ['test_table3.test_table2.test_field_int_null' => ['$exists' => FALSE]],
        ['test_table3.test_table2.test_field_int_not_null' => ['$exists' => FALSE]],
        ['test_table3.test_table2.test_field_int_default' => ['$exists' => FALSE]],
        ['test_table3.test_table2.test_field_float_null' => ['$exists' => FALSE]],
        ['test_table3.test_table2.test_field_float_not_null' => ['$exists' => FALSE]],
        ['test_table3.test_table2.test_field_float_default' => ['$exists' => FALSE]],
        ['test_table3.test_table2.test_field_numeric_null' => ['$exists' => FALSE]],
        ['test_table3.test_table2.test_field_numeric_not_null' => ['$exists' => FALSE]],
        ['test_table3.test_table2.test_field_numeric_default' => ['$exists' => FALSE]],
      ]],
      ['$and' => [
        ['test_table3.test_table2.id' => ['$type' => 'int']],
        ['test_table3.test_table2.id' => ['$exists' => TRUE]],
        ['test_table3.test_table2.id' => ['$gte' => 0]],
        ['$or' => [
          ['test_table3.test_table2.test_field_int_null' => ['$type' => 'int']],
          ['test_table3.test_table2.test_field_int_null' => ['$exists' => FALSE]]
        ]],
        ['test_table3.test_table2.test_field_int_not_null' => ['$type' => 'int']],
        ['test_table3.test_table2.test_field_int_not_null' => ['$exists' => TRUE]],
        ['$or' => [
          ['test_table3.test_table2.test_field_int_default' => ['$type' => 'int']],
          ['test_table3.test_table2.test_field_int_default' => ['$exists' => FALSE]]
        ]],
        ['$or' => [
          ['test_table3.test_table2.test_field_float_null' => ['$type' => 'double']],
          ['test_table3.test_table2.test_field_float_null' => ['$exists' => FALSE]]
        ]],
        ['test_table3.test_table2.test_field_float_not_null' => ['$type' => 'double']],
        ['test_table3.test_table2.test_field_float_not_null' => ['$exists' => TRUE]],
        ['$or' => [
          ['test_table3.test_table2.test_field_float_default' => ['$type' => 'double']],
          ['test_table3.test_table2.test_field_float_default' => ['$exists' => FALSE]]
        ]],
        ['$or' => [
          ['test_table3.test_table2.test_field_numeric_null' => ['$type' => 'int']],
          ['test_table3.test_table2.test_field_numeric_null' => ['$exists' => FALSE]]
        ]],
        ['test_table3.test_table2.test_field_numeric_not_null' => ['$type' => 'int']],
        ['test_table3.test_table2.test_field_numeric_not_null' => ['$exists' => TRUE]],
        ['$or' => [
          ['test_table3.test_table2.test_field_numeric_default' => ['$type' => 'int']],
          ['test_table3.test_table2.test_field_numeric_default' => ['$exists' => FALSE]]
        ]],
      ]],
    ]],
  ]];

  /**
   * An array with the expected indexes for base table test_table1 and the embedded table test_table2.
   *
   * @var array
   */
  protected $embedded_indexes_base_1_embedded_2 = [
    ['name' => '_id_', 'unique' => FALSE, 'key' => ['_id' => 1]],
    ['name' => '__pkey', 'unique' => TRUE, 'key' => ['id' => 1]],
    ['name' => 'test_field__key', 'unique' => TRUE, 'key' => ['test_field' => 1]],
    ['name' => 'test_table2.__pkey', 'unique' => TRUE, 'key' => ['test_table2.id' => 1]],
    ['name' => 'test_table2.test_fields_not_null__key', 'unique' => TRUE, 'key' => ['test_table2.test_field_int_not_null' => 1, 'test_table2.test_field_float_not_null' => 1, 'test_table2.test_field_numeric_not_null' => 1]],
    ['name' => 'test_table2.test_fields_null__key', 'unique' => TRUE, 'key' => ['test_table2.test_field_int_null' => 1, 'test_table2.test_field_float_null' => 1, 'test_table2.test_field_numeric_null' => 1]],
    ['name' => 'test_table2.test_fields_default__idx', 'unique' => FALSE, 'key' => ['test_table2.test_field_int_default' => 1, 'test_table2.test_field_float_default' => 1, 'test_table2.test_field_numeric_default' => 1]],
    ['name' => 'test_table2.test_field_int_null__idx', 'unique' => FALSE, 'key' => ['test_table2.test_field_int_null' => 1]],
  ];

  /**
   * An array with the expected indexes for base table test_table1 and the embedded table test_table3.
   *
   * @var array
   */
  protected $embedded_indexes_base_1_embedded_3 = [
    ['name' => '_id_', 'unique' => FALSE, 'key' => ['_id' => 1]],
    ['name' => '__pkey', 'unique' => TRUE, 'key' => ['id' => 1]],
    ['name' => 'test_field__key', 'unique' => TRUE, 'key' => ['test_field' => 1]],
  ];

  /**
   * An array with the expected indexes for base table test_table1 and the embedded tables test_table2 and test_table3.
   *
   * @var array
   */
  protected $embedded_indexes_base_1_embedded_2_and_3 = [
    ['name' => '_id_', 'unique' => FALSE, 'key' => ['_id' => 1]],
    ['name' => '__pkey', 'unique' => TRUE, 'key' => ['id' => 1]],
    ['name' => 'test_field__key', 'unique' => TRUE, 'key' => ['test_field' => 1]],
    ['name' => 'test_table2.__pkey', 'unique' => TRUE, 'key' => ['test_table2.id' => 1]],
    ['name' => 'test_table2.test_fields_not_null__key', 'unique' => TRUE, 'key' => ['test_table2.test_field_int_not_null' => 1, 'test_table2.test_field_float_not_null' => 1, 'test_table2.test_field_numeric_not_null' => 1]],
    ['name' => 'test_table2.test_fields_null__key', 'unique' => TRUE, 'key' => ['test_table2.test_field_int_null' => 1, 'test_table2.test_field_float_null' => 1, 'test_table2.test_field_numeric_null' => 1]],
    ['name' => 'test_table2.test_fields_default__idx', 'unique' => FALSE, 'key' => ['test_table2.test_field_int_default' => 1, 'test_table2.test_field_float_default' => 1, 'test_table2.test_field_numeric_default' => 1]],
    ['name' => 'test_table2.test_field_int_null__idx', 'unique' => FALSE, 'key' => ['test_table2.test_field_int_null' => 1]],
  ];

  /**
   * Data provider for testCreateTable().
   *
   * @return array[]
   *   Returns data-set elements with:
   *     - the table data to use as base table (the name, the schema and the
   *       validation).
   *     - the array of table data to use as embedded tables (the name, the
   *       schema and the validation).
   *     - the expected validation for the base table from the first parameter
   *       and the embedded tables from the second parameter.
   */
  public function providerCreateTable() {
    $validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3 = ['$and' => [
      ['id' => ['$type' => 'int']],
      ['id' => ['$exists' => TRUE]],
      ['test_field' => ['$type' => 'int']],
      ['test_field' => ['$exists' => TRUE]],
      ['test_field_string' => ['$type' => 'string']],
      ['test_field_string' => ['$exists' => TRUE]],
      ['$or' => [
        ['test_field_string_ascii' => ['$type' => 'string']],
        ['test_field_string_ascii' => ['$exists' => FALSE]]
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.id' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_default' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_default' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.id' => ['$type' => 'int']],
          ['test_table2.id' => ['$exists' => TRUE]],
          ['test_table2.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table2.test_field_int_null' => ['$type' => 'int']],
            ['test_table2.test_field_int_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_int_not_null' => ['$type' => 'int']],
          ['test_table2.test_field_int_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_int_default' => ['$type' => 'int']],
            ['test_table2.test_field_int_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_field_float_null' => ['$type' => 'double']],
            ['test_table2.test_field_float_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_float_not_null' => ['$type' => 'double']],
          ['test_table2.test_field_float_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_float_default' => ['$type' => 'double']],
            ['test_table2.test_field_float_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_field_numeric_null' => ['$type' => 'decimal']],
            ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_numeric_not_null' => ['$type' => 'decimal']],
          ['test_table2.test_field_numeric_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_numeric_default' => ['$type' => 'decimal']],
            ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table3.id' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_varchar_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_varchar_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_varchar_default' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_text_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_text_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_text_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table3.id' => ['$type' => 'int']],
          ['test_table2.test_table3.id' => ['$exists' => TRUE]],
          ['test_table2.test_table3.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table2.test_table3.test_field_varchar_null' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_varchar_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_table3.test_field_varchar_not_null' => ['$type' => 'string']],
          ['test_table2.test_table3.test_field_varchar_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_table3.test_field_varchar_default' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_varchar_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_table3.test_field_text_null' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_text_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_table3.test_field_text_not_null' => ['$type' => 'string']],
          ['test_table2.test_table3.test_field_text_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_table3.test_field_text_default' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_text_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table3.test_table5.id' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_table5.test_field_string' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table3.test_table5.id' => ['$type' => 'int']],
          ['test_table2.test_table3.test_table5.id' => ['$exists' => TRUE]],
          ['test_table2.test_table3.test_table5.id' => ['$gte' => 0]],
          ['test_table2.test_table3.test_table5.test_field_string' => ['$type' => 'string']],
          ['test_table2.test_table3.test_table5.test_field_string' => ['$exists' => TRUE]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table3.test_table6.id' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_table6.test_field_string' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table3.test_table6.id' => ['$type' => 'int']],
          ['test_table2.test_table3.test_table6.id' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_table3.test_table6.test_field_string' => ['$type' => 'string']],
            ['test_table2.test_table3.test_table6.test_field_string' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table4.id' => ['$exists' => FALSE]],
          ['test_table2.test_table4.test_field' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table4.id' => ['$type' => 'int']],
          ['test_table2.test_table4.id' => ['$exists' => TRUE]],
          ['test_table2.test_table4.test_field' => ['$type' => 'int']],
          ['test_table2.test_table4.test_field' => ['$exists' => TRUE]],
        ]],
      ]],
    ]];

    $indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3 = [
      ['name' => '_id_', 'unique' => FALSE, 'key' => ['_id' => 1]],
      ['name' => '__pkey', 'unique' => TRUE, 'key' => ['id' => 1]],
      ['name' => 'test_field__key', 'unique' => TRUE, 'key' => ['test_field' => 1]],
      ['name' => 'test_table2.__pkey', 'unique' => TRUE, 'key' => ['test_table2.id' => 1]],
      ['name' => 'test_table2.test_fields_not_null__key', 'unique' => TRUE, 'key' => ['test_table2.test_field_int_not_null' => 1, 'test_table2.test_field_float_not_null' => 1, 'test_table2.test_field_numeric_not_null' => 1]],
      ['name' => 'test_table2.test_fields_null__key', 'unique' => TRUE, 'key' => ['test_table2.test_field_int_null' => 1, 'test_table2.test_field_float_null' => 1, 'test_table2.test_field_numeric_null' => 1]],
      ['name' => 'test_table2.test_fields_default__idx', 'unique' => FALSE, 'key' => ['test_table2.test_field_int_default' => 1, 'test_table2.test_field_float_default' => 1, 'test_table2.test_field_numeric_default' => 1]],
      ['name' => 'test_table2.test_field_int_null__idx', 'unique' => FALSE, 'key' => ['test_table2.test_field_int_null' => 1]],
      ['name' => 'test_table2.test_table4.__pkey', 'unique' => TRUE, 'key' => ['test_table2.test_table4.id' => 1]],
      ['name' => 'test_table2.test_table4.test_field__key', 'unique' => TRUE, 'key' => ['test_table2.test_table4.test_field' => 1]],
      ['name' => 'test_table2.test_table3.test_table6.__pkey', 'unique' => TRUE, 'key' => ['test_table2.test_table3.test_table6.id' => 1]],
      ['name' => 'test_table2.test_table3.test_table6.test_field_string__idx', 'unique' => FALSE, 'key' => ['test_table2.test_table3.test_table6.test_field_string' => 1]],
    ];

    return [
      [
        $this->test_table1,
        [],
        $this->test_table1['validation'],
        $this->test_table1['indexes'],
      ],
      [
        $this->test_table2,
        [],
        $this->test_table2['validation'],
        $this->test_table2['indexes'],
      ],
      [
        $this->test_table3,
        [],
        $this->test_table3['validation'],
        $this->test_table3['indexes'],
      ],
      [
        $this->test_table4,
        [],
        $this->test_table4['validation'],
        $this->test_table4['indexes'],
      ],
      [
        $this->test_table5,
        [],
        $this->test_table5['validation'],
        $this->test_table5['indexes'],
      ],
      [
        $this->test_table6,
        [],
        $this->test_table6['validation'],
        $this->test_table6['indexes'],
      ],
      [
        $this->test_table1,
        [$this->test_table1['name'] => [$this->test_table2]],
        $this->embedded_validation_base_1_embedded_2,
        $this->embedded_indexes_base_1_embedded_2,
      ],
      [
        $this->test_table1,
        [$this->test_table1['name'] => [$this->test_table3]],
        $this->embedded_validation_base_1_embedded_3,
        $this->embedded_indexes_base_1_embedded_3,
      ],
      [
        $this->test_table1,
        [$this->test_table1['name'] => [$this->test_table2, $this->test_table3]],
        $this->embedded_validation_base_1_embedded_2_and_3,
        $this->embedded_indexes_base_1_embedded_2_and_3,
      ],
      [
        $this->test_table1,
        [$this->test_table1['name'] => [$this->test_table2], $this->test_table2['name'] => [$this->test_table3, $this->test_table4], $this->test_table3['name'] => [$this->test_table5, $this->test_table6]],
        $validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3,
        $indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3,
      ],
    ];
  }

  /**
   * @covers ::createTable
   * @covers ::createEmbeddedTable
   * @dataProvider providerCreateTable
   */
  public function testCreateTable($base_table_data, $embedded_tables_data, $embedded_validation, $expected_indexes) {
    $schema = Database::getConnection()->schema();

    $this->assertFalse($schema->tableExists($base_table_data['name']), 'The table does not exist in the MongoDB database.');

    // Call the to be tested methods: Schema::createTable() and
    // Schema::createEmbeddedTable().
    $schema->createTable($base_table_data['name'], $base_table_data['schema']);
    foreach ($embedded_tables_data as $parent_table_name => $embedded_table_array) {
      foreach ($embedded_table_array as $embedded_table_data) {
        $schema->createEmbeddedTable($parent_table_name, $embedded_table_data['name'], $embedded_table_data['schema']);
      }
    }

    // Check that the tables exist and that their validation, schema or index
    // data is what is it should be.
    $this->assertTrue($schema->tableExists($base_table_data['name']), 'The table exists in the MongoDB database.');
    foreach ($embedded_tables_data as $parent_table_name => $embedded_table_array) {
      foreach ($embedded_table_array as $embedded_table_data) {
        $this->assertTrue($schema->tableExists($embedded_table_data['name']), 'The embedded table exists in the MongoDB database.');
      }
    }
    $this->checkTableValidation($base_table_data['name'], $embedded_validation);
    $this->checkTableSchema($base_table_data['name'], $base_table_data['schema']);
    foreach ($embedded_tables_data as $parent_table_name => $embedded_table_array) {
      foreach ($embedded_table_array as $embedded_table_data) {
        $embedded_table_data['schema']['embedded_to_table'] = $parent_table_name;
        $this->checkTableSchema($embedded_table_data['name'], $embedded_table_data['schema']);
      }
    }
    $this->checkTableIndexes($base_table_data['name'], $base_table_data['schema']);
    foreach ($embedded_tables_data as $parent_table_name => $embedded_table_array) {
      foreach ($embedded_table_array as $embedded_table_data) {
        $this->checkEmbeddedTableIndexes($parent_table_name, $embedded_table_data['name'], $embedded_table_data['schema']);
      }
    }
    $this->checkExpectedIndexesAgainstDatabase($base_table_data['name'], $expected_indexes);
    $this->checkTableNumberOfIndexes($base_table_data, $embedded_tables_data);
  }

  /**
   * @covers ::createTable
   */
  public function testCreateTableForTableExists() {
    $schema = Database::getConnection()->schema();

    $this->assertFalse($schema->tableExists($this->test_table1['name']), 'The table does not exist in the MongoDB database.');

    $schema->createTable($this->test_table1['name'], $this->test_table1['schema']);

    $this->assertTrue($schema->tableExists($this->test_table1['name']), 'The table exists in the MongoDB database.');

    // If we try to create a table that exists an exception should be thrown.
    $this->setExpectedException('Drupal\Core\Database\SchemaObjectExistsException');
    $schema->createTable($this->test_table1['name'], $this->test_table1['schema']);
  }

  /**
   * @covers ::createEmbeddedTable
   */
  public function testCreateEmbeddedTableForBaseTableDoesNotExist() {
    $schema = Database::getConnection()->schema();

    $this->assertFalse($schema->tableExists($this->test_table1['name']), 'The table does not exist in the MongoDB database.');

    // If we try to create an embedded table on a base table that does not
    // exists, an exception should be thrown.
    $this->setExpectedException('Drupal\Core\Database\SchemaObjectDoesNotExistException');
    $schema->createEmbeddedTable($this->test_table1['name'], $this->test_table2['name'], $this->test_table2['schema']);
  }

  /**
   * @covers ::createEmbeddedTable
   */
  public function testCreateEmbeddedTableForEmbeddedTableExists() {
    $schema = Database::getConnection()->schema();

    $this->assertFalse($schema->tableExists($this->test_table1['name']), 'The table does not exist in the MongoDB database.');

    $schema->createTable($this->test_table1['name'], $this->test_table1['schema']);
    $schema->createEmbeddedTable($this->test_table1['name'], $this->test_table2['name'], $this->test_table2['schema']);

    $this->assertTrue($schema->tableExists($this->test_table1['name']), 'The table exists in the MongoDB database.');

    // If we try to create an embedded table that exists an exception should be thrown.
    $this->setExpectedException('Drupal\Core\Database\SchemaObjectExistsException');
    $schema->createEmbeddedTable($this->test_table1['name'], $this->test_table2['name'], $this->test_table2['schema']);
  }

  /**
   * @covers ::createEmbeddedTable
   */
  public function testCreateEmbeddedTableForEmbeddedTableExistsAsBaseTable() {
    $schema = Database::getConnection()->schema();

    $this->assertFalse($schema->tableExists($this->test_table1['name']), 'The table does not exist in the MongoDB database.');
    $this->assertFalse($schema->tableExists($this->test_table2['name']), 'The table does not exist in the MongoDB database.');

    $schema->createTable($this->test_table1['name'], $this->test_table1['schema']);
    $schema->createTable($this->test_table2['name'], $this->test_table2['schema']);

    $this->assertTrue($schema->tableExists($this->test_table1['name']), 'The table exists in the MongoDB database.');
    $this->assertTrue($schema->tableExists($this->test_table2['name']), 'The table exists in the MongoDB database.');

    // If we try to create an embedded table that exists an exception should be thrown.
    $this->setExpectedException('Drupal\Core\Database\SchemaObjectExistsException');
    $schema->createEmbeddedTable($this->test_table1['name'], $this->test_table2['name'], $this->test_table2['schema']);
  }

  /**
   * @covers ::createEmbeddedTable
   * @covers ::tableExists
   */
  public function testCreateEmbeddedTableForEmbeddedTableExistsOnOtherBaseTable() {
    $schema = Database::getConnection()->schema();

    $this->assertFalse($schema->tableExists($this->test_table1['name']), 'The table does not exist in the MongoDB database.');
    $this->assertFalse($schema->tableExists($this->test_table2['name']), 'The table does not exist in the MongoDB database.');
    $this->assertFalse($schema->tableExists($this->test_table2['name']), 'The embedded table does not exist in the MongoDB database.');

    $schema->createTable($this->test_table1['name'], $this->test_table1['schema']);
    $schema->createTable($this->test_table2['name'], $this->test_table2['schema']);
    $schema->createEmbeddedTable($this->test_table2['name'], $this->test_table3['name'], $this->test_table3['schema']);

    $this->assertTrue($schema->tableExists($this->test_table1['name']), 'The table exists in the MongoDB database.');
    $this->assertTrue($schema->tableExists($this->test_table2['name']), 'The table exists in the MongoDB database.');
    $this->assertTrue($schema->tableExists($this->test_table3['name']), 'The embedded table exists in the MongoDB database.');

    // If we try to create an embedded table on a base table that does not
    // exists, an exception should be thrown.
    $this->setExpectedException('Drupal\Core\Database\SchemaObjectExistsException');
    $schema->createEmbeddedTable($this->test_table1['name'], $this->test_table3['name'], $this->test_table3['schema']);
  }

  /**
   * Data provider for testRenameTable().
   *
   * @return array[]
   *   Returns data-set elements with:
   *     - the table data to use as base table (the name, the schema and the
   *       validation).
   *     - the array of table data to use as embedded tables (the name, the
   *       schema and the validation) keyed by their parent table name.
   *     - the table name to rename.
   *     - the expected validation for the base table before we call the rename
   *       table method.
   *     - the expected validation for the base table after we have called the
   *       rename table method.
   *     - the expected indexes for the base table before we call the rename
   *       table method.
   *     - the expected indexes for the base table after we have called the
   *       rename table method.
   */
  public function providerRenameTable() {
    $renamed_embedded_validation_base_1_embedded_2 = ['$and' => [
      ['id' => ['$type' => 'int']],
      ['id' => ['$exists' => TRUE]],
      ['test_field' => ['$type' => 'int']],
      ['test_field' => ['$exists' => TRUE]],
      ['test_field_string' => ['$type' => 'string']],
      ['test_field_string' => ['$exists' => TRUE]],
      ['$or' => [
        ['test_field_string_ascii' => ['$type' => 'string']],
        ['test_field_string_ascii' => ['$exists' => FALSE]]
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2_new.id' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_int_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_int_not_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_int_default' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_float_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_float_not_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_float_default' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_numeric_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_numeric_not_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_numeric_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2_new.id' => ['$type' => 'int']],
          ['test_table2_new.id' => ['$exists' => TRUE]],
          ['test_table2_new.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table2_new.test_field_int_null' => ['$type' => 'int']],
            ['test_table2_new.test_field_int_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2_new.test_field_int_not_null' => ['$type' => 'int']],
          ['test_table2_new.test_field_int_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2_new.test_field_int_default' => ['$type' => 'int']],
            ['test_table2_new.test_field_int_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2_new.test_field_float_null' => ['$type' => 'double']],
            ['test_table2_new.test_field_float_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2_new.test_field_float_not_null' => ['$type' => 'double']],
          ['test_table2_new.test_field_float_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2_new.test_field_float_default' => ['$type' => 'double']],
            ['test_table2_new.test_field_float_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2_new.test_field_numeric_null' => ['$type' => 'decimal']],
            ['test_table2_new.test_field_numeric_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2_new.test_field_numeric_not_null' => ['$type' => 'decimal']],
          ['test_table2_new.test_field_numeric_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2_new.test_field_numeric_default' => ['$type' => 'decimal']],
            ['test_table2_new.test_field_numeric_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
    ]];

    $renamed_embedded_validation_base_1_embedded_3 = ['$and' => [
      ['id' => ['$type' => 'int']],
      ['id' => ['$exists' => TRUE]],
      ['test_field' => ['$type' => 'int']],
      ['test_field' => ['$exists' => TRUE]],
      ['test_field_string' => ['$type' => 'string']],
      ['test_field_string' => ['$exists' => TRUE]],
      ['$or' => [
        ['test_field_string_ascii' => ['$type' => 'string']],
        ['test_field_string_ascii' => ['$exists' => FALSE]]
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table3_new.id' => ['$exists' => FALSE]],
          ['test_table3_new.test_field_varchar_null' => ['$exists' => FALSE]],
          ['test_table3_new.test_field_varchar_not_null' => ['$exists' => FALSE]],
          ['test_table3_new.test_field_varchar_default' => ['$exists' => FALSE]],
          ['test_table3_new.test_field_text_null' => ['$exists' => FALSE]],
          ['test_table3_new.test_field_text_not_null' => ['$exists' => FALSE]],
          ['test_table3_new.test_field_text_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table3_new.id' => ['$type' => 'int']],
          ['test_table3_new.id' => ['$exists' => TRUE]],
          ['test_table3_new.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table3_new.test_field_varchar_null' => ['$type' => 'string']],
            ['test_table3_new.test_field_varchar_null' => ['$exists' => FALSE]]
          ]],
          ['test_table3_new.test_field_varchar_not_null' => ['$type' => 'string']],
          ['test_table3_new.test_field_varchar_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table3_new.test_field_varchar_default' => ['$type' => 'string']],
            ['test_table3_new.test_field_varchar_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table3_new.test_field_text_null' => ['$type' => 'string']],
            ['test_table3_new.test_field_text_null' => ['$exists' => FALSE]]
          ]],
          ['test_table3_new.test_field_text_not_null' => ['$type' => 'string']],
          ['test_table3_new.test_field_text_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table3_new.test_field_text_default' => ['$type' => 'string']],
            ['test_table3_new.test_field_text_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
    ]];

    $renamed_embedded_validation_base_1_embedded_2_and_3 = ['$and' => [
      ['id' => ['$type' => 'int']],
      ['id' => ['$exists' => TRUE]],
      ['test_field' => ['$type' => 'int']],
      ['test_field' => ['$exists' => TRUE]],
      ['test_field_string' => ['$type' => 'string']],
      ['test_field_string' => ['$exists' => TRUE]],
      ['$or' => [
        ['test_field_string_ascii' => ['$type' => 'string']],
        ['test_field_string_ascii' => ['$exists' => FALSE]]
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2_new.id' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_int_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_int_not_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_int_default' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_float_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_float_not_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_float_default' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_numeric_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_numeric_not_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_numeric_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2_new.id' => ['$type' => 'int']],
          ['test_table2_new.id' => ['$exists' => TRUE]],
          ['test_table2_new.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table2_new.test_field_int_null' => ['$type' => 'int']],
            ['test_table2_new.test_field_int_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2_new.test_field_int_not_null' => ['$type' => 'int']],
          ['test_table2_new.test_field_int_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2_new.test_field_int_default' => ['$type' => 'int']],
            ['test_table2_new.test_field_int_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2_new.test_field_float_null' => ['$type' => 'double']],
            ['test_table2_new.test_field_float_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2_new.test_field_float_not_null' => ['$type' => 'double']],
          ['test_table2_new.test_field_float_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2_new.test_field_float_default' => ['$type' => 'double']],
            ['test_table2_new.test_field_float_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2_new.test_field_numeric_null' => ['$type' => 'decimal']],
            ['test_table2_new.test_field_numeric_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2_new.test_field_numeric_not_null' => ['$type' => 'decimal']],
          ['test_table2_new.test_field_numeric_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2_new.test_field_numeric_default' => ['$type' => 'decimal']],
            ['test_table2_new.test_field_numeric_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table3.id' => ['$exists' => FALSE]],
          ['test_table3.test_field_varchar_null' => ['$exists' => FALSE]],
          ['test_table3.test_field_varchar_not_null' => ['$exists' => FALSE]],
          ['test_table3.test_field_varchar_default' => ['$exists' => FALSE]],
          ['test_table3.test_field_text_null' => ['$exists' => FALSE]],
          ['test_table3.test_field_text_not_null' => ['$exists' => FALSE]],
          ['test_table3.test_field_text_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table3.id' => ['$type' => 'int']],
          ['test_table3.id' => ['$exists' => TRUE]],
          ['test_table3.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table3.test_field_varchar_null' => ['$type' => 'string']],
            ['test_table3.test_field_varchar_null' => ['$exists' => FALSE]]
          ]],
          ['test_table3.test_field_varchar_not_null' => ['$type' => 'string']],
          ['test_table3.test_field_varchar_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table3.test_field_varchar_default' => ['$type' => 'string']],
            ['test_table3.test_field_varchar_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table3.test_field_text_null' => ['$type' => 'string']],
            ['test_table3.test_field_text_null' => ['$exists' => FALSE]]
          ]],
          ['test_table3.test_field_text_not_null' => ['$type' => 'string']],
          ['test_table3.test_field_text_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table3.test_field_text_default' => ['$type' => 'string']],
            ['test_table3.test_field_text_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
    ]];

    $renamed_validation_base_1_embedded_2_and_3_on_2_before = $renamed_validation_base_1_embedded_2_and_3_on_2_after_renamed_1 = ['$and' => [
      ['id' => ['$type' => 'int']],
      ['id' => ['$exists' => TRUE]],
      ['test_field' => ['$type' => 'int']],
      ['test_field' => ['$exists' => TRUE]],
      ['test_field_string' => ['$type' => 'string']],
      ['test_field_string' => ['$exists' => TRUE]],
      ['$or' => [
        ['test_field_string_ascii' => ['$type' => 'string']],
        ['test_field_string_ascii' => ['$exists' => FALSE]]
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.id' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_default' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_default' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.id' => ['$type' => 'int']],
          ['test_table2.id' => ['$exists' => TRUE]],
          ['test_table2.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table2.test_field_int_null' => ['$type' => 'int']],
            ['test_table2.test_field_int_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_int_not_null' => ['$type' => 'int']],
          ['test_table2.test_field_int_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_int_default' => ['$type' => 'int']],
            ['test_table2.test_field_int_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_field_float_null' => ['$type' => 'double']],
            ['test_table2.test_field_float_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_float_not_null' => ['$type' => 'double']],
          ['test_table2.test_field_float_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_float_default' => ['$type' => 'double']],
            ['test_table2.test_field_float_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_field_numeric_null' => ['$type' => 'decimal']],
            ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_numeric_not_null' => ['$type' => 'decimal']],
          ['test_table2.test_field_numeric_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_numeric_default' => ['$type' => 'decimal']],
            ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table3.id' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_varchar_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_varchar_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_varchar_default' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_text_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_text_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_text_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table3.id' => ['$type' => 'int']],
          ['test_table2.test_table3.id' => ['$exists' => TRUE]],
          ['test_table2.test_table3.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table2.test_table3.test_field_varchar_null' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_varchar_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_table3.test_field_varchar_not_null' => ['$type' => 'string']],
          ['test_table2.test_table3.test_field_varchar_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_table3.test_field_varchar_default' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_varchar_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_table3.test_field_text_null' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_text_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_table3.test_field_text_not_null' => ['$type' => 'string']],
          ['test_table2.test_table3.test_field_text_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_table3.test_field_text_default' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_text_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
    ]];

    $renamed_validation_base_1_embedded_2_and_3_on_2_after_renamed_2 = ['$and' => [
      ['id' => ['$type' => 'int']],
      ['id' => ['$exists' => TRUE]],
      ['test_field' => ['$type' => 'int']],
      ['test_field' => ['$exists' => TRUE]],
      ['test_field_string' => ['$type' => 'string']],
      ['test_field_string' => ['$exists' => TRUE]],
      ['$or' => [
        ['test_field_string_ascii' => ['$type' => 'string']],
        ['test_field_string_ascii' => ['$exists' => FALSE]]
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2_new.id' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_int_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_int_not_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_int_default' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_float_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_float_not_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_float_default' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_numeric_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_numeric_not_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_numeric_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2_new.id' => ['$type' => 'int']],
          ['test_table2_new.id' => ['$exists' => TRUE]],
          ['test_table2_new.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table2_new.test_field_int_null' => ['$type' => 'int']],
            ['test_table2_new.test_field_int_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2_new.test_field_int_not_null' => ['$type' => 'int']],
          ['test_table2_new.test_field_int_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2_new.test_field_int_default' => ['$type' => 'int']],
            ['test_table2_new.test_field_int_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2_new.test_field_float_null' => ['$type' => 'double']],
            ['test_table2_new.test_field_float_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2_new.test_field_float_not_null' => ['$type' => 'double']],
          ['test_table2_new.test_field_float_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2_new.test_field_float_default' => ['$type' => 'double']],
            ['test_table2_new.test_field_float_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2_new.test_field_numeric_null' => ['$type' => 'decimal']],
            ['test_table2_new.test_field_numeric_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2_new.test_field_numeric_not_null' => ['$type' => 'decimal']],
          ['test_table2_new.test_field_numeric_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2_new.test_field_numeric_default' => ['$type' => 'decimal']],
            ['test_table2_new.test_field_numeric_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2_new.test_table3.id' => ['$exists' => FALSE]],
          ['test_table2_new.test_table3.test_field_varchar_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_table3.test_field_varchar_not_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_table3.test_field_varchar_default' => ['$exists' => FALSE]],
          ['test_table2_new.test_table3.test_field_text_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_table3.test_field_text_not_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_table3.test_field_text_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2_new.test_table3.id' => ['$type' => 'int']],
          ['test_table2_new.test_table3.id' => ['$exists' => TRUE]],
          ['test_table2_new.test_table3.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table2_new.test_table3.test_field_varchar_null' => ['$type' => 'string']],
            ['test_table2_new.test_table3.test_field_varchar_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2_new.test_table3.test_field_varchar_not_null' => ['$type' => 'string']],
          ['test_table2_new.test_table3.test_field_varchar_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2_new.test_table3.test_field_varchar_default' => ['$type' => 'string']],
            ['test_table2_new.test_table3.test_field_varchar_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2_new.test_table3.test_field_text_null' => ['$type' => 'string']],
            ['test_table2_new.test_table3.test_field_text_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2_new.test_table3.test_field_text_not_null' => ['$type' => 'string']],
          ['test_table2_new.test_table3.test_field_text_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2_new.test_table3.test_field_text_default' => ['$type' => 'string']],
            ['test_table2_new.test_table3.test_field_text_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
    ]];

    $renamed_validation_base_1_embedded_2_and_3_on_2_after_renamed_3 = ['$and' => [
      ['id' => ['$type' => 'int']],
      ['id' => ['$exists' => TRUE]],
      ['test_field' => ['$type' => 'int']],
      ['test_field' => ['$exists' => TRUE]],
      ['test_field_string' => ['$type' => 'string']],
      ['test_field_string' => ['$exists' => TRUE]],
      ['$or' => [
        ['test_field_string_ascii' => ['$type' => 'string']],
        ['test_field_string_ascii' => ['$exists' => FALSE]]
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.id' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_default' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_default' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.id' => ['$type' => 'int']],
          ['test_table2.id' => ['$exists' => TRUE]],
          ['test_table2.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table2.test_field_int_null' => ['$type' => 'int']],
            ['test_table2.test_field_int_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_int_not_null' => ['$type' => 'int']],
          ['test_table2.test_field_int_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_int_default' => ['$type' => 'int']],
            ['test_table2.test_field_int_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_field_float_null' => ['$type' => 'double']],
            ['test_table2.test_field_float_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_float_not_null' => ['$type' => 'double']],
          ['test_table2.test_field_float_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_float_default' => ['$type' => 'double']],
            ['test_table2.test_field_float_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_field_numeric_null' => ['$type' => 'decimal']],
            ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_numeric_not_null' => ['$type' => 'decimal']],
          ['test_table2.test_field_numeric_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_numeric_default' => ['$type' => 'decimal']],
            ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table3_new.id' => ['$exists' => FALSE]],
          ['test_table2.test_table3_new.test_field_varchar_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3_new.test_field_varchar_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3_new.test_field_varchar_default' => ['$exists' => FALSE]],
          ['test_table2.test_table3_new.test_field_text_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3_new.test_field_text_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3_new.test_field_text_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table3_new.id' => ['$type' => 'int']],
          ['test_table2.test_table3_new.id' => ['$exists' => TRUE]],
          ['test_table2.test_table3_new.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table2.test_table3_new.test_field_varchar_null' => ['$type' => 'string']],
            ['test_table2.test_table3_new.test_field_varchar_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_table3_new.test_field_varchar_not_null' => ['$type' => 'string']],
          ['test_table2.test_table3_new.test_field_varchar_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_table3_new.test_field_varchar_default' => ['$type' => 'string']],
            ['test_table2.test_table3_new.test_field_varchar_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_table3_new.test_field_text_null' => ['$type' => 'string']],
            ['test_table2.test_table3_new.test_field_text_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_table3_new.test_field_text_not_null' => ['$type' => 'string']],
          ['test_table2.test_table3_new.test_field_text_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_table3_new.test_field_text_default' => ['$type' => 'string']],
            ['test_table2.test_table3_new.test_field_text_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
    ]];

    $renamed_validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_before = $renamed_validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_renamed_1 = ['$and' => [
      ['id' => ['$type' => 'int']],
      ['id' => ['$exists' => TRUE]],
      ['test_field' => ['$type' => 'int']],
      ['test_field' => ['$exists' => TRUE]],
      ['test_field_string' => ['$type' => 'string']],
      ['test_field_string' => ['$exists' => TRUE]],
      ['$or' => [
        ['test_field_string_ascii' => ['$type' => 'string']],
        ['test_field_string_ascii' => ['$exists' => FALSE]]
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.id' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_default' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_default' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.id' => ['$type' => 'int']],
          ['test_table2.id' => ['$exists' => TRUE]],
          ['test_table2.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table2.test_field_int_null' => ['$type' => 'int']],
            ['test_table2.test_field_int_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_int_not_null' => ['$type' => 'int']],
          ['test_table2.test_field_int_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_int_default' => ['$type' => 'int']],
            ['test_table2.test_field_int_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_field_float_null' => ['$type' => 'double']],
            ['test_table2.test_field_float_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_float_not_null' => ['$type' => 'double']],
          ['test_table2.test_field_float_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_float_default' => ['$type' => 'double']],
            ['test_table2.test_field_float_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_field_numeric_null' => ['$type' => 'decimal']],
            ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_numeric_not_null' => ['$type' => 'decimal']],
          ['test_table2.test_field_numeric_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_numeric_default' => ['$type' => 'decimal']],
            ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table3.id' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_varchar_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_varchar_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_varchar_default' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_text_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_text_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_text_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table3.id' => ['$type' => 'int']],
          ['test_table2.test_table3.id' => ['$exists' => TRUE]],
          ['test_table2.test_table3.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table2.test_table3.test_field_varchar_null' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_varchar_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_table3.test_field_varchar_not_null' => ['$type' => 'string']],
          ['test_table2.test_table3.test_field_varchar_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_table3.test_field_varchar_default' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_varchar_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_table3.test_field_text_null' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_text_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_table3.test_field_text_not_null' => ['$type' => 'string']],
          ['test_table2.test_table3.test_field_text_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_table3.test_field_text_default' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_text_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table3.test_table5.id' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_table5.test_field_string' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table3.test_table5.id' => ['$type' => 'int']],
          ['test_table2.test_table3.test_table5.id' => ['$exists' => TRUE]],
          ['test_table2.test_table3.test_table5.id' => ['$gte' => 0]],
          ['test_table2.test_table3.test_table5.test_field_string' => ['$type' => 'string']],
          ['test_table2.test_table3.test_table5.test_field_string' => ['$exists' => TRUE]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table3.test_table6.id' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_table6.test_field_string' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table3.test_table6.id' => ['$type' => 'int']],
          ['test_table2.test_table3.test_table6.id' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_table3.test_table6.test_field_string' => ['$type' => 'string']],
            ['test_table2.test_table3.test_table6.test_field_string' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table4.id' => ['$exists' => FALSE]],
          ['test_table2.test_table4.test_field' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table4.id' => ['$type' => 'int']],
          ['test_table2.test_table4.id' => ['$exists' => TRUE]],
          ['test_table2.test_table4.test_field' => ['$type' => 'int']],
          ['test_table2.test_table4.test_field' => ['$exists' => TRUE]],
        ]],
      ]],
    ]];

    $renamed_validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_renamed_2 = ['$and' => [
      ['id' => ['$type' => 'int']],
      ['id' => ['$exists' => TRUE]],
      ['test_field' => ['$type' => 'int']],
      ['test_field' => ['$exists' => TRUE]],
      ['test_field_string' => ['$type' => 'string']],
      ['test_field_string' => ['$exists' => TRUE]],
      ['$or' => [
        ['test_field_string_ascii' => ['$type' => 'string']],
        ['test_field_string_ascii' => ['$exists' => FALSE]]
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2_new.id' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_int_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_int_not_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_int_default' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_float_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_float_not_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_float_default' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_numeric_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_numeric_not_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_field_numeric_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2_new.id' => ['$type' => 'int']],
          ['test_table2_new.id' => ['$exists' => TRUE]],
          ['test_table2_new.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table2_new.test_field_int_null' => ['$type' => 'int']],
            ['test_table2_new.test_field_int_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2_new.test_field_int_not_null' => ['$type' => 'int']],
          ['test_table2_new.test_field_int_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2_new.test_field_int_default' => ['$type' => 'int']],
            ['test_table2_new.test_field_int_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2_new.test_field_float_null' => ['$type' => 'double']],
            ['test_table2_new.test_field_float_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2_new.test_field_float_not_null' => ['$type' => 'double']],
          ['test_table2_new.test_field_float_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2_new.test_field_float_default' => ['$type' => 'double']],
            ['test_table2_new.test_field_float_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2_new.test_field_numeric_null' => ['$type' => 'decimal']],
            ['test_table2_new.test_field_numeric_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2_new.test_field_numeric_not_null' => ['$type' => 'decimal']],
          ['test_table2_new.test_field_numeric_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2_new.test_field_numeric_default' => ['$type' => 'decimal']],
            ['test_table2_new.test_field_numeric_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2_new.test_table3.id' => ['$exists' => FALSE]],
          ['test_table2_new.test_table3.test_field_varchar_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_table3.test_field_varchar_not_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_table3.test_field_varchar_default' => ['$exists' => FALSE]],
          ['test_table2_new.test_table3.test_field_text_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_table3.test_field_text_not_null' => ['$exists' => FALSE]],
          ['test_table2_new.test_table3.test_field_text_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2_new.test_table3.id' => ['$type' => 'int']],
          ['test_table2_new.test_table3.id' => ['$exists' => TRUE]],
          ['test_table2_new.test_table3.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table2_new.test_table3.test_field_varchar_null' => ['$type' => 'string']],
            ['test_table2_new.test_table3.test_field_varchar_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2_new.test_table3.test_field_varchar_not_null' => ['$type' => 'string']],
          ['test_table2_new.test_table3.test_field_varchar_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2_new.test_table3.test_field_varchar_default' => ['$type' => 'string']],
            ['test_table2_new.test_table3.test_field_varchar_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2_new.test_table3.test_field_text_null' => ['$type' => 'string']],
            ['test_table2_new.test_table3.test_field_text_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2_new.test_table3.test_field_text_not_null' => ['$type' => 'string']],
          ['test_table2_new.test_table3.test_field_text_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2_new.test_table3.test_field_text_default' => ['$type' => 'string']],
            ['test_table2_new.test_table3.test_field_text_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2_new.test_table3.test_table5.id' => ['$exists' => FALSE]],
          ['test_table2_new.test_table3.test_table5.test_field_string' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2_new.test_table3.test_table5.id' => ['$type' => 'int']],
          ['test_table2_new.test_table3.test_table5.id' => ['$exists' => TRUE]],
          ['test_table2_new.test_table3.test_table5.id' => ['$gte' => 0]],
          ['test_table2_new.test_table3.test_table5.test_field_string' => ['$type' => 'string']],
          ['test_table2_new.test_table3.test_table5.test_field_string' => ['$exists' => TRUE]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2_new.test_table3.test_table6.id' => ['$exists' => FALSE]],
          ['test_table2_new.test_table3.test_table6.test_field_string' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2_new.test_table3.test_table6.id' => ['$type' => 'int']],
          ['test_table2_new.test_table3.test_table6.id' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2_new.test_table3.test_table6.test_field_string' => ['$type' => 'string']],
            ['test_table2_new.test_table3.test_table6.test_field_string' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2_new.test_table4.id' => ['$exists' => FALSE]],
          ['test_table2_new.test_table4.test_field' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2_new.test_table4.id' => ['$type' => 'int']],
          ['test_table2_new.test_table4.id' => ['$exists' => TRUE]],
          ['test_table2_new.test_table4.test_field' => ['$type' => 'int']],
          ['test_table2_new.test_table4.test_field' => ['$exists' => TRUE]],
        ]],
      ]],
    ]];

    $renamed_validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_renamed_3 = ['$and' => [
      ['id' => ['$type' => 'int']],
      ['id' => ['$exists' => TRUE]],
      ['test_field' => ['$type' => 'int']],
      ['test_field' => ['$exists' => TRUE]],
      ['test_field_string' => ['$type' => 'string']],
      ['test_field_string' => ['$exists' => TRUE]],
      ['$or' => [
        ['test_field_string_ascii' => ['$type' => 'string']],
        ['test_field_string_ascii' => ['$exists' => FALSE]]
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.id' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_default' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_default' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.id' => ['$type' => 'int']],
          ['test_table2.id' => ['$exists' => TRUE]],
          ['test_table2.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table2.test_field_int_null' => ['$type' => 'int']],
            ['test_table2.test_field_int_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_int_not_null' => ['$type' => 'int']],
          ['test_table2.test_field_int_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_int_default' => ['$type' => 'int']],
            ['test_table2.test_field_int_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_field_float_null' => ['$type' => 'double']],
            ['test_table2.test_field_float_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_float_not_null' => ['$type' => 'double']],
          ['test_table2.test_field_float_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_float_default' => ['$type' => 'double']],
            ['test_table2.test_field_float_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_field_numeric_null' => ['$type' => 'decimal']],
            ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_numeric_not_null' => ['$type' => 'decimal']],
          ['test_table2.test_field_numeric_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_numeric_default' => ['$type' => 'decimal']],
            ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table3_new.id' => ['$exists' => FALSE]],
          ['test_table2.test_table3_new.test_field_varchar_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3_new.test_field_varchar_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3_new.test_field_varchar_default' => ['$exists' => FALSE]],
          ['test_table2.test_table3_new.test_field_text_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3_new.test_field_text_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3_new.test_field_text_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table3_new.id' => ['$type' => 'int']],
          ['test_table2.test_table3_new.id' => ['$exists' => TRUE]],
          ['test_table2.test_table3_new.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table2.test_table3_new.test_field_varchar_null' => ['$type' => 'string']],
            ['test_table2.test_table3_new.test_field_varchar_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_table3_new.test_field_varchar_not_null' => ['$type' => 'string']],
          ['test_table2.test_table3_new.test_field_varchar_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_table3_new.test_field_varchar_default' => ['$type' => 'string']],
            ['test_table2.test_table3_new.test_field_varchar_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_table3_new.test_field_text_null' => ['$type' => 'string']],
            ['test_table2.test_table3_new.test_field_text_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_table3_new.test_field_text_not_null' => ['$type' => 'string']],
          ['test_table2.test_table3_new.test_field_text_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_table3_new.test_field_text_default' => ['$type' => 'string']],
            ['test_table2.test_table3_new.test_field_text_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table3_new.test_table5.id' => ['$exists' => FALSE]],
          ['test_table2.test_table3_new.test_table5.test_field_string' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table3_new.test_table5.id' => ['$type' => 'int']],
          ['test_table2.test_table3_new.test_table5.id' => ['$exists' => TRUE]],
          ['test_table2.test_table3_new.test_table5.id' => ['$gte' => 0]],
          ['test_table2.test_table3_new.test_table5.test_field_string' => ['$type' => 'string']],
          ['test_table2.test_table3_new.test_table5.test_field_string' => ['$exists' => TRUE]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table3_new.test_table6.id' => ['$exists' => FALSE]],
          ['test_table2.test_table3_new.test_table6.test_field_string' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table3_new.test_table6.id' => ['$type' => 'int']],
          ['test_table2.test_table3_new.test_table6.id' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_table3_new.test_table6.test_field_string' => ['$type' => 'string']],
            ['test_table2.test_table3_new.test_table6.test_field_string' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table4.id' => ['$exists' => FALSE]],
          ['test_table2.test_table4.test_field' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table4.id' => ['$type' => 'int']],
          ['test_table2.test_table4.id' => ['$exists' => TRUE]],
          ['test_table2.test_table4.test_field' => ['$type' => 'int']],
          ['test_table2.test_table4.test_field' => ['$exists' => TRUE]],
        ]],
      ]],
    ]];

    $renamed_validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_renamed_4 = ['$and' => [
      ['id' => ['$type' => 'int']],
      ['id' => ['$exists' => TRUE]],
      ['test_field' => ['$type' => 'int']],
      ['test_field' => ['$exists' => TRUE]],
      ['test_field_string' => ['$type' => 'string']],
      ['test_field_string' => ['$exists' => TRUE]],
      ['$or' => [
        ['test_field_string_ascii' => ['$type' => 'string']],
        ['test_field_string_ascii' => ['$exists' => FALSE]]
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.id' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_default' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_default' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.id' => ['$type' => 'int']],
          ['test_table2.id' => ['$exists' => TRUE]],
          ['test_table2.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table2.test_field_int_null' => ['$type' => 'int']],
            ['test_table2.test_field_int_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_int_not_null' => ['$type' => 'int']],
          ['test_table2.test_field_int_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_int_default' => ['$type' => 'int']],
            ['test_table2.test_field_int_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_field_float_null' => ['$type' => 'double']],
            ['test_table2.test_field_float_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_float_not_null' => ['$type' => 'double']],
          ['test_table2.test_field_float_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_float_default' => ['$type' => 'double']],
            ['test_table2.test_field_float_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_field_numeric_null' => ['$type' => 'decimal']],
            ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_numeric_not_null' => ['$type' => 'decimal']],
          ['test_table2.test_field_numeric_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_numeric_default' => ['$type' => 'decimal']],
            ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table3.id' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_varchar_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_varchar_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_varchar_default' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_text_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_text_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_text_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table3.id' => ['$type' => 'int']],
          ['test_table2.test_table3.id' => ['$exists' => TRUE]],
          ['test_table2.test_table3.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table2.test_table3.test_field_varchar_null' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_varchar_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_table3.test_field_varchar_not_null' => ['$type' => 'string']],
          ['test_table2.test_table3.test_field_varchar_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_table3.test_field_varchar_default' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_varchar_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_table3.test_field_text_null' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_text_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_table3.test_field_text_not_null' => ['$type' => 'string']],
          ['test_table2.test_table3.test_field_text_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_table3.test_field_text_default' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_text_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table3.test_table5.id' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_table5.test_field_string' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table3.test_table5.id' => ['$type' => 'int']],
          ['test_table2.test_table3.test_table5.id' => ['$exists' => TRUE]],
          ['test_table2.test_table3.test_table5.id' => ['$gte' => 0]],
          ['test_table2.test_table3.test_table5.test_field_string' => ['$type' => 'string']],
          ['test_table2.test_table3.test_table5.test_field_string' => ['$exists' => TRUE]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table3.test_table6.id' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_table6.test_field_string' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table3.test_table6.id' => ['$type' => 'int']],
          ['test_table2.test_table3.test_table6.id' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_table3.test_table6.test_field_string' => ['$type' => 'string']],
            ['test_table2.test_table3.test_table6.test_field_string' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table4_new.id' => ['$exists' => FALSE]],
          ['test_table2.test_table4_new.test_field' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table4_new.id' => ['$type' => 'int']],
          ['test_table2.test_table4_new.id' => ['$exists' => TRUE]],
          ['test_table2.test_table4_new.test_field' => ['$type' => 'int']],
          ['test_table2.test_table4_new.test_field' => ['$exists' => TRUE]],
        ]],
      ]],
    ]];

    $renamed_validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_renamed_6 = ['$and' => [
      ['id' => ['$type' => 'int']],
      ['id' => ['$exists' => TRUE]],
      ['test_field' => ['$type' => 'int']],
      ['test_field' => ['$exists' => TRUE]],
      ['test_field_string' => ['$type' => 'string']],
      ['test_field_string' => ['$exists' => TRUE]],
      ['$or' => [
        ['test_field_string_ascii' => ['$type' => 'string']],
        ['test_field_string_ascii' => ['$exists' => FALSE]]
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.id' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_default' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_default' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.id' => ['$type' => 'int']],
          ['test_table2.id' => ['$exists' => TRUE]],
          ['test_table2.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table2.test_field_int_null' => ['$type' => 'int']],
            ['test_table2.test_field_int_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_int_not_null' => ['$type' => 'int']],
          ['test_table2.test_field_int_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_int_default' => ['$type' => 'int']],
            ['test_table2.test_field_int_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_field_float_null' => ['$type' => 'double']],
            ['test_table2.test_field_float_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_float_not_null' => ['$type' => 'double']],
          ['test_table2.test_field_float_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_float_default' => ['$type' => 'double']],
            ['test_table2.test_field_float_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_field_numeric_null' => ['$type' => 'decimal']],
            ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_numeric_not_null' => ['$type' => 'decimal']],
          ['test_table2.test_field_numeric_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_numeric_default' => ['$type' => 'decimal']],
            ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table3.id' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_varchar_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_varchar_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_varchar_default' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_text_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_text_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_text_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table3.id' => ['$type' => 'int']],
          ['test_table2.test_table3.id' => ['$exists' => TRUE]],
          ['test_table2.test_table3.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table2.test_table3.test_field_varchar_null' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_varchar_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_table3.test_field_varchar_not_null' => ['$type' => 'string']],
          ['test_table2.test_table3.test_field_varchar_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_table3.test_field_varchar_default' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_varchar_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_table3.test_field_text_null' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_text_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_table3.test_field_text_not_null' => ['$type' => 'string']],
          ['test_table2.test_table3.test_field_text_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_table3.test_field_text_default' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_text_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table3.test_table5.id' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_table5.test_field_string' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table3.test_table5.id' => ['$type' => 'int']],
          ['test_table2.test_table3.test_table5.id' => ['$exists' => TRUE]],
          ['test_table2.test_table3.test_table5.id' => ['$gte' => 0]],
          ['test_table2.test_table3.test_table5.test_field_string' => ['$type' => 'string']],
          ['test_table2.test_table3.test_table5.test_field_string' => ['$exists' => TRUE]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table3.test_table6_new.id' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_table6_new.test_field_string' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table3.test_table6_new.id' => ['$type' => 'int']],
          ['test_table2.test_table3.test_table6_new.id' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_table3.test_table6_new.test_field_string' => ['$type' => 'string']],
            ['test_table2.test_table3.test_table6_new.test_field_string' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table4.id' => ['$exists' => FALSE]],
          ['test_table2.test_table4.test_field' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table4.id' => ['$type' => 'int']],
          ['test_table2.test_table4.id' => ['$exists' => TRUE]],
          ['test_table2.test_table4.test_field' => ['$type' => 'int']],
          ['test_table2.test_table4.test_field' => ['$exists' => TRUE]],
        ]],
      ]],
    ]];

    $renamed_embedded_indexes_base_1_embedded_2 = [
      ['name' => '_id_', 'unique' => FALSE, 'key' => ['_id' => 1]],
      ['name' => '__pkey', 'unique' => TRUE, 'key' => ['id' => 1]],
      ['name' => 'test_field__key', 'unique' => TRUE, 'key' => ['test_field' => 1]],
      ['name' => 'test_table2_new.__pkey', 'unique' => TRUE, 'key' => ['test_table2_new.id' => 1]],
      ['name' => 'test_table2_new.test_fields_not_null__key', 'unique' => TRUE, 'key' => ['test_table2_new.test_field_int_not_null' => 1, 'test_table2_new.test_field_float_not_null' => 1, 'test_table2_new.test_field_numeric_not_null' => 1]],
      ['name' => 'test_table2_new.test_fields_null__key', 'unique' => TRUE, 'key' => ['test_table2_new.test_field_int_null' => 1, 'test_table2_new.test_field_float_null' => 1, 'test_table2_new.test_field_numeric_null' => 1]],
      ['name' => 'test_table2_new.test_fields_default__idx', 'unique' => FALSE, 'key' => ['test_table2_new.test_field_int_default' => 1, 'test_table2_new.test_field_float_default' => 1, 'test_table2_new.test_field_numeric_default' => 1]],
      ['name' => 'test_table2_new.test_field_int_null__idx', 'unique' => FALSE, 'key' => ['test_table2_new.test_field_int_null' => 1]],
    ];

    $renamed_embedded_indexes_base_1_embedded_3 = [
      ['name' => '_id_', 'unique' => FALSE, 'key' => ['_id' => 1]],
      ['name' => '__pkey', 'unique' => TRUE, 'key' => ['id' => 1]],
      ['name' => 'test_field__key', 'unique' => TRUE, 'key' => ['test_field' => 1]],
    ];

    $renamed_embedded_indexes_base_1_embedded_2_and_3 = [
      ['name' => '_id_', 'unique' => FALSE, 'key' => ['_id' => 1]],
      ['name' => '__pkey', 'unique' => TRUE, 'key' => ['id' => 1]],
      ['name' => 'test_field__key', 'unique' => TRUE, 'key' => ['test_field' => 1]],
      ['name' => 'test_table2_new.__pkey', 'unique' => TRUE, 'key' => ['test_table2_new.id' => 1]],
      ['name' => 'test_table2_new.test_fields_not_null__key', 'unique' => TRUE, 'key' => ['test_table2_new.test_field_int_not_null' => 1, 'test_table2_new.test_field_float_not_null' => 1, 'test_table2_new.test_field_numeric_not_null' => 1]],
      ['name' => 'test_table2_new.test_fields_null__key', 'unique' => TRUE, 'key' => ['test_table2_new.test_field_int_null' => 1, 'test_table2_new.test_field_float_null' => 1, 'test_table2_new.test_field_numeric_null' => 1]],
      ['name' => 'test_table2_new.test_fields_default__idx', 'unique' => FALSE, 'key' => ['test_table2_new.test_field_int_default' => 1, 'test_table2_new.test_field_float_default' => 1, 'test_table2_new.test_field_numeric_default' => 1]],
      ['name' => 'test_table2_new.test_field_int_null__idx', 'unique' => FALSE, 'key' => ['test_table2_new.test_field_int_null' => 1]],
    ];

    $renamed_table_indexes_base_1_embedded_2_and_3_on_2_before = $renamed_table_indexes_base_1_embedded_2_and_3_on_2_after_renamed_1 = $renamed_table_indexes_base_1_embedded_2_and_3_on_2_after_renamed_3 = [
      ['name' => '_id_', 'unique' => FALSE, 'key' => ['_id' => 1]],
      ['name' => '__pkey', 'unique' => TRUE, 'key' => ['id' => 1]],
      ['name' => 'test_field__key', 'unique' => TRUE, 'key' => ['test_field' => 1]],
      ['name' => 'test_table2.__pkey', 'unique' => TRUE, 'key' => ['test_table2.id' => 1]],
      ['name' => 'test_table2.test_fields_not_null__key', 'unique' => TRUE, 'key' => ['test_table2.test_field_int_not_null' => 1, 'test_table2.test_field_float_not_null' => 1, 'test_table2.test_field_numeric_not_null' => 1]],
      ['name' => 'test_table2.test_fields_null__key', 'unique' => TRUE, 'key' => ['test_table2.test_field_int_null' => 1, 'test_table2.test_field_float_null' => 1, 'test_table2.test_field_numeric_null' => 1]],
      ['name' => 'test_table2.test_fields_default__idx', 'unique' => FALSE, 'key' => ['test_table2.test_field_int_default' => 1, 'test_table2.test_field_float_default' => 1, 'test_table2.test_field_numeric_default' => 1]],
      ['name' => 'test_table2.test_field_int_null__idx', 'unique' => FALSE, 'key' => ['test_table2.test_field_int_null' => 1]],
    ];

    $renamed_table_indexes_base_1_embedded_2_and_3_on_2_after_renamed_2 = [
      ['name' => '_id_', 'unique' => FALSE, 'key' => ['_id' => 1]],
      ['name' => '__pkey', 'unique' => TRUE, 'key' => ['id' => 1]],
      ['name' => 'test_field__key', 'unique' => TRUE, 'key' => ['test_field' => 1]],
      ['name' => 'test_table2_new.__pkey', 'unique' => TRUE, 'key' => ['test_table2_new.id' => 1]],
      ['name' => 'test_table2_new.test_fields_not_null__key', 'unique' => TRUE, 'key' => ['test_table2_new.test_field_int_not_null' => 1, 'test_table2_new.test_field_float_not_null' => 1, 'test_table2_new.test_field_numeric_not_null' => 1]],
      ['name' => 'test_table2_new.test_fields_null__key', 'unique' => TRUE, 'key' => ['test_table2_new.test_field_int_null' => 1, 'test_table2_new.test_field_float_null' => 1, 'test_table2_new.test_field_numeric_null' => 1]],
      ['name' => 'test_table2_new.test_fields_default__idx', 'unique' => FALSE, 'key' => ['test_table2_new.test_field_int_default' => 1, 'test_table2_new.test_field_float_default' => 1, 'test_table2_new.test_field_numeric_default' => 1]],
      ['name' => 'test_table2_new.test_field_int_null__idx', 'unique' => FALSE, 'key' => ['test_table2_new.test_field_int_null' => 1]],
    ];

    $renamed_table_indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_before = $renamed_table_indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_renamed_1 = [
      ['name' => '_id_', 'unique' => FALSE, 'key' => ['_id' => 1]],
      ['name' => '__pkey', 'unique' => TRUE, 'key' => ['id' => 1]],
      ['name' => 'test_field__key', 'unique' => TRUE, 'key' => ['test_field' => 1]],
      ['name' => 'test_table2.__pkey', 'unique' => TRUE, 'key' => ['test_table2.id' => 1]],
      ['name' => 'test_table2.test_fields_not_null__key', 'unique' => TRUE, 'key' => ['test_table2.test_field_int_not_null' => 1, 'test_table2.test_field_float_not_null' => 1, 'test_table2.test_field_numeric_not_null' => 1]],
      ['name' => 'test_table2.test_fields_null__key', 'unique' => TRUE, 'key' => ['test_table2.test_field_int_null' => 1, 'test_table2.test_field_float_null' => 1, 'test_table2.test_field_numeric_null' => 1]],
      ['name' => 'test_table2.test_fields_default__idx', 'unique' => FALSE, 'key' => ['test_table2.test_field_int_default' => 1, 'test_table2.test_field_float_default' => 1, 'test_table2.test_field_numeric_default' => 1]],
      ['name' => 'test_table2.test_field_int_null__idx', 'unique' => FALSE, 'key' => ['test_table2.test_field_int_null' => 1]],
      ['name' => 'test_table2.test_table4.__pkey', 'unique' => TRUE, 'key' => ['test_table2.test_table4.id' => 1]],
      ['name' => 'test_table2.test_table4.test_field__key', 'unique' => TRUE, 'key' => ['test_table2.test_table4.test_field' => 1]],
      ['name' => 'test_table2.test_table3.test_table6.__pkey', 'unique' => TRUE, 'key' => ['test_table2.test_table3.test_table6.id' => 1]],
      ['name' => 'test_table2.test_table3.test_table6.test_field_string__idx', 'unique' => FALSE, 'key' => ['test_table2.test_table3.test_table6.test_field_string' => 1]],
    ];

    $renamed_table_indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_renamed_2 = [
      ['name' => '_id_', 'unique' => FALSE, 'key' => ['_id' => 1]],
      ['name' => '__pkey', 'unique' => TRUE, 'key' => ['id' => 1]],
      ['name' => 'test_field__key', 'unique' => TRUE, 'key' => ['test_field' => 1]],
      ['name' => 'test_table2_new.__pkey', 'unique' => TRUE, 'key' => ['test_table2_new.id' => 1]],
      ['name' => 'test_table2_new.test_fields_not_null__key', 'unique' => TRUE, 'key' => ['test_table2_new.test_field_int_not_null' => 1, 'test_table2_new.test_field_float_not_null' => 1, 'test_table2_new.test_field_numeric_not_null' => 1]],
      ['name' => 'test_table2_new.test_fields_null__key', 'unique' => TRUE, 'key' => ['test_table2_new.test_field_int_null' => 1, 'test_table2_new.test_field_float_null' => 1, 'test_table2_new.test_field_numeric_null' => 1]],
      ['name' => 'test_table2_new.test_fields_default__idx', 'unique' => FALSE, 'key' => ['test_table2_new.test_field_int_default' => 1, 'test_table2_new.test_field_float_default' => 1, 'test_table2_new.test_field_numeric_default' => 1]],
      ['name' => 'test_table2_new.test_field_int_null__idx', 'unique' => FALSE, 'key' => ['test_table2_new.test_field_int_null' => 1]],
      ['name' => 'test_table2_new.test_table4.__pkey', 'unique' => TRUE, 'key' => ['test_table2_new.test_table4.id' => 1]],
      ['name' => 'test_table2_new.test_table4.test_field__key', 'unique' => TRUE, 'key' => ['test_table2_new.test_table4.test_field' => 1]],
      ['name' => 'test_table2_new.test_table3.test_table6.__pkey', 'unique' => TRUE, 'key' => ['test_table2_new.test_table3.test_table6.id' => 1]],
      ['name' => 'test_table2_new.test_table3.test_table6.test_field_string__idx', 'unique' => FALSE, 'key' => ['test_table2_new.test_table3.test_table6.test_field_string' => 1]],
    ];

    $renamed_table_indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_renamed_3 = [
      ['name' => '_id_', 'unique' => FALSE, 'key' => ['_id' => 1]],
      ['name' => '__pkey', 'unique' => TRUE, 'key' => ['id' => 1]],
      ['name' => 'test_field__key', 'unique' => TRUE, 'key' => ['test_field' => 1]],
      ['name' => 'test_table2.__pkey', 'unique' => TRUE, 'key' => ['test_table2.id' => 1]],
      ['name' => 'test_table2.test_fields_not_null__key', 'unique' => TRUE, 'key' => ['test_table2.test_field_int_not_null' => 1, 'test_table2.test_field_float_not_null' => 1, 'test_table2.test_field_numeric_not_null' => 1]],
      ['name' => 'test_table2.test_fields_null__key', 'unique' => TRUE, 'key' => ['test_table2.test_field_int_null' => 1, 'test_table2.test_field_float_null' => 1, 'test_table2.test_field_numeric_null' => 1]],
      ['name' => 'test_table2.test_fields_default__idx', 'unique' => FALSE, 'key' => ['test_table2.test_field_int_default' => 1, 'test_table2.test_field_float_default' => 1, 'test_table2.test_field_numeric_default' => 1]],
      ['name' => 'test_table2.test_field_int_null__idx', 'unique' => FALSE, 'key' => ['test_table2.test_field_int_null' => 1]],
      ['name' => 'test_table2.test_table4.__pkey', 'unique' => TRUE, 'key' => ['test_table2.test_table4.id' => 1]],
      ['name' => 'test_table2.test_table4.test_field__key', 'unique' => TRUE, 'key' => ['test_table2.test_table4.test_field' => 1]],
      ['name' => 'test_table2.test_table3_new.test_table6.__pkey', 'unique' => TRUE, 'key' => ['test_table2.test_table3_new.test_table6.id' => 1]],
      ['name' => 'test_table2.test_table3_new.test_table6.test_field_string__idx', 'unique' => FALSE, 'key' => ['test_table2.test_table3_new.test_table6.test_field_string' => 1]],
    ];

    $renamed_table_indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_renamed_4 = [
      ['name' => '_id_', 'unique' => FALSE, 'key' => ['_id' => 1]],
      ['name' => '__pkey', 'unique' => TRUE, 'key' => ['id' => 1]],
      ['name' => 'test_field__key', 'unique' => TRUE, 'key' => ['test_field' => 1]],
      ['name' => 'test_table2.__pkey', 'unique' => TRUE, 'key' => ['test_table2.id' => 1]],
      ['name' => 'test_table2.test_fields_not_null__key', 'unique' => TRUE, 'key' => ['test_table2.test_field_int_not_null' => 1, 'test_table2.test_field_float_not_null' => 1, 'test_table2.test_field_numeric_not_null' => 1]],
      ['name' => 'test_table2.test_fields_null__key', 'unique' => TRUE, 'key' => ['test_table2.test_field_int_null' => 1, 'test_table2.test_field_float_null' => 1, 'test_table2.test_field_numeric_null' => 1]],
      ['name' => 'test_table2.test_fields_default__idx', 'unique' => FALSE, 'key' => ['test_table2.test_field_int_default' => 1, 'test_table2.test_field_float_default' => 1, 'test_table2.test_field_numeric_default' => 1]],
      ['name' => 'test_table2.test_field_int_null__idx', 'unique' => FALSE, 'key' => ['test_table2.test_field_int_null' => 1]],
      ['name' => 'test_table2.test_table4_new.__pkey', 'unique' => TRUE, 'key' => ['test_table2.test_table4_new.id' => 1]],
      ['name' => 'test_table2.test_table4_new.test_field__key', 'unique' => TRUE, 'key' => ['test_table2.test_table4_new.test_field' => 1]],
      ['name' => 'test_table2.test_table3.test_table6.__pkey', 'unique' => TRUE, 'key' => ['test_table2.test_table3.test_table6.id' => 1]],
      ['name' => 'test_table2.test_table3.test_table6.test_field_string__idx', 'unique' => FALSE, 'key' => ['test_table2.test_table3.test_table6.test_field_string' => 1]],
    ];

    $renamed_table_indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_renamed_6 = [
      ['name' => '_id_', 'unique' => FALSE, 'key' => ['_id' => 1]],
      ['name' => '__pkey', 'unique' => TRUE, 'key' => ['id' => 1]],
      ['name' => 'test_field__key', 'unique' => TRUE, 'key' => ['test_field' => 1]],
      ['name' => 'test_table2.__pkey', 'unique' => TRUE, 'key' => ['test_table2.id' => 1]],
      ['name' => 'test_table2.test_fields_not_null__key', 'unique' => TRUE, 'key' => ['test_table2.test_field_int_not_null' => 1, 'test_table2.test_field_float_not_null' => 1, 'test_table2.test_field_numeric_not_null' => 1]],
      ['name' => 'test_table2.test_fields_null__key', 'unique' => TRUE, 'key' => ['test_table2.test_field_int_null' => 1, 'test_table2.test_field_float_null' => 1, 'test_table2.test_field_numeric_null' => 1]],
      ['name' => 'test_table2.test_fields_default__idx', 'unique' => FALSE, 'key' => ['test_table2.test_field_int_default' => 1, 'test_table2.test_field_float_default' => 1, 'test_table2.test_field_numeric_default' => 1]],
      ['name' => 'test_table2.test_field_int_null__idx', 'unique' => FALSE, 'key' => ['test_table2.test_field_int_null' => 1]],
      ['name' => 'test_table2.test_table4.__pkey', 'unique' => TRUE, 'key' => ['test_table2.test_table4.id' => 1]],
      ['name' => 'test_table2.test_table4.test_field__key', 'unique' => TRUE, 'key' => ['test_table2.test_table4.test_field' => 1]],
      ['name' => 'test_table2.test_table3.test_table6_new.__pkey', 'unique' => TRUE, 'key' => ['test_table2.test_table3.test_table6_new.id' => 1]],
      ['name' => 'test_table2.test_table3.test_table6_new.test_field_string__idx', 'unique' => FALSE, 'key' => ['test_table2.test_table3.test_table6_new.test_field_string' => 1]],
    ];

    return [
      [
        $this->test_table1,
        [],
        $this->test_table1['name'],
        $this->test_table1['validation'],
        $this->test_table1['validation'],
        $this->test_table1['indexes'],
        $this->test_table1['indexes'],
      ],
      [
        $this->test_table2,
        [],
        $this->test_table2['name'],
        $this->test_table2['validation'],
        $this->test_table2['validation'],
        $this->test_table2['indexes'],
        $this->test_table2['indexes'],
      ],
      [
        $this->test_table3,
        [],
        $this->test_table3['name'],
        $this->test_table3['validation'],
        $this->test_table3['validation'],
        $this->test_table3['indexes'],
        $this->test_table3['indexes'],
      ],
      [
        $this->test_table4,
        [],
        $this->test_table4['name'],
        $this->test_table4['validation'],
        $this->test_table4['validation'],
        $this->test_table4['indexes'],
        $this->test_table4['indexes'],
      ],
      [
        $this->test_table5,
        [],
        $this->test_table5['name'],
        $this->test_table5['validation'],
        $this->test_table5['validation'],
        $this->test_table5['indexes'],
        $this->test_table5['indexes'],
      ],
      [
        $this->test_table6,
        [],
        $this->test_table6['name'],
        $this->test_table6['validation'],
        $this->test_table6['validation'],
        $this->test_table6['indexes'],
        $this->test_table6['indexes'],
      ],
      [
        $this->test_table1,
        [$this->test_table1['name'] => [$this->test_table2]],
        $this->test_table2['name'],
        $this->embedded_validation_base_1_embedded_2,
        $renamed_embedded_validation_base_1_embedded_2,
        $this->embedded_indexes_base_1_embedded_2,
        $renamed_embedded_indexes_base_1_embedded_2,
      ],
      [
        $this->test_table1,
        [$this->test_table1['name'] => [$this->test_table3]],
        $this->test_table3['name'],
        $this->embedded_validation_base_1_embedded_3,
        $renamed_embedded_validation_base_1_embedded_3,
        $this->embedded_indexes_base_1_embedded_3,
        $renamed_embedded_indexes_base_1_embedded_3,
      ],
      [
        $this->test_table1,
        [$this->test_table1['name'] => [$this->test_table2, $this->test_table3]],
        $this->test_table2['name'],
        $this->embedded_validation_base_1_embedded_2_and_3,
        $renamed_embedded_validation_base_1_embedded_2_and_3,
        $this->embedded_indexes_base_1_embedded_2_and_3,
        $renamed_embedded_indexes_base_1_embedded_2_and_3,
      ],
      [
        $this->test_table1,
        [$this->test_table1['name'] => [$this->test_table2], $this->test_table2['name'] => [$this->test_table3]],
        $this->test_table1['name'],
        $renamed_validation_base_1_embedded_2_and_3_on_2_before,
        $renamed_validation_base_1_embedded_2_and_3_on_2_after_renamed_1,
        $renamed_table_indexes_base_1_embedded_2_and_3_on_2_before,
        $renamed_table_indexes_base_1_embedded_2_and_3_on_2_after_renamed_1,
      ],
      [
        $this->test_table1,
        [$this->test_table1['name'] => [$this->test_table2], $this->test_table2['name'] => [$this->test_table3]],
        $this->test_table2['name'],
        $renamed_validation_base_1_embedded_2_and_3_on_2_before,
        $renamed_validation_base_1_embedded_2_and_3_on_2_after_renamed_2,
        $renamed_table_indexes_base_1_embedded_2_and_3_on_2_before,
        $renamed_table_indexes_base_1_embedded_2_and_3_on_2_after_renamed_2,
      ],
      [
        $this->test_table1,
        [$this->test_table1['name'] => [$this->test_table2], $this->test_table2['name'] => [$this->test_table3]],
        $this->test_table3['name'],
        $renamed_validation_base_1_embedded_2_and_3_on_2_before,
        $renamed_validation_base_1_embedded_2_and_3_on_2_after_renamed_3,
        $renamed_table_indexes_base_1_embedded_2_and_3_on_2_before,
        $renamed_table_indexes_base_1_embedded_2_and_3_on_2_after_renamed_3,
      ],
      [
        $this->test_table1,
        [$this->test_table1['name'] => [$this->test_table2], $this->test_table2['name'] => [$this->test_table3, $this->test_table4], $this->test_table3['name'] => [$this->test_table5, $this->test_table6]],
        $this->test_table1['name'],
        $renamed_validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_before,
        $renamed_validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_renamed_1,
        $renamed_table_indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_before,
        $renamed_table_indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_renamed_1,
      ],
      [
        $this->test_table1,
        [$this->test_table1['name'] => [$this->test_table2], $this->test_table2['name'] => [$this->test_table3, $this->test_table4], $this->test_table3['name'] => [$this->test_table5, $this->test_table6]],
        $this->test_table2['name'],
        $renamed_validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_before,
        $renamed_validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_renamed_2,
        $renamed_table_indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_before,
        $renamed_table_indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_renamed_2,
      ],
      [
        $this->test_table1,
        [$this->test_table1['name'] => [$this->test_table2], $this->test_table2['name'] => [$this->test_table3, $this->test_table4], $this->test_table3['name'] => [$this->test_table5, $this->test_table6]],
        $this->test_table3['name'],
        $renamed_validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_before,
        $renamed_validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_renamed_3,
        $renamed_table_indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_before,
        $renamed_table_indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_renamed_3,
      ],
      [
        $this->test_table1,
        [$this->test_table1['name'] => [$this->test_table2], $this->test_table2['name'] => [$this->test_table3, $this->test_table4], $this->test_table3['name'] => [$this->test_table5, $this->test_table6]],
        $this->test_table4['name'],
        $renamed_validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_before,
        $renamed_validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_renamed_4,
        $renamed_table_indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_before,
        $renamed_table_indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_renamed_4,
      ],
      [
        $this->test_table1,
        [$this->test_table1['name'] => [$this->test_table2], $this->test_table2['name'] => [$this->test_table3, $this->test_table4], $this->test_table3['name'] => [$this->test_table5, $this->test_table6]],
        $this->test_table6['name'],
        $renamed_validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_before,
        $renamed_validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_renamed_6,
        $renamed_table_indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_before,
        $renamed_table_indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_renamed_6,
      ],
    ];
  }

  /**
   * @covers ::createTable
   * @covers ::createEmbeddedTable
   * @covers ::renameTable
   * @covers ::tableExists
   * @dataProvider providerRenameTable
   */
  public function testRenameTable($base_table_data, $embedded_tables_data, $renamed_table_name, $expected_validation_before, $expected_validation_after, $expected_indexes_before, $expected_indexes_after) {
    $schema = Database::getConnection()->schema();

    $renamed_table_name_old = $renamed_table_name;
    $renamed_table_name_new = $renamed_table_name . '_new';

    // Create all the tables.
    $schema->createTable($base_table_data['name'], $base_table_data['schema']);
    foreach ($embedded_tables_data as $parent_table_name => $embedded_table_array) {
      foreach ($embedded_table_array as $embedded_table_data) {
        $schema->createEmbeddedTable($parent_table_name, $embedded_table_data['name'], $embedded_table_data['schema']);
      }
    }

    // Test everything before the table has been renamed.
    $this->assertTrue($schema->tableExists($base_table_data['name']), 'The table exists in the MongoDB database.');
    foreach ($embedded_tables_data as $parent_table_name => $embedded_table_array) {
      foreach ($embedded_table_array as $embedded_table_data) {
        $this->assertTrue($schema->tableExists($embedded_table_data['name']), 'The embedded table exists in the MongoDB database.');
      }
    }
    $this->checkTableValidation($base_table_data['name'], $expected_validation_before);
    $this->checkTableSchema($base_table_data['name'], $base_table_data['schema']);
    foreach ($embedded_tables_data as $parent_table_name => $embedded_table_array) {
      foreach ($embedded_table_array as $embedded_table_data) {
        $embedded_table_data['schema']['embedded_to_table'] = $parent_table_name;
        $this->checkTableSchema($embedded_table_data['name'], $embedded_table_data['schema']);
      }
    }

    $this->checkTableIndexes($base_table_data['name'], $base_table_data['schema']);
    foreach ($embedded_tables_data as $parent_table_name => $embedded_table_array) {
      foreach ($embedded_table_array as $embedded_table_data) {
        $this->checkEmbeddedTableIndexes($parent_table_name, $embedded_table_data['name'], $embedded_table_data['schema']);
      }
    }
    $this->checkExpectedIndexesAgainstDatabase($base_table_data['name'], $expected_indexes_before);
    $this->checkTableNumberOfIndexes($base_table_data, $embedded_tables_data);

    // Call the to be tested method: Schema::renameTable().
    $schema->renameTable($renamed_table_name_old, $renamed_table_name_new);

    // Test everything after the table has been renamed.
    if ($base_table_data['name'] == $renamed_table_name_old) {
      $this->assertFalse($schema->tableExists($base_table_data['name']), 'The table does not exist in the MongoDB database.');
    }
    else {
      $this->assertTrue($schema->tableExists($base_table_data['name']), 'The table exists in the MongoDB database.');
    }
    foreach ($embedded_tables_data as $parent_table_name => $embedded_table_array) {
      foreach ($embedded_table_array as $embedded_table_data) {
        if ($embedded_table_data['name'] == $renamed_table_name_old) {
          $this->assertTrue($schema->tableExists($renamed_table_name_new), 'The embedded table does not exist in the MongoDB database.');
        }
        else {
          $this->assertTrue($schema->tableExists($embedded_table_data['name']), 'The embedded table exists in the MongoDB database.');
        }
      }
    }
    if ($base_table_data['name'] != $renamed_table_name_old) {
      $this->checkTableValidation($base_table_data['name'], $expected_validation_after);
      $this->checkTableSchema($base_table_data['name'], $base_table_data['schema']);
    }
    foreach ($embedded_tables_data as $parent_table_name => $embedded_table_array) {
      foreach ($embedded_table_array as $embedded_table_data) {
        if ($parent_table_name == $renamed_table_name_old) {
          $embedded_table_data['schema']['embedded_to_table'] = $renamed_table_name_new;
        }
        else {
          $embedded_table_data['schema']['embedded_to_table'] = $parent_table_name;
        }
        if ($embedded_table_data['name'] == $renamed_table_name_old) {
          $this->checkTableSchema($renamed_table_name_new, $embedded_table_data['schema']);
        }
      }
    }
    if ($base_table_data['name'] == $renamed_table_name_old) {
      $this->checkTableIndexes($renamed_table_name_new, $base_table_data['schema']);
    }
    else {
      $this->checkTableIndexes($base_table_data['name'], $base_table_data['schema']);
    }
    foreach ($embedded_tables_data as $parent_table_name => $embedded_table_array) {
      foreach ($embedded_table_array as $embedded_table_data) {
        if ($parent_table_name == $renamed_table_name_old) {
          $this->checkEmbeddedTableIndexes($renamed_table_name_new, $embedded_table_data['name'], $embedded_table_data['schema']);
        }
        elseif ($embedded_table_data['name'] == $renamed_table_name_old) {
          $this->checkEmbeddedTableIndexes($parent_table_name, $renamed_table_name_new, $embedded_table_data['schema']);
        }
        else {
          $this->checkEmbeddedTableIndexes($parent_table_name, $embedded_table_data['name'], $embedded_table_data['schema']);
        }
      }
    }
    if ($base_table_data['name'] == $renamed_table_name_old) {
      $this->checkExpectedIndexesAgainstDatabase($renamed_table_name_new, $expected_indexes_after);
      $base_table_data['name'] = $renamed_table_name_new;
    }
    else {
      $this->checkExpectedIndexesAgainstDatabase($base_table_data['name'], $expected_indexes_after);
    }
    $embedded_tables_data_after = [];
    foreach ($embedded_tables_data as $parent_table_name => $embedded_table_array) {
      if ($parent_table_name == $renamed_table_name_old) {
        $parent_table_name = $renamed_table_name_new;
      }
      foreach ($embedded_table_array as $embedded_table_data) {
        if ($embedded_table_data['name'] == $renamed_table_name_old) {
          $embedded_table_data['name'] = $renamed_table_name_new;
        }
        if (empty($embedded_tables_data_after[$parent_table_name])) {
          $embedded_tables_data_after[$parent_table_name] = [$embedded_table_data];
        }
        else {
          array_push($embedded_tables_data_after[$parent_table_name], $embedded_table_data);
        }
      }
    }
    $this->checkTableNumberOfIndexes($base_table_data, $embedded_tables_data_after);
  }

  /**
   * @covers ::renameTable
   */
  public function testRenameTableForTableDoesNotExist() {
    $schema = Database::getConnection()->schema();

    $this->assertFalse($schema->tableExists($this->test_table1['name']), 'The table does not exist in the MongoDB database.');

    // If we try to delete a table on a non existent base table an exception should be thrown.
    $this->setExpectedException('Drupal\Core\Database\SchemaObjectDoesNotExistException');
    $schema->renameTable($this->test_table1['name'], $this->test_table2['name']);
  }

  /**
   * @covers ::renameTable
   */
  public function testRenameTableForNewTableExistsAsBaseTable() {
    $schema = Database::getConnection()->schema();

    $this->assertFalse($schema->tableExists($this->test_table1['name']), 'The table does not exist in the MongoDB database.');
    $this->assertFalse($schema->tableExists($this->test_table2['name']), 'The table does not exist in the MongoDB database.');

    // Create the to be renamed table.
    $schema->createTable($this->test_table1['name'], $this->test_table1['schema']);
    $schema->createTable($this->test_table2['name'], $this->test_table2['schema']);

    $this->assertTrue($schema->tableExists($this->test_table1['name']), 'The table exists in the MongoDB database.');
    $this->assertTrue($schema->tableExists($this->test_table2['name']), 'The table exists in the MongoDB database.');

    // If we try to delete a table on a non existent base table an exception should be thrown.
    $this->setExpectedException('Drupal\Core\Database\SchemaObjectExistsException');
    $schema->renameTable($this->test_table1['name'], $this->test_table2['name']);
  }

  /**
   * @covers ::renameTable
   */
  public function testRenameTableForEmbeddedTableDoesNotExist() {
    $schema = Database::getConnection()->schema();

    $this->assertFalse($schema->tableExists($this->test_table1['name']), 'The table does not exist in the MongoDB database.');

    // Create the to be renamed table.
    $schema->createTable($this->test_table1['name'], $this->test_table1['schema']);

    $this->assertTrue($schema->tableExists($this->test_table1['name']), 'The table exists in the MongoDB database.');

    // If we try to rename an embedded table on a non existent embedded table an exception should be thrown.
    $this->setExpectedException('Drupal\Core\Database\SchemaObjectDoesNotExistException');
    $schema->renameTable($this->test_table2['name'], $this->test_table2['name']);
  }

  /**
   * @covers ::renameTable
   */
  public function testRenameTableForNewTableExistsAsEmbeddedTable() {
    $schema = Database::getConnection()->schema();

    $this->assertFalse($schema->tableExists($this->test_table1['name']), 'The table does not exist in the MongoDB database.');
    $this->assertFalse($schema->tableExists($this->test_table2['name']), 'The embedded table does not exist in the MongoDB database.');
    $this->assertFalse($schema->tableExists($this->test_table3['name']), 'The embedded table does not exist in the MongoDB database.');

    // Create the to be renamed tables.
    $schema->createTable($this->test_table1['name'], $this->test_table1['schema']);
    $schema->createEmbeddedTable($this->test_table1['name'], $this->test_table2['name'], $this->test_table2['schema']);
    $schema->createEmbeddedTable($this->test_table1['name'], $this->test_table3['name'], $this->test_table3['schema']);

    $this->assertTrue($schema->tableExists($this->test_table1['name']), 'The table exists in the MongoDB database.');
    $this->assertTrue($schema->tableExists($this->test_table2['name']), 'The embedded table exists in the MongoDB database.');
    $this->assertTrue($schema->tableExists($this->test_table3['name']), 'The embedded table exists in the MongoDB database.');

    // If we try to rename an embedded table to an existent embedded table an exception should be thrown.
    $this->setExpectedException('Drupal\Core\Database\SchemaObjectExistsException');
    $schema->renameTable($this->test_table2['name'], $this->test_table3['name']);
  }

  /**
   * @covers ::renameTable
   */
  public function testRenameTableForNewEmbeddedTableExistAsBaseTable() {
    $schema = Database::getConnection()->schema();

    $this->assertFalse($schema->tableExists($this->test_table1['name']), 'The table does not exist in the MongoDB database.');
    $this->assertFalse($schema->tableExists($this->test_table3['name']), 'The table does not exist in the MongoDB database.');
    $this->assertFalse($schema->tableExists($this->test_table2['name']), 'The embedded table does not exist in the MongoDB database.');

    // Create the to be renamed tables.
    $schema->createTable($this->test_table1['name'], $this->test_table1['schema']);
    $schema->createTable($this->test_table3['name'], $this->test_table3['schema']);
    $schema->createEmbeddedTable($this->test_table1['name'], $this->test_table2['name'], $this->test_table2['schema']);


    $this->assertTrue($schema->tableExists($this->test_table1['name']), 'The table exists in the MongoDB database.');
    $this->assertTrue($schema->tableExists($this->test_table3['name']), 'The table exists in the MongoDB database.');
    $this->assertTrue($schema->tableExists($this->test_table2['name']), 'The embedded table exists in the MongoDB database.');

    // If we try to rename an embedded table to an existent base table an exception should be thrown.
    $this->setExpectedException('Drupal\Core\Database\SchemaObjectExistsException');
    $schema->renameTable($this->test_table2['name'], $this->test_table3['name']);
  }

  /**
   * @covers ::renameTable
   * @covers ::tableExists
   */
  public function testRenameTableForEmbeddedTableExistsOnOtherBaseTable() {
    $schema = Database::getConnection()->schema();
    $test_table4 = $this->test_table3;
    $test_table4['name'] = 'test_table4';

    $this->assertFalse($schema->tableExists($this->test_table1['name']), 'The table does not exist in the MongoDB database.');
    $this->assertFalse($schema->tableExists($this->test_table2['name']), 'The table does not exist in the MongoDB database.');
    $this->assertFalse($schema->tableExists($this->test_table3['name']), 'The embedded table does not exist in the MongoDB database.');
    $this->assertFalse($schema->tableExists($test_table4['name']), 'The embedded table does not exist in the MongoDB database.');

    // Create the to be renamed tables.
    $schema->createTable($this->test_table1['name'], $this->test_table1['schema']);
    $schema->createTable($this->test_table2['name'], $this->test_table2['schema']);
    $schema->createEmbeddedTable($this->test_table1['name'], $this->test_table3['name'], $this->test_table3['schema']);
    $schema->createEmbeddedTable($this->test_table2['name'], $test_table4['name'], $test_table4['schema']);

    $this->assertTrue($schema->tableExists($this->test_table1['name']), 'The table exists in the MongoDB database.');
    $this->assertTrue($schema->tableExists($this->test_table2['name']), 'The table exists in the MongoDB database.');
    $this->assertTrue($schema->tableExists($this->test_table3['name']), 'The embedded table exists in the MongoDB database.');
    $this->assertTrue($schema->tableExists($test_table4['name']), 'The embedded table exists in the MongoDB database.');

    // If we try to rename an embedded table on a base table that does not
    // exists, an exception should be thrown.
    $this->setExpectedException('Drupal\Core\Database\SchemaObjectExistsException');
    $schema->renameTable($this->test_table3['name'], $test_table4['name']);
  }

  /**
   * Data provider for testDropTableWithEmbeddedTables().
   *
   * @return array[]
   *   Returns data-set elements with:
   *     - the table data to use as base table (the name, the schema and the
   *       validation).
   *     - the array of table data to use as embedded tables (the name, the
   *       schema and the validation) keyed by their parent table name before we
   *       call the drop table method
   *     - the array of table data to use as embedded tables (the name, the
   *       schema and the validation) keyed by their parent table name after we
   *       have called the drop table method
   *     - the table name to drop.
   *     - the expected validation for the base table before we call the rename
   *       table method.
   *     - the expected validation for the base table after we have called the
   *       rename table method.
   *     - the expected indexes for the base table before we call the rename
   *       table method.
   *     - the expected indexes for the base table after we have called the
   *       rename table method.
   *     - An array with the droped table names.
   */
  public function providerDropTableWithEmbeddedTables() {
    $droped_embedded_validation_base_1_embedded_2 = $droped_embedded_validation_base_1_embedded_3 = ['$and' => [
      ['id' => ['$type' => 'int']],
      ['id' => ['$exists' => TRUE]],
      ['test_field' => ['$type' => 'int']],
      ['test_field' => ['$exists' => TRUE]],
      ['test_field_string' => ['$type' => 'string']],
      ['test_field_string' => ['$exists' => TRUE]],
      ['$or' => [
        ['test_field_string_ascii' => ['$type' => 'string']],
        ['test_field_string_ascii' => ['$exists' => FALSE]]
      ]],
    ]];

    $droped_embedded_validation_base_1_embedded_2_and_3 = ['$and' => [
      ['id' => ['$type' => 'int']],
      ['id' => ['$exists' => TRUE]],
      ['test_field' => ['$type' => 'int']],
      ['test_field' => ['$exists' => TRUE]],
      ['test_field_string' => ['$type' => 'string']],
      ['test_field_string' => ['$exists' => TRUE]],
      ['$or' => [
        ['test_field_string_ascii' => ['$type' => 'string']],
        ['test_field_string_ascii' => ['$exists' => FALSE]]
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table3.id' => ['$exists' => FALSE]],
          ['test_table3.test_field_varchar_null' => ['$exists' => FALSE]],
          ['test_table3.test_field_varchar_not_null' => ['$exists' => FALSE]],
          ['test_table3.test_field_varchar_default' => ['$exists' => FALSE]],
          ['test_table3.test_field_text_null' => ['$exists' => FALSE]],
          ['test_table3.test_field_text_not_null' => ['$exists' => FALSE]],
          ['test_table3.test_field_text_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table3.id' => ['$type' => 'int']],
          ['test_table3.id' => ['$exists' => TRUE]],
          ['test_table3.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table3.test_field_varchar_null' => ['$type' => 'string']],
            ['test_table3.test_field_varchar_null' => ['$exists' => FALSE]]
          ]],
          ['test_table3.test_field_varchar_not_null' => ['$type' => 'string']],
          ['test_table3.test_field_varchar_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table3.test_field_varchar_default' => ['$type' => 'string']],
            ['test_table3.test_field_varchar_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table3.test_field_text_null' => ['$type' => 'string']],
            ['test_table3.test_field_text_null' => ['$exists' => FALSE]]
          ]],
          ['test_table3.test_field_text_not_null' => ['$type' => 'string']],
          ['test_table3.test_field_text_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table3.test_field_text_default' => ['$type' => 'string']],
            ['test_table3.test_field_text_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
    ]];

    $droped_validation_base_1_embedded_2_and_3_on_2_before = ['$and' => [
      ['id' => ['$type' => 'int']],
      ['id' => ['$exists' => TRUE]],
      ['test_field' => ['$type' => 'int']],
      ['test_field' => ['$exists' => TRUE]],
      ['test_field_string' => ['$type' => 'string']],
      ['test_field_string' => ['$exists' => TRUE]],
      ['$or' => [
        ['test_field_string_ascii' => ['$type' => 'string']],
        ['test_field_string_ascii' => ['$exists' => FALSE]]
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.id' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_default' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_default' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.id' => ['$type' => 'int']],
          ['test_table2.id' => ['$exists' => TRUE]],
          ['test_table2.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table2.test_field_int_null' => ['$type' => 'int']],
            ['test_table2.test_field_int_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_int_not_null' => ['$type' => 'int']],
          ['test_table2.test_field_int_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_int_default' => ['$type' => 'int']],
            ['test_table2.test_field_int_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_field_float_null' => ['$type' => 'double']],
            ['test_table2.test_field_float_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_float_not_null' => ['$type' => 'double']],
          ['test_table2.test_field_float_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_float_default' => ['$type' => 'double']],
            ['test_table2.test_field_float_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_field_numeric_null' => ['$type' => 'decimal']],
            ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_numeric_not_null' => ['$type' => 'decimal']],
          ['test_table2.test_field_numeric_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_numeric_default' => ['$type' => 'decimal']],
            ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table3.id' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_varchar_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_varchar_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_varchar_default' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_text_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_text_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_text_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table3.id' => ['$type' => 'int']],
          ['test_table2.test_table3.id' => ['$exists' => TRUE]],
          ['test_table2.test_table3.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table2.test_table3.test_field_varchar_null' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_varchar_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_table3.test_field_varchar_not_null' => ['$type' => 'string']],
          ['test_table2.test_table3.test_field_varchar_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_table3.test_field_varchar_default' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_varchar_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_table3.test_field_text_null' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_text_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_table3.test_field_text_not_null' => ['$type' => 'string']],
          ['test_table2.test_table3.test_field_text_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_table3.test_field_text_default' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_text_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
    ]];

    $droped_validation_base_1_embedded_2_and_3_on_2_after_droped_3 = ['$and' => [
      ['id' => ['$type' => 'int']],
      ['id' => ['$exists' => TRUE]],
      ['test_field' => ['$type' => 'int']],
      ['test_field' => ['$exists' => TRUE]],
      ['test_field_string' => ['$type' => 'string']],
      ['test_field_string' => ['$exists' => TRUE]],
      ['$or' => [
        ['test_field_string_ascii' => ['$type' => 'string']],
        ['test_field_string_ascii' => ['$exists' => FALSE]]
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.id' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_default' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_default' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.id' => ['$type' => 'int']],
          ['test_table2.id' => ['$exists' => TRUE]],
          ['test_table2.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table2.test_field_int_null' => ['$type' => 'int']],
            ['test_table2.test_field_int_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_int_not_null' => ['$type' => 'int']],
          ['test_table2.test_field_int_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_int_default' => ['$type' => 'int']],
            ['test_table2.test_field_int_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_field_float_null' => ['$type' => 'double']],
            ['test_table2.test_field_float_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_float_not_null' => ['$type' => 'double']],
          ['test_table2.test_field_float_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_float_default' => ['$type' => 'double']],
            ['test_table2.test_field_float_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_field_numeric_null' => ['$type' => 'decimal']],
            ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_numeric_not_null' => ['$type' => 'decimal']],
          ['test_table2.test_field_numeric_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_numeric_default' => ['$type' => 'decimal']],
            ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
    ]];

    $droped_validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_before = ['$and' => [
      ['id' => ['$type' => 'int']],
      ['id' => ['$exists' => TRUE]],
      ['test_field' => ['$type' => 'int']],
      ['test_field' => ['$exists' => TRUE]],
      ['test_field_string' => ['$type' => 'string']],
      ['test_field_string' => ['$exists' => TRUE]],
      ['$or' => [
        ['test_field_string_ascii' => ['$type' => 'string']],
        ['test_field_string_ascii' => ['$exists' => FALSE]]
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.id' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_default' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_default' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.id' => ['$type' => 'int']],
          ['test_table2.id' => ['$exists' => TRUE]],
          ['test_table2.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table2.test_field_int_null' => ['$type' => 'int']],
            ['test_table2.test_field_int_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_int_not_null' => ['$type' => 'int']],
          ['test_table2.test_field_int_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_int_default' => ['$type' => 'int']],
            ['test_table2.test_field_int_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_field_float_null' => ['$type' => 'double']],
            ['test_table2.test_field_float_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_float_not_null' => ['$type' => 'double']],
          ['test_table2.test_field_float_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_float_default' => ['$type' => 'double']],
            ['test_table2.test_field_float_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_field_numeric_null' => ['$type' => 'decimal']],
            ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_numeric_not_null' => ['$type' => 'decimal']],
          ['test_table2.test_field_numeric_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_numeric_default' => ['$type' => 'decimal']],
            ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table3.id' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_varchar_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_varchar_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_varchar_default' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_text_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_text_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_text_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table3.id' => ['$type' => 'int']],
          ['test_table2.test_table3.id' => ['$exists' => TRUE]],
          ['test_table2.test_table3.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table2.test_table3.test_field_varchar_null' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_varchar_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_table3.test_field_varchar_not_null' => ['$type' => 'string']],
          ['test_table2.test_table3.test_field_varchar_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_table3.test_field_varchar_default' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_varchar_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_table3.test_field_text_null' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_text_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_table3.test_field_text_not_null' => ['$type' => 'string']],
          ['test_table2.test_table3.test_field_text_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_table3.test_field_text_default' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_text_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table3.test_table5.id' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_table5.test_field_string' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table3.test_table5.id' => ['$type' => 'int']],
          ['test_table2.test_table3.test_table5.id' => ['$exists' => TRUE]],
          ['test_table2.test_table3.test_table5.id' => ['$gte' => 0]],
          ['test_table2.test_table3.test_table5.test_field_string' => ['$type' => 'string']],
          ['test_table2.test_table3.test_table5.test_field_string' => ['$exists' => TRUE]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table3.test_table6.id' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_table6.test_field_string' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table3.test_table6.id' => ['$type' => 'int']],
          ['test_table2.test_table3.test_table6.id' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_table3.test_table6.test_field_string' => ['$type' => 'string']],
            ['test_table2.test_table3.test_table6.test_field_string' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table4.id' => ['$exists' => FALSE]],
          ['test_table2.test_table4.test_field' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table4.id' => ['$type' => 'int']],
          ['test_table2.test_table4.id' => ['$exists' => TRUE]],
          ['test_table2.test_table4.test_field' => ['$type' => 'int']],
          ['test_table2.test_table4.test_field' => ['$exists' => TRUE]],
        ]],
      ]],
    ]];

    $droped_validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_droped_2 = ['$and' => [
      ['id' => ['$type' => 'int']],
      ['id' => ['$exists' => TRUE]],
      ['test_field' => ['$type' => 'int']],
      ['test_field' => ['$exists' => TRUE]],
      ['test_field_string' => ['$type' => 'string']],
      ['test_field_string' => ['$exists' => TRUE]],
      ['$or' => [
        ['test_field_string_ascii' => ['$type' => 'string']],
        ['test_field_string_ascii' => ['$exists' => FALSE]]
      ]],
    ]];

    $droped_validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_droped_3 = ['$and' => [
      ['id' => ['$type' => 'int']],
      ['id' => ['$exists' => TRUE]],
      ['test_field' => ['$type' => 'int']],
      ['test_field' => ['$exists' => TRUE]],
      ['test_field_string' => ['$type' => 'string']],
      ['test_field_string' => ['$exists' => TRUE]],
      ['$or' => [
        ['test_field_string_ascii' => ['$type' => 'string']],
        ['test_field_string_ascii' => ['$exists' => FALSE]]
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.id' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_default' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_default' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.id' => ['$type' => 'int']],
          ['test_table2.id' => ['$exists' => TRUE]],
          ['test_table2.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table2.test_field_int_null' => ['$type' => 'int']],
            ['test_table2.test_field_int_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_int_not_null' => ['$type' => 'int']],
          ['test_table2.test_field_int_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_int_default' => ['$type' => 'int']],
            ['test_table2.test_field_int_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_field_float_null' => ['$type' => 'double']],
            ['test_table2.test_field_float_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_float_not_null' => ['$type' => 'double']],
          ['test_table2.test_field_float_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_float_default' => ['$type' => 'double']],
            ['test_table2.test_field_float_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_field_numeric_null' => ['$type' => 'decimal']],
            ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_numeric_not_null' => ['$type' => 'decimal']],
          ['test_table2.test_field_numeric_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_numeric_default' => ['$type' => 'decimal']],
            ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table4.id' => ['$exists' => FALSE]],
          ['test_table2.test_table4.test_field' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table4.id' => ['$type' => 'int']],
          ['test_table2.test_table4.id' => ['$exists' => TRUE]],
          ['test_table2.test_table4.test_field' => ['$type' => 'int']],
          ['test_table2.test_table4.test_field' => ['$exists' => TRUE]],
        ]],
      ]],
    ]];

    $droped_validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_droped_4 = ['$and' => [
      ['id' => ['$type' => 'int']],
      ['id' => ['$exists' => TRUE]],
      ['test_field' => ['$type' => 'int']],
      ['test_field' => ['$exists' => TRUE]],
      ['test_field_string' => ['$type' => 'string']],
      ['test_field_string' => ['$exists' => TRUE]],
      ['$or' => [
        ['test_field_string_ascii' => ['$type' => 'string']],
        ['test_field_string_ascii' => ['$exists' => FALSE]]
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.id' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_default' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_default' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.id' => ['$type' => 'int']],
          ['test_table2.id' => ['$exists' => TRUE]],
          ['test_table2.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table2.test_field_int_null' => ['$type' => 'int']],
            ['test_table2.test_field_int_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_int_not_null' => ['$type' => 'int']],
          ['test_table2.test_field_int_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_int_default' => ['$type' => 'int']],
            ['test_table2.test_field_int_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_field_float_null' => ['$type' => 'double']],
            ['test_table2.test_field_float_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_float_not_null' => ['$type' => 'double']],
          ['test_table2.test_field_float_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_float_default' => ['$type' => 'double']],
            ['test_table2.test_field_float_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_field_numeric_null' => ['$type' => 'decimal']],
            ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_numeric_not_null' => ['$type' => 'decimal']],
          ['test_table2.test_field_numeric_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_numeric_default' => ['$type' => 'decimal']],
            ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table3.id' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_varchar_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_varchar_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_varchar_default' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_text_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_text_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_text_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table3.id' => ['$type' => 'int']],
          ['test_table2.test_table3.id' => ['$exists' => TRUE]],
          ['test_table2.test_table3.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table2.test_table3.test_field_varchar_null' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_varchar_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_table3.test_field_varchar_not_null' => ['$type' => 'string']],
          ['test_table2.test_table3.test_field_varchar_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_table3.test_field_varchar_default' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_varchar_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_table3.test_field_text_null' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_text_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_table3.test_field_text_not_null' => ['$type' => 'string']],
          ['test_table2.test_table3.test_field_text_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_table3.test_field_text_default' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_text_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table3.test_table5.id' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_table5.test_field_string' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table3.test_table5.id' => ['$type' => 'int']],
          ['test_table2.test_table3.test_table5.id' => ['$exists' => TRUE]],
          ['test_table2.test_table3.test_table5.id' => ['$gte' => 0]],
          ['test_table2.test_table3.test_table5.test_field_string' => ['$type' => 'string']],
          ['test_table2.test_table3.test_table5.test_field_string' => ['$exists' => TRUE]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table3.test_table6.id' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_table6.test_field_string' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table3.test_table6.id' => ['$type' => 'int']],
          ['test_table2.test_table3.test_table6.id' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_table3.test_table6.test_field_string' => ['$type' => 'string']],
            ['test_table2.test_table3.test_table6.test_field_string' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
    ]];

    $droped_validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_droped_6 = ['$and' => [
      ['id' => ['$type' => 'int']],
      ['id' => ['$exists' => TRUE]],
      ['test_field' => ['$type' => 'int']],
      ['test_field' => ['$exists' => TRUE]],
      ['test_field_string' => ['$type' => 'string']],
      ['test_field_string' => ['$exists' => TRUE]],
      ['$or' => [
        ['test_field_string_ascii' => ['$type' => 'string']],
        ['test_field_string_ascii' => ['$exists' => FALSE]]
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.id' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_int_default' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_float_default' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.id' => ['$type' => 'int']],
          ['test_table2.id' => ['$exists' => TRUE]],
          ['test_table2.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table2.test_field_int_null' => ['$type' => 'int']],
            ['test_table2.test_field_int_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_int_not_null' => ['$type' => 'int']],
          ['test_table2.test_field_int_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_int_default' => ['$type' => 'int']],
            ['test_table2.test_field_int_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_field_float_null' => ['$type' => 'double']],
            ['test_table2.test_field_float_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_float_not_null' => ['$type' => 'double']],
          ['test_table2.test_field_float_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_float_default' => ['$type' => 'double']],
            ['test_table2.test_field_float_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_field_numeric_null' => ['$type' => 'decimal']],
            ['test_table2.test_field_numeric_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_field_numeric_not_null' => ['$type' => 'decimal']],
          ['test_table2.test_field_numeric_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_field_numeric_default' => ['$type' => 'decimal']],
            ['test_table2.test_field_numeric_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table3.id' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_varchar_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_varchar_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_varchar_default' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_text_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_text_not_null' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_field_text_default' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table3.id' => ['$type' => 'int']],
          ['test_table2.test_table3.id' => ['$exists' => TRUE]],
          ['test_table2.test_table3.id' => ['$gte' => 0]],
          ['$or' => [
            ['test_table2.test_table3.test_field_varchar_null' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_varchar_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_table3.test_field_varchar_not_null' => ['$type' => 'string']],
          ['test_table2.test_table3.test_field_varchar_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_table3.test_field_varchar_default' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_varchar_default' => ['$exists' => FALSE]]
          ]],
          ['$or' => [
            ['test_table2.test_table3.test_field_text_null' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_text_null' => ['$exists' => FALSE]]
          ]],
          ['test_table2.test_table3.test_field_text_not_null' => ['$type' => 'string']],
          ['test_table2.test_table3.test_field_text_not_null' => ['$exists' => TRUE]],
          ['$or' => [
            ['test_table2.test_table3.test_field_text_default' => ['$type' => 'string']],
            ['test_table2.test_table3.test_field_text_default' => ['$exists' => FALSE]]
          ]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table3.test_table5.id' => ['$exists' => FALSE]],
          ['test_table2.test_table3.test_table5.test_field_string' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table3.test_table5.id' => ['$type' => 'int']],
          ['test_table2.test_table3.test_table5.id' => ['$exists' => TRUE]],
          ['test_table2.test_table3.test_table5.id' => ['$gte' => 0]],
          ['test_table2.test_table3.test_table5.test_field_string' => ['$type' => 'string']],
          ['test_table2.test_table3.test_table5.test_field_string' => ['$exists' => TRUE]],
        ]],
      ]],
      ['$or' => [
        ['$and' => [
          ['test_table2.test_table4.id' => ['$exists' => FALSE]],
          ['test_table2.test_table4.test_field' => ['$exists' => FALSE]],
        ]],
        ['$and' => [
          ['test_table2.test_table4.id' => ['$type' => 'int']],
          ['test_table2.test_table4.id' => ['$exists' => TRUE]],
          ['test_table2.test_table4.test_field' => ['$type' => 'int']],
          ['test_table2.test_table4.test_field' => ['$exists' => TRUE]],
        ]],
      ]],
    ]];

    $droped_embedded_indexes_base_1_embedded_2 = $droped_embedded_indexes_base_1_embedded_3 = $droped_embedded_indexes_base_1_embedded_2_and_3 = [
      ['name' => '_id_', 'unique' => FALSE, 'key' => ['_id' => 1]],
      ['name' => '__pkey', 'unique' => TRUE, 'key' => ['id' => 1]],
      ['name' => 'test_field__key', 'unique' => TRUE, 'key' => ['test_field' => 1]],
    ];

    $droped_table_indexes_base_1_embedded_2_and_3_on_2_before = $droped_table_indexes_base_1_embedded_2_and_3_on_2_after_droped_3 = [
      ['name' => '_id_', 'unique' => FALSE, 'key' => ['_id' => 1]],
      ['name' => '__pkey', 'unique' => TRUE, 'key' => ['id' => 1]],
      ['name' => 'test_field__key', 'unique' => TRUE, 'key' => ['test_field' => 1]],
      ['name' => 'test_table2.__pkey', 'unique' => TRUE, 'key' => ['test_table2.id' => 1]],
      ['name' => 'test_table2.test_fields_not_null__key', 'unique' => TRUE, 'key' => ['test_table2.test_field_int_not_null' => 1, 'test_table2.test_field_float_not_null' => 1, 'test_table2.test_field_numeric_not_null' => 1]],
      ['name' => 'test_table2.test_fields_null__key', 'unique' => TRUE, 'key' => ['test_table2.test_field_int_null' => 1, 'test_table2.test_field_float_null' => 1, 'test_table2.test_field_numeric_null' => 1]],
      ['name' => 'test_table2.test_fields_default__idx', 'unique' => FALSE, 'key' => ['test_table2.test_field_int_default' => 1, 'test_table2.test_field_float_default' => 1, 'test_table2.test_field_numeric_default' => 1]],
      ['name' => 'test_table2.test_field_int_null__idx', 'unique' => FALSE, 'key' => ['test_table2.test_field_int_null' => 1]],
    ];

    $droped_table_indexes_base_1_embedded_2_and_3_on_2_after_droped_2 = $droped_table_indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_droped_2 = [
      ['name' => '_id_', 'unique' => FALSE, 'key' => ['_id' => 1]],
      ['name' => '__pkey', 'unique' => TRUE, 'key' => ['id' => 1]],
      ['name' => 'test_field__key', 'unique' => TRUE, 'key' => ['test_field' => 1]],
    ];

    $droped_table_indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_before = [
      ['name' => '_id_', 'unique' => FALSE, 'key' => ['_id' => 1]],
      ['name' => '__pkey', 'unique' => TRUE, 'key' => ['id' => 1]],
      ['name' => 'test_field__key', 'unique' => TRUE, 'key' => ['test_field' => 1]],
      ['name' => 'test_table2.__pkey', 'unique' => TRUE, 'key' => ['test_table2.id' => 1]],
      ['name' => 'test_table2.test_fields_not_null__key', 'unique' => TRUE, 'key' => ['test_table2.test_field_int_not_null' => 1, 'test_table2.test_field_float_not_null' => 1, 'test_table2.test_field_numeric_not_null' => 1]],
      ['name' => 'test_table2.test_fields_null__key', 'unique' => TRUE, 'key' => ['test_table2.test_field_int_null' => 1, 'test_table2.test_field_float_null' => 1, 'test_table2.test_field_numeric_null' => 1]],
      ['name' => 'test_table2.test_fields_default__idx', 'unique' => FALSE, 'key' => ['test_table2.test_field_int_default' => 1, 'test_table2.test_field_float_default' => 1, 'test_table2.test_field_numeric_default' => 1]],
      ['name' => 'test_table2.test_field_int_null__idx', 'unique' => FALSE, 'key' => ['test_table2.test_field_int_null' => 1]],
      ['name' => 'test_table2.test_table4.__pkey', 'unique' => TRUE, 'key' => ['test_table2.test_table4.id' => 1]],
      ['name' => 'test_table2.test_table4.test_field__key', 'unique' => TRUE, 'key' => ['test_table2.test_table4.test_field' => 1]],
      ['name' => 'test_table2.test_table3.test_table6.__pkey', 'unique' => TRUE, 'key' => ['test_table2.test_table3.test_table6.id' => 1]],
      ['name' => 'test_table2.test_table3.test_table6.test_field_string__idx', 'unique' => FALSE, 'key' => ['test_table2.test_table3.test_table6.test_field_string' => 1]],
    ];

    $droped_table_indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_droped_3 = [
      ['name' => '_id_', 'unique' => FALSE, 'key' => ['_id' => 1]],
      ['name' => '__pkey', 'unique' => TRUE, 'key' => ['id' => 1]],
      ['name' => 'test_field__key', 'unique' => TRUE, 'key' => ['test_field' => 1]],
      ['name' => 'test_table2.__pkey', 'unique' => TRUE, 'key' => ['test_table2.id' => 1]],
      ['name' => 'test_table2.test_fields_not_null__key', 'unique' => TRUE, 'key' => ['test_table2.test_field_int_not_null' => 1, 'test_table2.test_field_float_not_null' => 1, 'test_table2.test_field_numeric_not_null' => 1]],
      ['name' => 'test_table2.test_fields_null__key', 'unique' => TRUE, 'key' => ['test_table2.test_field_int_null' => 1, 'test_table2.test_field_float_null' => 1, 'test_table2.test_field_numeric_null' => 1]],
      ['name' => 'test_table2.test_fields_default__idx', 'unique' => FALSE, 'key' => ['test_table2.test_field_int_default' => 1, 'test_table2.test_field_float_default' => 1, 'test_table2.test_field_numeric_default' => 1]],
      ['name' => 'test_table2.test_field_int_null__idx', 'unique' => FALSE, 'key' => ['test_table2.test_field_int_null' => 1]],
      ['name' => 'test_table2.test_table4.__pkey', 'unique' => TRUE, 'key' => ['test_table2.test_table4.id' => 1]],
      ['name' => 'test_table2.test_table4.test_field__key', 'unique' => TRUE, 'key' => ['test_table2.test_table4.test_field' => 1]],
    ];

    $droped_table_indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_droped_4 = [
      ['name' => '_id_', 'unique' => FALSE, 'key' => ['_id' => 1]],
      ['name' => '__pkey', 'unique' => TRUE, 'key' => ['id' => 1]],
      ['name' => 'test_field__key', 'unique' => TRUE, 'key' => ['test_field' => 1]],
      ['name' => 'test_table2.__pkey', 'unique' => TRUE, 'key' => ['test_table2.id' => 1]],
      ['name' => 'test_table2.test_fields_not_null__key', 'unique' => TRUE, 'key' => ['test_table2.test_field_int_not_null' => 1, 'test_table2.test_field_float_not_null' => 1, 'test_table2.test_field_numeric_not_null' => 1]],
      ['name' => 'test_table2.test_fields_null__key', 'unique' => TRUE, 'key' => ['test_table2.test_field_int_null' => 1, 'test_table2.test_field_float_null' => 1, 'test_table2.test_field_numeric_null' => 1]],
      ['name' => 'test_table2.test_fields_default__idx', 'unique' => FALSE, 'key' => ['test_table2.test_field_int_default' => 1, 'test_table2.test_field_float_default' => 1, 'test_table2.test_field_numeric_default' => 1]],
      ['name' => 'test_table2.test_field_int_null__idx', 'unique' => FALSE, 'key' => ['test_table2.test_field_int_null' => 1]],
      ['name' => 'test_table2.test_table3.test_table6.__pkey', 'unique' => TRUE, 'key' => ['test_table2.test_table3.test_table6.id' => 1]],
      ['name' => 'test_table2.test_table3.test_table6.test_field_string__idx', 'unique' => FALSE, 'key' => ['test_table2.test_table3.test_table6.test_field_string' => 1]],
    ];

    $droped_table_indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_droped_6 = [
      ['name' => '_id_', 'unique' => FALSE, 'key' => ['_id' => 1]],
      ['name' => '__pkey', 'unique' => TRUE, 'key' => ['id' => 1]],
      ['name' => 'test_field__key', 'unique' => TRUE, 'key' => ['test_field' => 1]],
      ['name' => 'test_table2.__pkey', 'unique' => TRUE, 'key' => ['test_table2.id' => 1]],
      ['name' => 'test_table2.test_fields_not_null__key', 'unique' => TRUE, 'key' => ['test_table2.test_field_int_not_null' => 1, 'test_table2.test_field_float_not_null' => 1, 'test_table2.test_field_numeric_not_null' => 1]],
      ['name' => 'test_table2.test_fields_null__key', 'unique' => TRUE, 'key' => ['test_table2.test_field_int_null' => 1, 'test_table2.test_field_float_null' => 1, 'test_table2.test_field_numeric_null' => 1]],
      ['name' => 'test_table2.test_fields_default__idx', 'unique' => FALSE, 'key' => ['test_table2.test_field_int_default' => 1, 'test_table2.test_field_float_default' => 1, 'test_table2.test_field_numeric_default' => 1]],
      ['name' => 'test_table2.test_field_int_null__idx', 'unique' => FALSE, 'key' => ['test_table2.test_field_int_null' => 1]],
      ['name' => 'test_table2.test_table4.__pkey', 'unique' => TRUE, 'key' => ['test_table2.test_table4.id' => 1]],
      ['name' => 'test_table2.test_table4.test_field__key', 'unique' => TRUE, 'key' => ['test_table2.test_table4.test_field' => 1]],
    ];

    return [
      [
        $this->test_table1,
        [],
        [],
        $this->test_table1['name'],
        $this->test_table1['validation'],
        [],
        $this->test_table1['indexes'],
        [],
        [$this->test_table1['name']],
      ],
      [
        $this->test_table2,
        [],
        [],
        $this->test_table2['name'],
        $this->test_table2['validation'],
        [],
        $this->test_table2['indexes'],
        [],
        [$this->test_table2['name']],
      ],
      [
        $this->test_table3,
        [],
        [],
        $this->test_table3['name'],
        $this->test_table3['validation'],
        [],
        $this->test_table3['indexes'],
        [],
        [$this->test_table3['name']],
      ],
      [
        $this->test_table4,
        [],
        [],
        $this->test_table4['name'],
        $this->test_table4['validation'],
        [],
        $this->test_table4['indexes'],
        [],
        [$this->test_table4['name']],
      ],
      [
        $this->test_table5,
        [],
        [],
        $this->test_table5['name'],
        $this->test_table5['validation'],
        [],
        $this->test_table5['indexes'],
        [],
        [$this->test_table5['name']],
      ],
      [
        $this->test_table6,
        [],
        [],
        $this->test_table6['name'],
        $this->test_table6['validation'],
        [],
        $this->test_table6['indexes'],
        [],
        [$this->test_table6['name']],
      ],
      [
        $this->test_table1,
        [$this->test_table1['name'] => [$this->test_table2]],
        [],
        $this->test_table2['name'],
        $this->embedded_validation_base_1_embedded_2,
        $droped_embedded_validation_base_1_embedded_2,
        $this->embedded_indexes_base_1_embedded_2,
        $droped_embedded_indexes_base_1_embedded_2,
        [$this->test_table2['name']],
      ],
      [
        $this->test_table1,
        [$this->test_table1['name'] => [$this->test_table3]],
        [],
        $this->test_table3['name'],
        $this->embedded_validation_base_1_embedded_3,
        $droped_embedded_validation_base_1_embedded_3,
        $this->embedded_indexes_base_1_embedded_3,
        $droped_embedded_indexes_base_1_embedded_3,
        [$this->test_table3['name']],
      ],
      [
        $this->test_table1,
        [$this->test_table1['name'] => [$this->test_table2, $this->test_table3]],
        [$this->test_table1['name'] => [$this->test_table3]],
        $this->test_table2['name'],
        $this->embedded_validation_base_1_embedded_2_and_3,
        $droped_embedded_validation_base_1_embedded_2_and_3,
        $this->embedded_indexes_base_1_embedded_2_and_3,
        $droped_embedded_indexes_base_1_embedded_2_and_3,
        [$this->test_table2['name']],
      ],
      [
        $this->test_table1,
        [$this->test_table1['name'] => [$this->test_table2], $this->test_table2['name'] => [$this->test_table3]],
        [],
        $this->test_table1['name'],
        $droped_validation_base_1_embedded_2_and_3_on_2_before,
        NULL,
        $droped_table_indexes_base_1_embedded_2_and_3_on_2_before,
        NULL,
        [$this->test_table1['name'], $this->test_table2['name'], $this->test_table3['name']],
      ],
      [
        $this->test_table1,
        [$this->test_table1['name'] => [$this->test_table2], $this->test_table2['name'] => [$this->test_table3]],
        [],
        $this->test_table2['name'],
        $droped_validation_base_1_embedded_2_and_3_on_2_before,
        $this->test_table1['validation'],
        $droped_table_indexes_base_1_embedded_2_and_3_on_2_before,
        $droped_table_indexes_base_1_embedded_2_and_3_on_2_after_droped_2,
        [$this->test_table2['name'], $this->test_table3['name']],
      ],
      [
        $this->test_table1,
        [$this->test_table1['name'] => [$this->test_table2], $this->test_table2['name'] => [$this->test_table3]],
        [$this->test_table1['name'] => [$this->test_table2]],
        $this->test_table3['name'],
        $droped_validation_base_1_embedded_2_and_3_on_2_before,
        $droped_validation_base_1_embedded_2_and_3_on_2_after_droped_3,
        $droped_table_indexes_base_1_embedded_2_and_3_on_2_before,
        $droped_table_indexes_base_1_embedded_2_and_3_on_2_after_droped_3,
        [$this->test_table3['name']],
      ],
      [
        $this->test_table1,
        [$this->test_table1['name'] => [$this->test_table2], $this->test_table2['name'] => [$this->test_table3, $this->test_table4], $this->test_table3['name'] => [$this->test_table5, $this->test_table6]],
        [],
        $this->test_table1['name'],
        $droped_validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_before,
        NULL,
        $droped_table_indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_before,
        NULL,
        [$this->test_table1['name'], $this->test_table2['name'], $this->test_table3['name'], $this->test_table4['name'], $this->test_table5['name'], $this->test_table6['name']],
      ],
      [
        $this->test_table1,
        [$this->test_table1['name'] => [$this->test_table2], $this->test_table2['name'] => [$this->test_table3, $this->test_table4], $this->test_table3['name'] => [$this->test_table5, $this->test_table6]],
        [],
        $this->test_table2['name'],
        $droped_validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_before,
        $droped_validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_droped_2,
        $droped_table_indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_before,
        $droped_table_indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_droped_2,
        [$this->test_table1['name'], $this->test_table2['name'], $this->test_table3['name'], $this->test_table4['name'], $this->test_table5['name'], $this->test_table6['name']],
      ],
      [
        $this->test_table1,
        [$this->test_table1['name'] => [$this->test_table2], $this->test_table2['name'] => [$this->test_table3, $this->test_table4], $this->test_table3['name'] => [$this->test_table5, $this->test_table6]],
        [$this->test_table1['name'] => [$this->test_table2], $this->test_table2['name'] => [$this->test_table4]],
        $this->test_table3['name'],
        $droped_validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_before,
        $droped_validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_droped_3,
        $droped_table_indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_before,
        $droped_table_indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_droped_3,
        [$this->test_table3['name'], $this->test_table5['name'], $this->test_table6['name']],
      ],
      [
        $this->test_table1,
        [$this->test_table1['name'] => [$this->test_table2], $this->test_table2['name'] => [$this->test_table3, $this->test_table4], $this->test_table3['name'] => [$this->test_table5, $this->test_table6]],
        [$this->test_table1['name'] => [$this->test_table2], $this->test_table2['name'] => [$this->test_table3], $this->test_table3['name'] => [$this->test_table5, $this->test_table6]],
        $this->test_table4['name'],
        $droped_validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_before,
        $droped_validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_droped_4,
        $droped_table_indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_before,
        $droped_table_indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_droped_4,
        [$this->test_table4['name']],
      ],
      [
        $this->test_table1,
        [$this->test_table1['name'] => [$this->test_table2], $this->test_table2['name'] => [$this->test_table3, $this->test_table4], $this->test_table3['name'] => [$this->test_table5, $this->test_table6]],
        [$this->test_table1['name'] => [$this->test_table2], $this->test_table2['name'] => [$this->test_table3, $this->test_table4], $this->test_table3['name'] => [$this->test_table5]],
        $this->test_table6['name'],
        $droped_validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_before,
        $droped_validation_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_droped_6,
        $droped_table_indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_before,
        $droped_table_indexes_base_1_embedded_2_and_3_4_on_2_and_5_6_on_3_after_droped_6,
        [$this->test_table6['name']],
      ],
    ];
  }

  /**
   * @covers ::createTable
   * @covers ::createEmbeddedTable
   * @covers ::dropTable
   * @covers ::tableExists
   * @dataProvider providerDropTableWithEmbeddedTables
   */
  public function testDropTableWithEmbeddedTables($base_table_data, $embedded_tables_data, $embedded_tables_data_after, $droped_table_name, $expected_validation_before, $expected_validation_after, $expected_indexes_before, $expected_indexes_after, $deleted_tables) {
    $schema = Database::getConnection()->schema();

    // Create all the tables.
    $schema->createTable($base_table_data['name'], $base_table_data['schema']);
    foreach ($embedded_tables_data as $parent_table_name => $embedded_table_array) {
      foreach ($embedded_table_array as $embedded_table_data) {
        $schema->createEmbeddedTable($parent_table_name, $embedded_table_data['name'], $embedded_table_data['schema']);
      }
    }

    // Test everything before the table has been droped.
    $this->assertTrue($schema->tableExists($base_table_data['name']), 'The table exists in the MongoDB database.');
    foreach ($embedded_tables_data as $parent_table_name => $embedded_table_array) {
      foreach ($embedded_table_array as $embedded_table_data) {
        $this->assertTrue($schema->tableExists($embedded_table_data['name']), 'The embedded table exists in the MongoDB database.');
      }
    }
    $this->checkTableValidation($base_table_data['name'], $expected_validation_before);
    $this->checkTableSchema($base_table_data['name'], $base_table_data['schema']);
    foreach ($embedded_tables_data as $parent_table_name => $embedded_table_array) {
      foreach ($embedded_table_array as $embedded_table_data) {
        $embedded_table_data['schema']['embedded_to_table'] = $parent_table_name;
        $this->checkTableSchema($embedded_table_data['name'], $embedded_table_data['schema']);
      }
    }
    $this->checkTableIndexes($base_table_data['name'], $base_table_data['schema']);
    foreach ($embedded_tables_data as $parent_table_name => $embedded_table_array) {
      foreach ($embedded_table_array as $embedded_table_data) {
        $this->checkEmbeddedTableIndexes($parent_table_name, $embedded_table_data['name'], $embedded_table_data['schema']);
      }
    }
    $this->checkExpectedIndexesAgainstDatabase($base_table_data['name'], $expected_indexes_before);
    $this->checkTableNumberOfIndexes($base_table_data, $embedded_tables_data);

    // Call the to be tested method: Schema::dropTable().
    $schema->dropTable($droped_table_name);

    // Test everything after the table has been droped.
    if ($base_table_data['name'] == $droped_table_name) {
      $this->assertFalse($schema->tableExists($base_table_data['name']), 'The table does not exist in the MongoDB database.');
    }
    else {
      $this->assertTrue($schema->tableExists($base_table_data['name']), 'The table exists in the MongoDB database.');
    }
    foreach ($embedded_tables_data as $parent_table_name => $embedded_table_array) {
      foreach ($embedded_table_array as $embedded_table_data) {
        if (in_array($embedded_table_data['name'], $deleted_tables, TRUE)) {
          $this->assertFalse($schema->tableExists($embedded_table_data['name']), 'The embedded table does not exist in the MongoDB database.');
        }
        else {
          $this->assertTrue($schema->tableExists($embedded_table_data['name']), 'The embedded table exists in the MongoDB database.');
        }
      }
    }

    if ($base_table_data['name'] != $droped_table_name) {
      $this->checkTableValidation($base_table_data['name'], $expected_validation_after);
      $this->checkTableSchema($base_table_data['name'], $base_table_data['schema']);
    }
    foreach ($embedded_tables_data as $parent_table_name => $embedded_table_array) {
      foreach ($embedded_table_array as $embedded_table_data) {
        if (!in_array($embedded_table_data['name'], $deleted_tables, TRUE)) {
          $embedded_table_data['schema']['embedded_to_table'] = $parent_table_name;
          $this->checkTableSchema($embedded_table_data['name'], $embedded_table_data['schema']);
        }
      }
    }
    if ($base_table_data['name'] != $droped_table_name) {
      $this->checkTableIndexes($base_table_data['name'], $base_table_data['schema']);
    }
    foreach ($embedded_tables_data as $parent_table_name => $embedded_table_array) {
      foreach ($embedded_table_array as $embedded_table_data) {
        if (!in_array($embedded_table_data['name'], $deleted_tables, TRUE)) {
          $this->checkEmbeddedTableIndexes($parent_table_name, $embedded_table_data['name'], $embedded_table_data['schema']);
        }
      }
    }
    if ($base_table_data['name'] != $droped_table_name) {
      $this->checkExpectedIndexesAgainstDatabase($base_table_data['name'], $expected_indexes_after);
      $this->checkTableNumberOfIndexes($base_table_data, $embedded_tables_data_after);
    }
  }

  /**
   * @covers ::dropTable
   */
  public function testDropTableForTableDoesNotExist() {
    $schema = Database::getConnection()->schema();

    $this->assertFalse($schema->tableExists($this->test_table1['name']), 'The table does not exist in the MongoDB database.');

    $this->assertFalse($schema->dropTable($this->test_table1['name']), 'Dropping a non existing table in the MongoDB database.');
  }

  /**
   * @covers ::dropTable
   */
  public function testDropTableForEmbeddedTableDoesNotExist() {
    $schema = Database::getConnection()->schema();

    $this->assertFalse($schema->tableExists($this->test_table1['name']), 'The table does not exist in the MongoDB database.');

    $schema->createTable($this->test_table1['name'], $this->test_table1['schema']);

    $this->assertTrue($schema->tableExists($this->test_table1['name']), 'The table exists in the MongoDB database.');

    $this->assertFalse($schema->dropTable($this->test_table2['name']), 'Dropping a non existing embedded table in the MongoDB database.');
  }

}
