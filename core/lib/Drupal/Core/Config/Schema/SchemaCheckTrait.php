<?php

namespace Drupal\Core\Config\Schema;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Config\Entity\ConfigEntityType;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Entity\Plugin\DataType\ConfigEntityAdapter;
use Drupal\Core\TypedData\PrimitiveInterface;
use Drupal\Core\TypedData\TraversableTypedDataInterface;
use Drupal\Core\TypedData\Type\BooleanInterface;
use Drupal\Core\TypedData\Type\StringInterface;
use Drupal\Core\TypedData\Type\FloatInterface;
use Drupal\Core\TypedData\Type\IntegerInterface;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Provides a trait for checking configuration schema.
 */
trait SchemaCheckTrait {

  /**
   * The config schema wrapper object for the configuration object under test.
   */
  protected TraversableTypedDataInterface $schema;

  /**
   * The configuration object name under test.
   */
  protected string $configName;

  /**
   * The ignored property paths.
   *
   * Allow ignoring specific config schema types (top-level keys, require an
   * exact match to one of the top-level entries in *.schema.yml files) by
   * allowing one or more partial property path matches.
   *
   * Keys must be an exact match for a Config object's schema type.
   * Values must be wildcard matches for property paths, where any property
   * path segment can use a wildcard (`*`) to indicate any value for that
   * segment should be accepted for this property path to be ignored.
   *
   * @var \string[][]
   */
  protected static array $ignoredPropertyPaths = [
    'block.block.*' => [
      // @todo Fix config or tweak schema of `type: block.block.*`.
      // @see block.schema.yml
      'weight',
      'provider',
    ],
    'block_content.type.*' => [
      // @todo Fix config or tweak schema of `type: block_content.type.*`.
      // @see block_content.schema.yml
      'description',
      'label',
      'revision',
    ],
    'comment.type.*' => [
      // @todo Fix config or tweak schema of `type: comment.type.*`.
      // @see comment.schema.yml
      'description',
      'label',
    ],
    'contact.form.*' => [
      // @todo Fix config or tweak schema of `type: contact.form.*`.
      // @see contact.schema.yml
      'message',
      'redirect',
    ],
    'core.base_field_override.*.*.*' => [
      // @todo Fix config or tweak schema of `type: core.base_field_override.*.*.*`.
      // @see core.data_types.schema.yml
      'label',
      // @todo Fix config or tweak schema of `type: field.field_settings.integer`.
      // @see core.data_types.schema.yml
      'settings.min',
      'settings.max',
    ],
    'core.date_format.*' => [
      // @todo Fix config or tweak schema of `type: core.date_format.*`.
      // @see core.data_types.schema.yml
      'label',
      'pattern',
    ],
    'core.entity_form_mode.*.*' => [
      // @todo Fix config or tweak schema of `type: core.entity_form_mode.*.*`.
      // @see core.entity.schema.yml
      'description',
      'label',
    ],
    'core.entity_view_mode.*.*' => [
      // @todo Fix config or tweak schema of `type: core.entity_view_mode.*.*`.
      // @see core.entity.schema.yml
      'description',
      'label',
    ],
    'core.entity_form_display.*.*.*' => [
      // @todo Fix config or tweak schema of `type: core.entity_form_display.*.*.*`.
      // @see core.entity.schema.yml
      'status',
    ],
    'core.entity_view_display.*.*.*' => [
      // @todo Fix config or tweak schema of `type: core.entity_view_display.*.*.*`.
      // @see core.entity.schema.yml
      'status',
    ],
    'views.view.*' => [
      // Values may be
      // @todo Fix config or tweak schema of `type: views_pager_sql`.
      // @see views.data_types.schema.yml
      'display.*.display_options.pager.options.total_pages',
      'display.*.display_options.pager.options.items_per_page',
      // @todo Fix config or tweak schema of `type: views_filter`.
      // @see views.data_types.schema.yml
      'display.*.display_options.filters.*.expose.description',
      // @todo Fix config or tweak schema of `type: views_handler`.
      // @see views.data_types.schema.yml
      'display.*.display_options.fields.*.entity_type',
      'display.*.display_options.fields.*.entity_field',
      // @todo Fix config or tweak schema of `type: views_filter`.
      // @see views.data_types.schema.yml
      'display.*.display_options.filters.bundle.group_info.description',
      // @todo Fix config or tweak schema of `type: views.view.*`.
      // @see views.schema.yml
      'display.*.position',
      'label',
    ],
    'entity_test.entity_test_bundle.*' => [
      // @todo Fix config or tweak schema of `type: entity_test.entity_test_bundle.*`.
      // @see entity_test.schema.yml
      'description',
      'label',
    ],
    'field.field.*.*.*' => [
      // @todo Fix config or tweak schema of `type: field.value.comment`.
      // @see comment.schema.yml
      'default_value.*.last_comment_name',
      // @todo Fix config or tweak schema of `type: field.field_settings.images`.
      // @see image.schema.yml
      'settings.default_image.uuid',
      'settings.default_image.width',
      'settings.default_image.height',
      // @todo Fix config or tweak schema of `type: field.field_settings.integer`.
      // @see core.data_types.schema.yml
      'settings.min',
      'settings.max',
    ],
    'field.storage.*.*' => [
      // @todo Fix config or tweak schema of `type: field.storage_settings.image`.
      // @see image.schema.yml
      'settings.default_image.uuid',
      'settings.default_image.width',
      'settings.default_image.height',
    ],
    'image.style.*' => [
      // @todo Fix config or tweak schema of `type: image.effect.image_rotate`.
      // @see image.schema.yml
      'effects.*.data.bgcolor',
      // @todo Fix config or tweak schema of `type: image.effect.image_scale`.
      // @see image.schema.yml
      'effects.*.data.height',
      'effects.*.data.width',
      // @todo Fix config or tweak schema of `type: image.style.*`.
      // @see image.schema.yml
      'effects.*.weight',
      'label',
    ],
    'language.entity.*' => [
      // @todo Fix config or tweak schema of `type: language.entity.*`.
      // @see language.schema.yml
      'label',
    ],
    'language.negotiation' => [
      // @todo Fix config or tweak schema of `type: language.negotiation`.
      // @see language.schema.yml
      'url.prefixes',
    ],
    'media.type.*' => [
      // @todo Fix config or tweak schema of `type: media.type.*`.
      // @see media.schema.yml
      'label',
      'description',
    ],
    'node.type.*' => [
      // @todo Fix config or tweak schema of `type: node.type.*`.
      // @see node.schema.yml
      'name',
      'description',
      'help',
    ],
    'search.page.*' => [
      // @todo Fix config or tweak schema of `type: search.page.*`.
      // @see search.schema.yml
      'label',
      'path',
    ],
    'system.action.*' => [
      // @todo Fix config or tweak schema of `type: system.action.*`.
      // @see system.schema.yml
      'description',
      'label',
      'type',
    ],
    'system.menu.*' => [
      // @todo Fix config or tweak schema of `type: system.menu.*`.
      // @see system.schema.yml
      'description',
      'label',
    ],
    'taxonomy.vocabulary.*' => [
      // @todo Fix config or tweak schema of `type: taxonomy.vocabulary.*`.
      // @see taxonomy.schema.yml
      'name',
      'description',
    ],
    'user.role.*' => [
      // @todo Fix config or tweak schema of `type: user.role.*`.
      // @see user.schema.yml
      'is_admin',
      'weight',
    ],
    'user.settings' => [
      // @todo Fix config or tweak schema of `type: user.settings`.
      // @see user.schema.yml
      'cancel_method',
      'register',
    ],
    'workflows.workflow.*' => [
      // @todo Fix config or tweak schema of `type: workflows.workflow.*`.
      // @see workflows.schema.yml
      'label',
    ],
  ];

