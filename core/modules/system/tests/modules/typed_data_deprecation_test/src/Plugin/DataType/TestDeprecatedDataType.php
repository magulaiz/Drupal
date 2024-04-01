<?php

namespace Drupal\typed_data_deprecation_test\Plugin\DataType;

use Drupal\Core\TypedData\TypedData;

/**
 * Provides a deprecated test data type.
 *
 * @DataType(
 *   id = "test_deprecated_data_type",
 *   deprecation_message = "The test_deprecated_data_type plugin is deprecated",
 * )
 */
class TestDeprecatedDataType extends TypedData {}
