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
   * allowing one or more partial property path matches and one or more ignored
   * constraint violation messages:
   * - Top-level keys must be an exact match for a Config object's schema type.
   * - Second-level keys must be must be wildcard matches for property paths,
   *   where any property path segment can use a wildcard (`*`) to indicate any
   *   value for that segment should be accepted for this property path to be
   *   ignored.
   * - Values of second-level keys must be regular expressions to match
   *   constraint violation messages of those property paths against.
   *
   * @var \string[][][]
   */
  protected static array $ignoredPropertyPaths = [
    'block.block.*' => [
      'settings' => [
        "'status' is a required key.",
        "'info' is a required key.",
        "'view_mode' is a required key.",
        "'context_mapping' is a required key.",
        "'items_per_page' is a required key.",
      ],
      'visibility.request_path' => [
        "'uuid' is a required key.",
      ],
    ],
    'core.base_field_override.*.*.*' => [
      'settings.handler_settings' => [
        "'target_type' is a required key.",
        "'target_bundles' is a required key.",
        "'sort' is a required key.",
        "'auto_create' is a required key.",
        "'auto_create_bundle' is a required key.",
      ],
    ],
    'core.entity_form_display.*.*.*' => [
      'content.*' => [
        "'type' is a required key.",
        "'settings' is a required key.",
      ],
      'content.description.settings' => [
        "'size' is a required key.",
        "'placeholder' is a required key.",
      ],
    ],
    'core.entity_view_display.*.*.*' => [
      'content.*' => [
        "'label' is a required key.",
        "'type' is a required key.",
        "'settings' is a required key.",
      ],
    ],
    'field.field.*.*.*' => [
      'settings.handler_settings' => [
        "'target_type' is a required key.",
        "'auto_create_bundle' is a required key.",
        "'target_bundles' is a required key.",
        "'sort' is a required key.",
        "'auto_create' is a required key.",
        "'filter' is a required key.",
        "'include_anonymous' is a required key.",
      ],
      'settings.handler_settings.sort' => [
        "'direction' is a required key.",
      ],
      'settings.handler_settings.view' => [
        "'arguments' is a required key.",
      ],
      'default_value.*.options' => [
        "'query' is a required key.",
        "'fragment' is a required key.",
        "'absolute' is a required key.",
        "'https' is a required key.",
      ],
    ],
    'field.storage.*.*' => [
      'settings' => [
        "'allowed_formats' is a required key.",
      ],
      'settings.default_image' => [
        "'alt' is a required key.",
        "'title' is a required key.",
        "'width' is a required key.",
        "'height' is a required key.",
      ],
    ],
    'filter.format.*' => [
      '' => [
        "'roles' is a required key.",
      ],
      'filters.*.settings' => [
        "'id' is a required key.",
        "'provider' is a required key.",
        "'status' is a required key.",
        "'weight' is a required key.",
        "'settings' is a required key.",
      ],
    ],
    'language.content_settings.*.*' => [
      'third_party_settings.content_translation' => [
        "'bundle_settings' is a required key.",
      ],
    ],
    'language.negotiation' => [
      '' => [
        "'session' is a required key.",
        "'selected_langcode' is a required key.",
      ],
      'url' => [
        "'domains' is a required key.",
        "'source' is a required key.",
      ],
    ],
    'language.types' => [
      'negotiation.*' => [
        "'method_weights' is a required key.",
      ],
    ],
    'test_subtheme.settings' => [
      '' => [
        "'favicon' is a required key.",
        "'logo' is a required key.",
        "'features' is a required key.",
      ],
    ],
    'test_basetheme.settings' => [
      '' => [
        "'favicon' is a required key.",
        "'logo' is a required key.",
        "'features' is a required key.",
      ],
      'features' => [
        "'logo' is a required key.",
        "'name' is a required key.",
        "'slogan' is a required key.",
        "'comment_user_picture' is a required key.",
        "'comment_user_verification' is a required key.",
        "'node_user_picture' is a required key.",
      ],
    ],
    'olivero.settings' => [
      'favicon' => [
        "'mimetype' is a required key.",
        "'path' is a required key.",
        "'url' is a required key.",
      ],
      'features' => [
        "'logo' is a required key.",
        "'name' is a required key.",
        "'slogan' is a required key.",
      ],
      'logo' => [
        "'path' is a required key.",
        "'url' is a required key.",
      ],
    ],
    'claro.settings' => [
      '' => [
        "'favicon' is a required key.",
        "'features' is a required key.",
        "'logo' is a required key.",
      ],
      'favicon' => [
        "'mimetype' is a required key.",
        "'url' is a required key.",
      ],
      'features' => [
        "'logo' is a required key.",
      ],
      'logo' => [
        "'url' is a required key.",
      ],
    ],
    'stark.settings' => [
      '' => [
        "'favicon' is a required key.",
        "'features' is a required key.",
      ],
      'logo' => [
        "'url' is a required key.",
      ],
    ],
    'system.file' => [
      '' => [
        "'allow_insecure_uploads' is a required key.",
        "'path' is a required key.",
        "'temporary_maximum_age' is a required key.",
      ],
    ],
    'rest.resource.*' => [
      'configuration' => [
        "'HEAD' is a required key.",
        "'GET' is a required key.",
        "'POST' is a required key.",
        "'PUT' is a required key.",
        "'DELETE' is a required key.",
        "'TRACE' is a required key.",
        "'OPTIONS' is a required key.",
        "'CONNECT' is a required key.",
        "'PATCH' is a required key.",
      ],
    ],
    'system.site' => [
      '' => [
        "'mail_notification' is a required key.",
        "'uuid' is a required key.",
        "'name' is a required key.",
        "'slogan' is a required key.",
        "'page' is a required key.",
        "'admin_compact_mode' is a required key.",
        "'weight_select_max' is a required key.",
        "'default_langcode' is a required key.",
      ],
    ],
    'system.performance' => [
      '' => [
        "'cache' is a required key.",
        "'css' is a required key.",
        "'fast_404' is a required key.",
      ],
      'js' => [
        "'gzip' is a required key.",
      ],
    ],
    'system.theme' => [
      '' => [
        "'admin' is a required key.",
      ],
    ],
    'system.theme.global' => [
      'features' => [
        "'logo' is a required key.",
        "'name' is a required key.",
        "'slogan' is a required key.",
      ],
    ],
    'tour.tour.*' => [
      'routes.*' => [
        "'route_params' is a required key.",
      ],
      'tips.*' => [
        "'position' is a required key.",
        "'selector' is a required key.",
      ],
    ],
    'views.view.*' => [
      'display.*' => [
        "'cache_metadata' is a required key.",
      ],
      'display.*.display_options' => [
        "'access' is a required key.",
        "'allow' is a required key.",
        "'attachment_position' is a required key.",
        "'argument' is a required key.",
        "'arguments' is a required key.",
        "'auth' is a required key.",
        "'block_category' is a required key.",
        "'block_description' is a required key.",
        "'block_hide_empty' is a required key.",
        "'cache' is a required key.",
        "'css_class' is a required key.",
        "'defaults' is a required key.",
        "'display_comment' is a required key.",
        "'display_description' is a required key.",
        "'empty' is a required key.",
        "'enabled' is a required key.",
        "'exposed_block' is a required key.",
        "'exposed_form' is a required key.",
        "'fields' is a required key.",
        "'filters' is a required key.",
        "'filter_groups' is a required key.",
        "'footer' is a required key.",
        "'format' is a required key.",
        "'group_by' is a required key.",
        "'header' is a required key.",
        "'hide_attachment_summary' is a required key.",
        "'inherit_arguments' is a required key.",
        "'inherit_exposed_filters' is a required key.",
        "'inherit_pager' is a required key.",
        "'link_display' is a required key.",
        "'link_url' is a required key.",
        "'menu' is a required key.",
        "'pager' is a required key.",
        "'path' is a required key.",
        "'query' is a required key.",
        "'relationships' is a required key.",
        "'render_pager' is a required key.",
        "'rendering_language' is a required key.",
        "'route_name' is a required key.",
        "'row' is a required key.",
        "'show_admin_links' is a required key.",
        "'sitename_title' is a required key.",
        "'sorts' is a required key.",
        "'style' is a required key.",
        "'tab_options' is a required key.",
        "'title' is a required key.",
        "'use_admin_theme' is a required key.",
        "'use_ajax' is a required key.",
        "'use_more' is a required key.",
        "'use_more_always' is a required key.",
        "'use_more_text' is a required key.",
      ],
      'display.*.display_options.cache' => [
        "'options' is a required key.",
      ],
      'display.default.display_options.query' => [
        "'options' is a required key.",
        "'type' is a required key.",
      ],
      'display.*.display_options.style' => [
        "'options' is a required key.",
      ],
      'display.*.display_options.exposed_form' => [
        "'options' is a required key.",
      ],
      'display.*.display_options.exposed_form.options' => [
        "'submit_button' is a required key.",
        "'reset_button' is a required key.",
        "'reset_button_label' is a required key.",
        "'exposed_sorts_label' is a required key.",
        "'expose_sort_order' is a required key.",
        "'sort_asc_label' is a required key.",
        "'sort_desc_label' is a required key.",
      ],
      'display.*.display_options.pager' => [
        "'type' is a required key.",
        "'options' is a required key.",
      ],
      'display.*.display_options.access' => [
        "'type' is a required key.",
        "'options' is a required key.",
      ],
      'display.*.display_options.sorts.*' => [
        "'granularity' is a required key.",
        "'entity_field' is a required key.",
        "'entity_type' is a required key.",
        "'group_type' is a required key.",
        "'admin_label' is a required key.",
        "'expose' is a required key.",
        "'exposed' is a required key.",
        "'relationship' is a required key.",
        "'order' is a required key.",
        "'plugin_id' is a required key.",
      ],
      'display.*.display_options.header.*' => [
        "'label' is a required key.",
        "'admin_label' is a required key.",
        "'entity_field' is a required key.",
        "'entity_type' is a required key.",
        "'group_type' is a required key.",
        "'relationship' is a required key.",
        "'content' is a required key.",
        "'tokenize' is a required key.",
        "'bypass_access' is a required key.",
        "'empty' is a required key.",
        "'custom_access' is a required key.",
        "'string' is a required key.",
      ],
      'display.*.display_options.cache.options' => [
        "'results_lifespan_custom' is a required key.",
        "'output_lifespan_custom' is a required key.",
      ],
      'display.*.display_options.pager.options' => [
        "'quantity' is a required key.",
        "'expose' is a required key.",
        "'offset' is a required key.",
        "'total_pages' is a required key.",
        "'id' is a required key.",
        "'items_per_page' is a required key.",
        "'tags' is a required key.",
      ],
      'display.*.display_options.query.options' => [
        "'query_comment' is a required key.",
        "'disable_sql_rewrite' is a required key.",
        "'distinct' is a required key.",
        "'replica' is a required key.",
        "'query_tags' is a required key.",
      ],
      'display.default.display_options.empty.*' => [
        "'entity_field' is a required key.",
        "'entity_type' is a required key.",
        "'label' is a required key.",
        "'admin_label' is a required key.",
        "'tokenize' is a required key.",
        "'group_type' is a required key.",
        "'relationship' is a required key.",
        "'content' is a required key.",
        "'title' is a required key.",
        "'empty' is a required key.",
        "'custom_access' is a required key.",
        "'string' is a required key.",
      ],
      'display.*.display_options.arguments.*' => [
        "'plugin_id' is a required key.",
        "'must_not_be' is a required key.",
        "'day' is a required key.",
        "'month' is a required key.",
        "'default_action' is a required key.",
        "'exception' is a required key.",
        "'title' is a required key.",
        "'title_enable' is a required key.",
        "'default_argument_type' is a required key.",
        "'summary' is a required key.",
        "'summary_options' is a required key.",
        "'specify_validation' is a required key.",
        "'not' is a required key.",
        "'add_table' is a required key.",
        "'require_value' is a required key.",
        "'glossary' is a required key.",
        "'limit' is a required key.",
        "'case' is a required key.",
        "'path_case' is a required key.",
        "'transform_dash' is a required key.",
        "'relationship' is a required key.",
        "'group_type' is a required key.",
        "'admin_label' is a required key.",
        "'entity_field' is a required key.",
        "'entity_type' is a required key.",
        "'default_argument_options' is a required key.",
        "'default_argument_skip_url' is a required key.",
        "'validate' is a required key.",
        "'validate_options' is a required key.",
        "'break_phrase' is a required key.",
        "'date' is a required key.",
        "'node_created' is a required key.",
        "'node_changed' is a required key.",
        "'created' is a required key.",
      ],
      'display.*.display_options.arguments.*.summary' => [
        "'sort_order' is a required key.",
        "'number_of_records' is a required key.",
      ],
      'display.*.display_options.arguments.*.validate' => [
        "'fail' is a required key.",
      ],
      'display.*.display_options.arguments.*.validate_options' => [
        "'vids' is a required key.",
        "'multiple' is a required key.",
      ],
      'display.*.display_options.arguments.*.exception' => [
        "'title' is a required key.",
        "'value' is a required key.",
      ],
      'display.*.display_options.arguments.*.summary_options' => [
        "'items_per_page' is a required key.",
        "'grouping' is a required key.",
        "'row_class' is a required key.",
        "'default_row_class' is a required key.",
        "'uses_fields' is a required key.",
        "'base_path' is a required key.",
        "'count' is a required key.",
        "'override' is a required key.",
      ],
      'display.*.display_options.defaults' => [
        "'pager' is a required key.",
        "'relationships' is a required key.",
        "'empty' is a required key.",
        "'access' is a required key.",
        "'cache' is a required key.",
        "'query' is a required key.",
        "'title' is a required key.",
        "'css_class' is a required key.",
        "'display_description' is a required key.",
        "'use_ajax' is a required key.",
        "'hide_attachment_summary' is a required key.",
        "'show_admin_links' is a required key.",
        "'use_more' is a required key.",
        "'use_more_always' is a required key.",
        "'use_more_text' is a required key.",
        "'exposed_form' is a required key.",
        "'link_display' is a required key.",
        "'link_url' is a required key.",
        "'group_by' is a required key.",
        "'style' is a required key.",
        "'row' is a required key.",
        "'fields' is a required key.",
        "'sorts' is a required key.",
        "'arguments' is a required key.",
        "'filters' is a required key.",
        "'filter_groups' is a required key.",
        "'header' is a required key.",
        "'footer' is a required key.",
      ],
      'display.*.display_options.footer.*' => [
        "'content' is a required key.",
        "'entity_field' is a required key.",
        "'entity_type' is a required key.",
        "'label' is a required key.",
        "'admin_label' is a required key.",
        "'relationship' is a required key.",
        "'group_type' is a required key.",
        "'tokenize' is a required key.",
        "'custom_access' is a required key.",
        "'string' is a required key.",
        "'empty' is a required key.",
        "'bypass_access' is a required key.",
      ],
      'display.*.display_options.fields.*' => [
        "'link_to_user' is a required key.",
        "'link_to_node' is a required key.",
        "'field' is a required key.",
        "'table' is a required key.",
        "'id' is a required key.",
        "'output_url_as_text' is a required key.",
        "'absolute' is a required key.",
        "'label' is a required key.",
        "'decimal' is a required key.",
        "'set_precision' is a required key.",
        "'precision' is a required key.",
        "'format_plural' is a required key.",
        "'format_plural_string' is a required key.",
        "'prefix' is a required key.",
        "'suffix' is a required key.",
        "'plugin_id' is a required key.",
        "'exclude' is a required key.",
        "'alter' is a required key.",
        "'element_class' is a required key.",
        "'element_default_classes' is a required key.",
        "'empty' is a required key.",
        "'hide_empty' is a required key.",
        "'empty_zero' is a required key.",
        "'hide_alter_empty' is a required key.",
        "'relationship' is a required key.",
        "'group_type' is a required key.",
        "'admin_label' is a required key.",
        "'element_type' is a required key.",
        "'element_label_type' is a required key.",
        "'element_label_class' is a required key.",
        "'element_label_colon' is a required key.",
        "'element_wrapper_type' is a required key.",
        "'element_wrapper_class' is a required key.",
        "'action_title' is a required key.",
        "'include_exclude' is a required key.",
        "'selected_actions' is a required key.",
        "'destination' is a required key.",
        "'click_sort_column' is a required key.",
        "'type' is a required key.",
        "'settings' is a required key.",
        "'group_column' is a required key.",
        "'group_columns' is a required key.",
        "'group_rows' is a required key.",
        "'delta_limit' is a required key.",
        "'delta_offset' is a required key.",
        "'delta_reversed' is a required key.",
        "'delta_first_last' is a required key.",
        "'multi_type' is a required key.",
        "'separator' is a required key.",
        "'field_api_classes' is a required key.",
        "'text' is a required key.",
        "'path' is a required key.",
        "'external' is a required key.",
        "'entity_type' is a required key.",
        "'entity_field' is a required key.",
        "'destination' is a required key.",
      ],
      'display.*.display_options.fields.*.alter' => [
        "'alter_text' is a required key.",
        "'make_link' is a required key.",
        "'absolute' is a required key.",
        "'word_boundary' is a required key.",
        "'ellipsis' is a required key.",
        "'strip_tags' is a required key.",
        "'trim' is a required key.",
        "'html' is a required key.",
        "'text' is a required key.",
        "'path' is a required key.",
        "'external' is a required key.",
        "'replace_spaces' is a required key.",
        "'path_case' is a required key.",
        "'trim_whitespace' is a required key.",
        "'alt' is a required key.",
        "'rel' is a required key.",
        "'link_class' is a required key.",
        "'prefix' is a required key.",
        "'suffix' is a required key.",
        "'target' is a required key.",
        "'nl2br' is a required key.",
        "'max_length' is a required key.",
        "'more_link' is a required key.",
        "'more_link_text' is a required key.",
        "'more_link_path' is a required key.",
        "'preserve_tags' is a required key.",
      ],
      'display.*.display_options.fields.*.settings' => [
        "'granularity' is a required key.",
        "'past_format' is a required key.",
        "'future_format' is a required key.",
        "'image_loading' is a required key.",
        "'link_to_entity' is a required key.",
        "'link_to_file' is a required key.",
      ],
      'display.*.display_options.pager.options.tags' => [
        "'quantity' is a required key.",
      ],
      'display.default.display_options.empty.area_text_custom' => [
        "'relationship' is a required key.",
        "'group_type' is a required key.",
        "'admin_label' is a required key.",
        "'entity_type' is a required key.",
        "'entity_field' is a required key.",
        "'label' is a required key.",
        "'tokenize' is a required key.",
      ],
      'display.*.display_options.filters.*' => [
        "'reduce_duplicates' is a required key.",
        "'type' is a required key.",
        "'entity_field' is a required key.",
        "'entity_type' is a required key.",
        "'relationship' is a required key.",
        "'group_type' is a required key.",
        "'admin_label' is a required key.",
        "'operator' is a required key.",
        "'exposed' is a required key.",
        "'is_grouped' is a required key.",
        "'group_info' is a required key.",
        "'expose' is a required key.",
        "'group' is a required key.",
        "'value' is a required key.",
        "'plugin_id' is a required key.",
      ],
      'display.*.display_options.filters.*.group_info' => [
        "'default_group_multiple' is a required key.",
        "'remember' is a required key.",
        "'multiple' is a required key.",
        "'widget' is a required key.",
        "'optional' is a required key.",
        "'description' is a required key.",
      ],
      'display.*.display_options.filters.*.group_info.group_items.*' => [
        "'title' is a required key.",
        "'operator' is a required key.",
        "'value' is a required key.",
      ],
      'display.*.display_options.filters.*.group_info.group_items.*.value' => [
        "'value' is a required key.",
        "'min' is a required key.",
        "'max' is a required key.",
      ],
      'display.*.display_options.filters.*.value' => [
        "'min' is a required key.",
        "'max' is a required key.",
        "'type' is a required key.",
      ],
      'display.*.display_options.sorts.*.expose' => [
        "'field_identifier' is a required key.",
        "'label' is a required key.",
      ],
      'display.*.display_options.filters.*.expose' => [
        "'reduce' is a required key.",
        "'placeholder' is a required key.",
        "'min_placeholder' is a required key.",
        "'max_placeholder' is a required key.",
        "'remember_roles' is a required key.",
        "'required' is a required key.",
        "'remember' is a required key.",
        "'multiple' is a required key.",
        "'identifier' is a required key.",
        "'use_operator' is a required key.",
        "'description' is a required key.",
        "'label' is a required key.",
        "'operator_id' is a required key.",
        "'operator' is a required key.",
        "'operator_limit_selection' is a required key.",
        "'operator_list' is a required key.",
      ],
      'display.*.display_options.style.options' => [
        "'uses_fields' is a required key.",
        "'order' is a required key.",
        "'description' is a required key.",
        "'caption' is a required key.",
        "'default_row_class' is a required key.",
        "'row_class' is a required key.",
        "'grouping' is a required key.",
        "'columns' is a required key.",
        "'default' is a required key.",
        "'info' is a required key.",
        "'override' is a required key.",
        "'sticky' is a required key.",
        "'summary' is a required key.",
        "'empty_table' is a required key.",
        "'alignment' is a required key.",
        "'cell_min_width' is a required key.",
        "'grid_gutter' is a required key.",
      ],
      'display.*.display_options.style.options.info.*' => [
        "'align' is a required key.",
        "'sortable' is a required key.",
        "'default_sort_order' is a required key.",
        "'empty_column' is a required key.",
        "'responsive' is a required key.",
      ],
      'display.*.display_options.row' => [
        "'options' is a required key.",
      ],
      'display.*.display_options.row.options' => [
        "'inline' is a required key.",
        "'separator' is a required key.",
        "'hide_empty' is a required key.",
        "'relationship' is a required key.",
        "'default_field_elements' is a required key.",
      ],
      'display.*.display_options.menu' => [
        "'enabled' is a required key.",
        "'expanded' is a required key.",
        "'parent' is a required key.",
        "'description' is a required key.",
        "'context' is a required key.",
      ],
      'display.*.display_options.relationships.*' => [
        "'entity_type' is a required key.",
        "'entity_field' is a required key.",
        "'plugin_id' is a required key.",
        "'group_type' is a required key.",
        "'relationship' is a required key.",
        "'admin_label' is a required key.",
        "'required' is a required key.",
      ],
      'display.default.display_options.cache_metadata' => [
        "'cacheable' is a required key.",
      ],
      'display.*.cache_metadata' => [
        "'cacheable' is a required key.",
      ],
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
    $filtered_violations = array_filter(
      iterator_to_array($violations),
      fn (ConstraintViolation $v) => !static::isViolationForIgnoredPropertyPath($v),
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

    foreach (static::$ignoredPropertyPaths[$config_object_data_type] as $ignored_property_path_expression => $ignored_validation_constraint_messages) {
      // Convert the wildcard-based expression to a regex: treat `*` nor in the
      // regex sense nor as something to be escaped: treat it as the wildcard
      // for a segment in a property path (property path segments are separated
      // by periods).
      // That requires first ensuring that preg_quote() does not escape it, and
      // then replacing it with an appropriate regular expression: `[^\.]+`,
      // which means: ">=1 characters that are anything except a period".
      $ignored_property_path_regex = str_replace(' ', '[^\.]+', preg_quote(str_replace('*', ' ', $ignored_property_path_expression)));

      // To ignore this violation constraint, require a match on both the
      // property path and the message.
      $property_path_match = preg_match('/^' . $ignored_property_path_regex . '$/', $v->getPropertyPath(), $matches) === 1;
      if ($property_path_match) {
        return preg_match(sprintf("/^(%s)$/", implode('|', $ignored_validation_constraint_messages)), (string) $v->getMessage()) === 1;
      }
    }
    return FALSE;
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