  /**
   * Checks the TypedConfigManager has a valid schema for the configuration.
   *
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typed_config
   *   The TypedConfigManager.
   * @param string $config_name
   *   The configuration name.
   * @param array $config_data
   *   The configuration data, assumed to be data for a top-level config object.
   *
   * @return array|bool
   *   FALSE if no schema found. List of errors if any found. TRUE if fully
   *   valid.
   */
  public function checkConfigSchema(TypedConfigManagerInterface $typed_config, $config_name, $config_data) {
    // We'd like to verify that the top-level type is either config_base,
    // config_entity, or a derivative. The only thing we can really test though
    // is that the schema supports having langcode in it. So add 'langcode' to
    // the data if it doesn't already exist.
    if (!isset($config_data['langcode'])) {
      $config_data['langcode'] = 'en';
    }
    $this->configName = $config_name;
    if (!$typed_config->hasConfigSchema($config_name)) {
      return FALSE;
    }
    $this->schema = $typed_config->createFromNameAndData($config_name, $config_data);
    $errors = [];
    foreach ($config_data as $key => $value) {
      $errors[] = $this->checkValue($key, $value);
    }
    $errors = array_merge(...$errors);
    // Also perform explicit validation. Note this does NOT require every node
    // in the config schema tree to have validation constraints defined.
    $violations = $this->schema->validate();
    $ignored_validation_constraint_messages = [
      // Currently none!
    ];
    $filtered_violations = array_filter(
      iterator_to_array($violations),
      fn (ConstraintViolation $v) =>
        // Ignore violation messages in $ignored_validation_constraint_messages.
        preg_match(sprintf("/^(%s)$/", implode('|', $ignored_validation_constraint_messages)), (string) $v->getMessage()) !== 1
        // Ignore violation messages for static::$ignoredPropertyPaths.
        && !static::isViolationForIgnoredPropertyPath($v),
    );
    $validation_errors = array_map(
      fn (ConstraintViolation $v) => sprintf("[%s] %s", $v->getPropertyPath(), (string) $v->getMessage()),
      $filtered_violations
    );
    $errors = array_merge($errors, $validation_errors);
    if (empty($errors)) {
      return TRUE;
    }
    return $errors;
  }

  /**
   * Determines whether this violation is for an ignored Config property path.
   *
   * @param \Symfony\Component\Validator\ConstraintViolation $v
   *   A validation constraint violation for a Config object.
   *
   * @return bool
   */
  protected static function isViolationForIgnoredPropertyPath(ConstraintViolation $v): bool {
    // When the validated object is a config entity wrapped in a
    // ConfigEntityAdapter, some work is necessary to map from e.g.
    // `entity:comment_type` to the corresponding `comment.type.*`.
    if ($v->getRoot() instanceof ConfigEntityAdapter) {
      $config_entity = $v->getRoot()->getEntity();
      assert($config_entity instanceof ConfigEntityInterface);
      $config_entity_type = $config_entity->getEntityType();
      assert($config_entity_type instanceof ConfigEntityType);
      $config_prefix = $config_entity_type->getConfigPrefix();
      // Compute the data type of the config object being validated:
      // - the config entity type's config prefix
      // - with as many `.*`-suffixes appended as there are parts in the ID (for
      //   example, for NodeType there's only 1 part, for EntityViewDisplay
      //   there are 3 parts.)
      // TRICKY: in principle it is possible to compute the exact number of
      // suffixes by inspecting ConfigEntity::getConfigDependencyName(), except
      // when the entity ID itself is invalid. Unfortunately that means
      // gradually discovering it is the only available alternative.
      $suffix_count = 1;
      do {
        $config_object_data_type = $config_prefix . str_repeat('.*', $suffix_count);
        $suffix_count++;
      } while ($suffix_count <= 3 && !array_key_exists($config_object_data_type, static::$ignoredPropertyPaths));
    }
    else {
      $config_object_data_type = $v->getRoot()
        ->getDataDefinition()
        ->getDataType();
    }
    if (!array_key_exists($config_object_data_type, static::$ignoredPropertyPaths)) {
      return FALSE;
    }

    $ignored_property_paths_as_partial_regexes = array_map(
      // Treat `*` nor in the regex sense nor as something to be escaped: treat
      // it as the wildcard for a segment in a property path (property path
      // segments are separated by periods).
      // That requires first ensuring that preg_quote() does not escape it, and
      // then replacing it with an appropriate regular expression: `[^\.]+`,
      // which means: ">=1 characters that are anything except a period".
      fn ($s) => str_replace(' ', '[^\.]+', preg_quote(str_replace('*', ' ', $s))),
      static::$ignoredPropertyPaths[$config_object_data_type]
    );

    // All ignored property path expressions are combined into a single regex
    // capture group.
    $regex_capture_group = implode('|', $ignored_property_paths_as_partial_regexes);

    // Require an exact match to one of the ignored property path expressions.
    return preg_match('/^(' . $regex_capture_group . ')$/', $v->getPropertyPath()) === 1;
  }

  /**
   * Helper method to check data type.
   *
   * @param string $key
   *   A string of configuration key.
   * @param mixed $value
   *   Value of given key.
   *
   * @return array
   *   List of errors found while checking with the corresponding schema.
   */
  protected function checkValue($key, $value) {
    $error_key = $this->configName . ':' . $key;
    /** @var \Drupal\Core\TypedData\TypedDataInterface $element */
    $element = $this->schema->get($key);

    // Check if this type has been deprecated.
    $data_definition = $element->getDataDefinition();
    if (!empty($data_definition['deprecated'])) {
      @trigger_error($data_definition['deprecated'], E_USER_DEPRECATED);
    }

    if ($element instanceof Undefined) {
      return [$error_key => 'missing schema'];
    }

    // Do not check value if it is defined to be ignored.
    if ($element && $element instanceof Ignore) {
      return [];
    }

    if ($element && is_scalar($value) || $value === NULL) {
      $success = FALSE;
      $type = gettype($value);
      if ($element instanceof PrimitiveInterface) {
        $success =
          ($type == 'integer' && $element instanceof IntegerInterface) ||
          // Allow integer values in a float field.
          (($type == 'double' || $type == 'integer') && $element instanceof FloatInterface) ||
          ($type == 'boolean' && $element instanceof BooleanInterface) ||
          ($type == 'string' && $element instanceof StringInterface) ||
          // Null values are allowed for all primitive types.
          ($value === NULL);
      }
      // Array elements can also opt-in for allowing a NULL value.
      elseif ($element instanceof ArrayElement && $element->isNullable() && $value === NULL) {
        $success = TRUE;
      }
      $class = get_class($element);
      if (!$success) {
        return [$error_key => "variable type is $type but applied schema class is $class"];
      }
    }
    else {
      $errors = [];
      if (!$element instanceof TraversableTypedDataInterface) {
        $errors[$error_key] = 'non-scalar value but not defined as an array (such as mapping or sequence)';
      }

      // Go on processing so we can get errors on all levels. Any non-scalar
      // value must be an array so cast to an array.
      if (!is_array($value)) {
        $value = (array) $value;
      }
      $nested_errors = [];
      // Recurse into any nested keys.
      foreach ($value as $nested_value_key => $nested_value) {
        $nested_errors[] = $this->checkValue($key . '.' . $nested_value_key, $nested_value);
      }
      return array_merge($errors, ...$nested_errors);
    }
    // No errors found.
    return [];
  }

}
