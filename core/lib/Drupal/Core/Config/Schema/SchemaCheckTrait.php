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
    'search.page.*' => [
      // @todo Fix config or tweak schema of `type: search.page.*` in
      //   https://drupal.org/i/3380475.
      // @see search.schema.yml
      'label' => [
        'This value should not be blank.',
      ],
    ],
    'block.block.*' => [
      'settings' => [
        "'status' is a required key.",
        "'info' is a required key.",
        "'view_mode' is a required key.",
        "'context_mapping' is a required key.",
        "'items_per_page' is a required key.",
        "'level' is a required key.",
        "'depth' is a required key.",
        "'expand_all_items' is a required key.",
        "'views_label' is a required key.",
      ],
      'visibility.request_path' => [
        "'uuid' is a required key.",
        "'context_mapping' is a required key.",
      ],
      'visibility.user_role' => [
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
      'content.*.settings' => [
        "'.*' is a conditionally required key because content\..*\.type is .* \(see config schema type field\.widget\.settings\..*",
      ],
    ],
    'core.entity_view_display.*.*.*' => [
      'content.*' => [
        "'label' is a required key.",
        "'type' is a required key.",
        "'settings' is a required key.",
        "'weight' is a required key.",
      ],
      'content.*.settings' => [
        "'pager_id' is a required key.",
      ],
      'third_party_settings.layout_builder' => [
        "'sections' is a required key.",
        "'allow_custom' is a required key.",
        "'enabled' is a required key.",
      ],
      'third_party_settings.layout_builder.sections.*.layout_settings' => [
        "'label' is a required key.",
        "'context_mapping' is a required key.",
      ],
      'third_party_settings.field_layout.settings' => [
        "'label' is a required key.",
        "'context_mapping' is a required key.",
      ],
      'third_party_settings.layout_builder.sections.*.components.*.configuration' => [
        "'label' is a required key.",
        "'label_display' is a required key.",
        "'provider' is a required key.",
        "'info' is a required key.",
        "'view_mode' is a required key.",
        "'status' is a required key.",
        "'context_mapping' is a required key.",
        "'formatter' is a conditionally required key because third_party_settings\.layout_builder\.sections\..*\.configuration\.id is extra_field_block:.* \(see config schema type block\.settings\.extra_field_block:\*:\*:\*.*",
      ],
    ],
    'layout_builder_test.test_simple_config.*' => [
      'sections.*.layout_settings' => [
        "'label' is a required key.",
        "'context_mapping' is a required key.",
      ],
      'sections.*.components.*.configuration' => [
        "'label' is a required key.",
        "'label_display' is a required key.",
        "'provider' is a required key.",
        "'info' is a required key.",
        "'view_mode' is a required key.",
        "'status' is a required key.",
        "'context_mapping' is a required key.",
      ],
    ],
    'field.field.*.*.*' => [
      'settings' => [
        "'allowed_formats' is a required key.",
      ],
      'settings.handler_settings' => [
        "'target_type' is a required key.",
        "'view' is a required key.",
        // @see \Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection
        // @see \Drupal\user\Plugin\EntityReferenceSelection\UserSelection (inherited)
        "'auto_create' is a conditionally required key because settings\.handler is default:.* \(see config schema type entity_reference_selection\.default:.*",
        "'auto_create_bundle' is a conditionally required key because settings\.handler is default:.* \(see config schema type entity_reference_selection\.default:.*",
        "'sort' is a conditionally required key because settings\.handler is default:.* \(see config schema type entity_reference_selection\.default:.*",
        "'target_bundles' is a conditionally required key because settings\.handler is default:.* \(see config schema type entity_reference_selection\.default:.*",
        // @see \Drupal\user\Plugin\EntityReferenceSelection\UserSelection
        "'filter' is a conditionally required key because settings\.handler is default:user \(see config schema type entity_reference_selection\.default:user.*",
        "'include_anonymous' is a conditionally required key because settings\.handler is default:user \(see config schema type entity_reference_selection\.default:user.*",
      ],
      'settings.handler_settings.sort' => [
        "'direction' is a required key.",
      ],
      'settings.handler_settings.view' => [
        "'arguments' is a required key.",
      ],
      'settings.handler_settings.filter' => [
        "'role' is a required key.",
      ],
      'default_value.*' => [
        "'format' is a required key.",
        "'attributes' is a required key.",
        "'target_uuid' is a required key.",
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
        "'is_ascii' is a required key.",
        // @see \Drupal\text\Plugin\Field\FieldType\TextItemBase
        "'allowed_formats' is a conditionally required key because type is (text|text_long|text_with_summary) \(see config schema type field\.storage_settings\.text.*",
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
        "'restrictions' is a required key.",
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
      'path' => [
        "'temporary' is a required key.",
      ],
    ],
    'rest.resource.*' => [
      'configuration' => [
        "'.*' is a conditionally required key because granularity is method \(see config schema type rest_resource.method.*",
      ],
    ],
    'contact.settings' => [
      'flood' => [
        "'interval' is a required key.",
      ],
    ],
    'file.settings' => [
      '' => [
        "'make_unused_managed_files_temporary' is a required key.",
      ],
    ],
    'statistics.settings' => [
      '' => [
        "'display_max_age' is a required key.",
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
        "'mail' is a required key.",
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
    'locale.settings' => [
      '' => [
        "'translate_english' is a required key.",
        "'translation' is a required key.",
        "'cache_strings' is a required key.",
        "'javascript' is a required key.",
      ],
    ],
    'search.settings' => [
      '' => [
        "'and_or_limit' is a required key.",
        "'default_page' is a required key.",
      ],
      'index' => [
        "'tag_weights' is a required key.",
      ],
    ],
    'syslog.settings' => [
      '' => [
        "'format' is a required key.",
      ],
    ],
    'taxonomy.settings' => [
      '' => [
        "'maintain_index_table' is a required key.",
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
    'update.settings' => [
      'check' => [
        "'disabled_extensions' is a required key.",
      ],
      'fetch' => [
        "'timeout' is a required key.",
      ],
    ],
    'user.mail' => [
      '' => [
        "'register_pending_approval_admin' is a required key.",
        "'status_canceled' is a required key.",
      ],
    ],
    'user.settings' => [
      '' => [
        "'cancel_method' is a required key.",
        "'password_reset_timeout' is a required key.",
        "'password_strength' is a required key.",
      ],
      'notify' => [
        "'cancel_confirm' is a required key.",
        "'password_reset' is a required key.",
        "'status_canceled' is a required key.",
        "'register_admin_created' is a required key.",
        "'register_no_approval_required' is a required key.",
        "'register_pending_approval' is a required key.",
      ],
    ],
    'views.view.*' => [
      '' => [
        "'uuid' is a required key.",
        "'label' is a required key.",
        "'dependencies' is a required key.",
        "'description' is a required key.",
        "'tag' is a required key.",
      ],
      'display.*' => [
        "'cache_metadata' is a required key.",
        "'display_options' is a required key.",
        "'display_title' is a required key.",
        "'position' is a required key.",
      ],
      'display.*.display_options' => [
        "'access' is a required key.",
        "'argument' is a required key.",
        "'arguments' is a required key.",
        "'auth' is a required key.",
        "'cache' is a required key.",
        "'css_class' is a required key.",
        "'defaults' is a required key.",
        "'display_comment' is a required key.",
        "'display_description' is a required key.",
        "'display_extenders' is a required key.",
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
        "'link_display' is a required key.",
        "'link_url' is a required key.",
        "'pager' is a required key.",
        "'query' is a required key.",
        "'relationships' is a required key.",
        "'rendering_language' is a required key.",
        "'row' is a required key.",
        "'show_admin_links' is a required key.",
        "'sorts' is a required key.",
        "'style' is a required key.",
        "'title' is a required key.",
        "'use_ajax' is a required key.",
        "'use_more' is a required key.",
        "'use_more_always' is a required key.",
        "'use_more_text' is a required key.",
        // @see \Drupal\views\Plugin\views\display\Attachment
        "'attachment_position' is a conditionally required key because display\..*\.display_plugin is attachment \(see config schema type views\.display\.attachment.*",
        "'inherit_arguments' is a conditionally required key because display\..*\.display_plugin is attachment \(see config schema type views\.display\.attachment.*",
        "'inherit_exposed_filters' is a conditionally required key because display\..*\.display_plugin is attachment \(see config schema type views\.display\.attachment.*",
        "'inherit_pager' is a conditionally required key because display\..*\.display_plugin is attachment \(see config schema type views\.display\.attachment.*",
        "'render_pager' is a conditionally required key because display\..*\.display_plugin is attachment \(see config schema type views\.display\.attachment.*",
        // @see \Drupal\rest\Plugin\views\display\RestExport
        "'auth' is a conditionally required key because display\..*\.display_plugin is rest_export \(see config schema type views\.display\.rest_export.*",
        // @see \Drupal\views\Plugin\views\display\Block
        "'block_category' is a conditionally required key because display\..*\.display_plugin is block \(see config schema type views\.display\.block.*",
        "'block_description' is a conditionally required key because display\..*\.display_plugin is block \(see config schema type views\.display\.block.*",
        "'block_hide_empty' is a conditionally required key because display\..*\.display_plugin is block \(see config schema type views\.display\.block.*",
        "'block_hide_empty' is a conditionally required key because display\..*\.display_plugin is block \(see config schema type views\.display\.block.*",
        "'allow' is a conditionally required key because display\..*\.display_plugin is block \(see config schema type views\.display\.block.*",
        // @see \Drupal\views\Plugin\views\display\Feed
        "'displays' is a conditionally required key because display\..*\.display_plugin is feed \(see config schema type views\.display\.feed.*",
        "'sitename_title' is a conditionally required key because display\..*\.display_plugin is feed \(see config schema type views\.display\.feed.*",
        // @see \Drupal\views\Plugin\views\display\Page
        "'menu' is a conditionally required key because display\..*\.display_plugin is page \(see config schema type views\.display\.page.*",
        "'tab_options' is a conditionally required key because display\..*\.display_plugin is page \(see config schema type views\.display\.page.*",
        "'use_admin_theme' is a conditionally required key because display\..*\.display_plugin is page \(see config schema type views\.display\.page.*",
        // @see \Drupal\views\Plugin\views\display\PathPluginBase
        // @see \Drupal\views\Plugin\views\display\Feed (inherited)
        // @see \Drupal\views\Plugin\views\display\Page (inherited)
        // @see \Drupal\rest\Plugin\views\display\RestExport (inherited)
        "'path' is a conditionally required key because display\..*\.display_plugin is (feed|page|rest_export) \(see config schema type views\.display\..*",
        "'route_name' is a conditionally required key because display\..*\.display_plugin is (feed|page|rest_export) \(see config schema type views\.display\..*",
      ],
      'display.*.display_options.cache' => [
        // @see \Drupal\views\Plugin\views\cache\Tag
        // @see \Drupal\views\Plugin\views\cache\Time
        "'options' is a conditionally required key because display\..*\.cache\.type is .* \(see config schema type views\.cache\..*",
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
        "'entity_field' is a required key.",
        "'entity_type' is a required key.",
        "'group_type' is a required key.",
        "'admin_label' is a required key.",
        "'expose' is a required key.",
        "'exposed' is a required key.",
        "'relationship' is a required key.",
        "'order' is a required key.",
        "'plugin_id' is a required key.",
        // @see \Drupal\views\Plugin\views\sort\Date
        "'granularity' is a conditionally required key because display\..*\.sorts\..*\.plugin_id is date \(see config schema type views\.sort\.date.*",
      ],
      'display.*.display_options.header.*' => [
        "'label' is a required key.",
        "'admin_label' is a required key.",
        "'entity_field' is a required key.",
        "'entity_type' is a required key.",
        "'group_type' is a required key.",
        "'relationship' is a required key.",
        "'empty' is a required key.",
        // @see \Drupal\views\Plugin\views\area\TokenizeAreaPluginBase
        // @see \Drupal\views\Plugin\views\area\Entity (inherited)
        // @see \Drupal\views\Plugin\views\area\Text (inherited)
        // @see \Drupal\views\Plugin\views\area\TextCustom (inherited)
        "'tokenize' is a conditionally required key because display\..*\.header\..*\.plugin_id is (entity|text|text_custom) \(see config schema type views\.area\..*",
        // @see \Drupal\views\Plugin\views\area\Text
        // @see \Drupal\views\Plugin\views\area\TextCustom
        "'content' is a conditionally required key because display\..*\.header\..*\.plugin_id is (text|text_custom) \(see config schema type views\.area\.text.*",
        // @see \Drupal\views\Plugin\views\area\Entity
        "'bypass_access' is a conditionally required key because display\..*\.header\..*\.plugin_id is entity \(see config schema type views\.area\.entity.*",
        // @see \Drupal\views_test_data\Plugin\views\area\TestExample
        "'custom_access' is a conditionally required key because display\..*\.header\..*\.plugin_id is test_example \(see config schema type views\.area\.test_example.*",
        "'string' is a conditionally required key because display\..*\.header\..*\.plugin_id is test_example \(see config schema type views\.area\.test_example.*",
      ],
      'display.*.display_options.cache.options' => [
        "'results_lifespan_custom' is a required key.",
        "'output_lifespan_custom' is a required key.",
      ],
      'display.*.display_options.pager.options' => [
        "'offset' is a required key.",
        "'items_per_page' is a required key.",
        // @see \Drupal\views\Plugin\views\pager\Full
        "'expose' is a conditionally required key because display\..*\.pager\.type is full \(see config schema type views\.pager\.full.*",
        "'id' is a conditionally required key because display\..*\.pager\.type is full \(see config schema type views\.pager\.full.*",
        "'quantity' is a conditionally required key because display\..*\.pager\.type is full \(see config schema type views\.pager\.full.*",
        "'tags' is a conditionally required key because display\..*\.pager\.type is full \(see config schema type views\.pager\.full.*",
        "'total_pages' is a conditionally required key because display\..*\.pager\.type is full \(see config schema type views\.pager\.full.*",
        // @see \Drupal\views\Plugin\views\pager\Mini
        "'expose' is a conditionally required key because display\..*\.pager\.type is mini \(see config schema type views\.pager\.mini.*",
        "'id' is a conditionally required key because display\..*\.pager\.type is mini \(see config schema type views\.pager\.mini.*",
        "'tags' is a conditionally required key because display\..*\.pager\.type is mini \(see config schema type views\.pager\.mini.*",
        "'total_pages' is a conditionally required key because display\..*\.pager\.type is mini \(see config schema type views\.pager\.mini.*",
      ],
      'display.*.display_options.query.options' => [
        // @see \Drupal\views\Plugin\views\query\Sql
        "'disable_sql_rewrite' is a conditionally required key because display\..*\.query\.type is views_query \(see config schema type views\.query\.views_query.*",
        "'distinct' is a conditionally required key because display\..*\.query\.type is views_query \(see config schema type views\.query\.views_query.*",
        "'query_comment' is a conditionally required key because display\..*\.query\.type is views_query \(see config schema type views\.query\.views_query.*",
        "'query_tags' is a conditionally required key because display\..*\.query\.type is views_query \(see config schema type views\.query\.views_query.*",
        "'replica' is a conditionally required key because display\..*\.query\.type is views_query \(see config schema type views\.query\.views_query.*",
      ],
      'display.default.display_options.empty.*' => [
        "'entity_field' is a required key.",
        "'entity_type' is a required key.",
        "'label' is a required key.",
        "'admin_label' is a required key.",
        "'group_type' is a required key.",
        "'relationship' is a required key.",
        "'title' is a required key.",
        "'empty' is a required key.",
        // @see \Drupal\views\Plugin\views\area\Text
        // @see \Drupal\views\Plugin\views\area\TextCustom
        "'content' is a conditionally required key because display\..*\.empty\..*\.plugin_id is (text|text_custom) \(see config schema type views\.area\.text.*",
        // @see \Drupal\views\Plugin\views\area\TokenizeAreaPluginBase
        // @see \Drupal\views\Plugin\views\area\Entity (inherited)
        // @see \Drupal\views\Plugin\views\area\Text (inherited)
        // @see \Drupal\views\Plugin\views\area\TextCustom (inherited)
        "'tokenize' is a conditionally required key because display\..*\.empty\..*\.plugin_id is (entity|text|text_custom) \(see config schema type views\.area\..*",
        // @see \Drupal\views\Plugin\views\area\Entity
        "'bypass_access' is a conditionally required key because display\..*\.empty\..*\.plugin_id is entity \(see config schema type views\.area\.entity.*",
        "'view_mode' is a conditionally required key because display\..*\.empty\..*\.plugin_id is entity \(see config schema type views\.area\.entity.*",
        // @see \Drupal\views_test_data\Plugin\views\area\TestExample
        "'custom_access' is a conditionally required key because display\..*\.empty\..*\.plugin_id is test_example \(see config schema type views\.area\.test_example.*",
        "'string' is a conditionally required key because display\..*\.empty\..*\.plugin_id is test_example \(see config schema type views\.area\.test_example.*",
      ],
      'display.*.display_options.arguments.*' => [
        "'plugin_id' is a required key.",
        "'default_action' is a required key.",
        "'exception' is a required key.",
        "'title' is a required key.",
        "'title_enable' is a required key.",
        "'default_argument_type' is a required key.",
        "'summary' is a required key.",
        "'summary_options' is a required key.",
        "'specify_validation' is a required key.",
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
        // @see \Drupal\views\Plugin\views\argument\NullArgument
        "'must_not_be' is a conditionally required key because display\..*\.arguments\..*\.plugin_id is null \(see config schema type views\.argument\.null.*",
        // @see \Drupal\views\Plugin\views\argument\NumericArgument
        // @see \Drupal\file\Plugin\views\argument\Fid (inherited)
        // @see \Drupal\options\Plugin\views\argument\NumberListField (inherited)
        // @see \Drupal\node\Plugin\views\argument\Nid (inherited)
        // @see \Drupal\node\Plugin\views\argument\UidRevision (inherited)
        // @see \Drupal\node\Plugin\views\argument\Vid (inherited)
        // @see \Drupal\taxonomy\Plugin\views\argument\Taxonomy (inherited)
        // @see \Drupal\taxonomy\Plugin\views\argument\VocabularyVid (inherited)
        // @see \Drupal\user\Plugin\views\argument\Uid (inherited)
        "'not' is a conditionally required key because display\..*\.arguments\..*\.plugin_id is (numeric|file_fid|number_list_field|node_nid|node_uid_revision|node_vid|taxonomy|vocabulary_vid|user_uid) \(see config schema type views\.argument\..*",
        // @see \Drupal\views\Plugin\views\argument\Date
        // @see \Drupal\views\Plugin\views\argument\DayDate (inherited)
        // @see \Drupal\views\Plugin\views\argument\YearMonthDate (inherited)
        // @see \Drupal\views\Plugin\views\argument\FullDate (inherited)
        // @see \Drupal\views\Plugin\views\argument\MonthDate (inherited)
        // @see \Drupal\views\Plugin\views\argument\WeekDate (inherited)
        // @see \Drupal\views\Plugin\views\argument\YearDate (inherited)
        // @see \Drupal\views\Plugin\views\argument\YearMonthDate (inherited)
        // @see \Drupal\datetime\Plugin\views\argument\Date (inherited)
        // @see \Drupal\datetime\Plugin\views\argument\DayDate (inherited)
        // @see \Drupal\datetime\Plugin\views\argument\FullDate (inherited)
        // @see \Drupal\datetime\Plugin\views\argument\MonthDate (inherited)
        // @see \Drupal\datetime\Plugin\views\argument\WeekDate (inherited)
        // @see \Drupal\datetime\Plugin\views\argument\YearDate (inherited)
        // @see \Drupal\datetime\Plugin\views\argument\YearMonthDate (inherited)
        "'date' is a conditionally required key because display\..*\.arguments\..*\.plugin_id is (date|date_day|date_fulldate|date_month|date_week|date_year|date_year_month|date_year_month|datetime|datetime_day|datetime_full_date|datetime_month|datetime_week|datetime_year_month|datetime_year) \(see config schema type views\.argument\.date.*",
        "'node_created' is a conditionally required key because display\..*\.arguments\..*\.plugin_id is (date|date_day|date_fulldate|date_month|date_week|date_year|date_year_month|date_year_month|datetime|datetime_day|datetime_full_date|datetime_month|datetime_week|datetime_year_month|datetime_year) \(see config schema type views\.argument\.date.*",
        "'node_changed' is a conditionally required key because display\..*\.arguments\..*\.plugin_id is (date|date_day|date_fulldate|date_month|date_week|date_year|date_year_month|date_year_month|datetime|datetime_day|datetime_full_date|datetime_month|datetime_week|datetime_year_month|datetime_year) \(see config schema type views\.argument\.date.*",
        // @see \Drupal\views\Plugin\views\argument\DayDate
        // @see \Drupal\datetime\Plugin\views\argument\DayDate (inherited)
        "'day' is a conditionally required key because display\..*\.arguments\..*\.plugin_id is (date_day|datetime_day) \(see config schema type views\.argument\.date.*",
        // @see \Drupal\views\Plugin\views\argument\MonthDate
        // @see \Drupal\datetime\Plugin\views\argument\MonthDate (inherited)
        "'month' is a conditionally required key because display\..*\.arguments\..*\.plugin_id is (date_month|datetime_month) \(see config schema type views\.argument\.date.*",
        // @see \Drupal\views\Plugin\views\argument\YearMonthDate
        // @see \Drupal\datetime\Plugin\views\argument\YearMonthDate (inherited)
        // @see \Drupal\views\Plugin\views\argument\FullDate
        // @see \Drupal\datetime\Plugin\views\argument\FullDate (inherited)
        "'created' is a conditionally required key because display\..*\.arguments\..*\.plugin_id is (date_year_month|datetime_year_month|date_fulldate|datetime_fulldate) \(see config schema type views\.argument\.date.*",
        // @see \Drupal\views\Plugin\views\argument\StringArgument
        // @see \Drupal\node\Plugin\views\argument\Type (inherited)
        // @see \Drupal\options\Plugin\views\argument\StringListField (inherited)
        "'add_table' is a conditionally required key because display\..*\.arguments\..*\.plugin_id is (string|node_type|string_list_field) \(see config schema type views\.argument\..*",
        "'require_value' is a conditionally required key because display\..*\.arguments\..*\.plugin_id is (string|node_type|string_list_field) \(see config schema type views\.argument\..*",
      ],
      'display.*.display_options.arguments.*.summary' => [
        "'sort_order' is a required key.",
        "'number_of_records' is a required key.",
      ],
      'display.*.display_options.arguments.*.validate' => [
        "'fail' is a required key.",
      ],
      'display.*.display_options.arguments.*.validate_options' => [
        // @see \Drupal\views\Plugin\views\argument_validator\Entity
        // @see \Drupal\views\Plugin\Derivative\ViewsEntityArgumentValidator
        // @see \Drupal\taxonomy\Plugin\views\argument_validator\TermName (inherited)
        // @see \Drupal\user\Plugin\views\argument_validator\UserName (inherited)
        "'bundles' is a conditionally required key because display\..*\.arguments\..*\.validate\.type is (entity:.*|taxonomy_term_name|user_name) \(see config schema type views\.argument_validator\..*",
        "'multiple' is a conditionally required key because display\..*\.arguments\..*\.validate\.type is (entity:.*|taxonomy_term_name|user_name) \(see config schema type views\.argument_validator\..*",
        // @see \Drupal\taxonomy\Plugin\views\argument_validator\TermName
        "'vids' is a conditionally required key because display\..*\.arguments\..*\.validate\.type is taxonomy_term_name \(see config schema type views\.argument_validator\.taxonomy_term_name.*",
      ],
      'display.*.display_options.arguments.*.exception' => [
        "'title' is a required key.",
        "'value' is a required key.",
      ],
      'display.*.display_options.arguments.*.summary_options' => [
        "'grouping' is a required key.",
        "'row_class' is a required key.",
        "'default_row_class' is a required key.",
        "'uses_fields' is a required key.",
        // @see \Drupal\views\Plugin\views\style\DefaultSummary
        // @see \Drupal\views\Plugin\views\style\UnformattedSummary (inherited)
        "'base_path' is a conditionally required key because display\..*\.arguments\..*\.summary\.format is (default_summary|unformatted_summary) \(see config schema type views\.style\..*",
        "'count' is a conditionally required key because display\..*\.arguments\..*\.summary\.format is (default_summary|unformatted_summary) \(see config schema type views\.style\..*",
        "'items_per_page' is a conditionally required key because display\..*\.arguments\..*\.summary\.format is (default_summary|unformatted_summary) \(see config schema type views\.style\..*",
        "'override' is a conditionally required key because display\..*\.arguments\..*\.summary\.format is (default_summary|unformatted_summary) \(see config schema type views\.style\..*",
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
        "'entity_field' is a required key.",
        "'entity_type' is a required key.",
        "'label' is a required key.",
        "'admin_label' is a required key.",
        "'relationship' is a required key.",
        "'group_type' is a required key.",
        "'empty' is a required key.",
        // @see \Drupal\views\Plugin\views\area\TokenizeAreaPluginBase
        // @see \Drupal\views\Plugin\views\area\Entity (inherited)
        // @see \Drupal\views\Plugin\views\area\Text (inherited)
        // @see \Drupal\views\Plugin\views\area\TextCustom (inherited)
        "'tokenize' is a conditionally required key because display\..*\.footer\..*\.plugin_id is (entity|text|text_custom) \(see config schema type views\.area\..*",
        // @see \Drupal\views\Plugin\views\area\Text
        // @see \Drupal\views\Plugin\views\area\TextCustom
        "'content' is a conditionally required key because display\..*\.footer\..*\.plugin_id is (text|text_custom) \(see config schema type views\.area\.text.*",
        // @see \Drupal\views\Plugin\views\area\Entity
        "'bypass_access' is a conditionally required key because display\..*\.footer\..*\.plugin_id is entity \(see config schema type views\.area\..*",
        // @see \Drupal\views_test_data\Plugin\views\area\TestExample
        "'custom_access' is a conditionally required key because display\..*\.footer\..*\.plugin_id is test_example \(see config schema type views\.area\.test_example.*",
        "'string' is a conditionally required key because display\..*\.footer\..*\.plugin_id is test_example \(see config schema type views\.area\.test_example.*",
      ],
      'display.*.display_options.fields.*' => [
        "'field' is a required key.",
        "'table' is a required key.",
        "'id' is a required key.",
        "'label' is a required key.",
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
        "'destination' is a required key.",
        "'path' is a required key.",
        "'external' is a required key.",
        "'entity_type' is a required key.",
        "'entity_field' is a required key.",
        "'destination' is a required key.",
        // 🐛 This appears dead code: `link_to_user` only exists in tests, no code uses it!
        "'link_to_user' is a conditionally required key because display\..*\.fields\..*\.plugin_id is user \(see config schema type views\.field\.user.*",
        // @see \Drupal\comment\Plugin\views\field\NodeNewComments
        "'format_plural' is a conditionally required key because display\..*\.fields\..*\.plugin_id is node_new_comments \(see config schema type views\.field\.node_new_comments.*",
        "'format_plural_string' is a conditionally required key because display\..*\.fields\..*\.plugin_id is node_new_comments \(see config schema type views\.field\.node_new_comments.*",
        "'separator' is a conditionally required key because display\..*\.fields\..*\.plugin_id is node_new_comments \(see config schema type views\.field\.node_new_comments.*",
        // @see \Drupal\dblog\Plugin\views\field\DblogMessage
        "'replace_variables' is a conditionally required key because display\..*\.fields\..*\.plugin_id is dblog_message \(see config schema type views\.field\.dblog_message.*",
        // @see \Drupal\node\Plugin\views\field\Node
        "'link_to_node' is a conditionally required key because display\..*\.fields\..*\.plugin_id is node \(see config schema type views\.field\.node.*",
        // @see \Drupal\views\Plugin\views\field\BulkForm
        // @see \Drupal\comment\Plugin\views\field\CommentBulkForm (inherited)
        // @see \Drupal\node\Plugin\views\field\NodeBulkForm (inherited)
        // @see \Drupal\user\Plugin\views\field\UserBulkForm (inherited)
        "'action_title' is a conditionally required key because display\..*\.fields\..*\.plugin_id is (bulk_form|comment_bulk_form|node_bulk_form|user_bulk_form) \(see config schema type views\.field\..*",
        "'include_exclude' is a conditionally required key because display\..*\.fields\..*\.plugin_id is (bulk_form|comment_bulk_form|node_bulk_form|user_bulk_form) \(see config schema type views\.field\..*",
        "'selected_actions' is a conditionally required key because display\..*\.fields\..*\.plugin_id is (bulk_form|comment_bulk_form|node_bulk_form|user_bulk_form) \(see config schema type views\.field\..*",
        // @see \Drupal\views\Plugin\views\field\NumericField
        "'decimal' is a conditionally required key because display\..*\.fields\..*\.plugin_id is numeric \(see config schema type views\.field\.numeric.*",
        "'format_plural' is a conditionally required key because display\..*\.fields\..*\.plugin_id is numeric \(see config schema type views\.field\.numeric.*",
        "'format_plural_string' is a conditionally required key because display\..*\.fields\..*\.plugin_id is numeric \(see config schema type views\.field\.numeric.*",
        "'precision' is a conditionally required key because display\..*\.fields\..*\.plugin_id is numeric \(see config schema type views\.field\.numeric.*",
        "'prefix' is a conditionally required key because display\..*\.fields\..*\.plugin_id is numeric \(see config schema type views\.field\.numeric.*",
        "'separator' is a conditionally required key because display\..*\.fields\..*\.plugin_id is numeric \(see config schema type views\.field\.numeric.*",
        "'set_precision' is a conditionally required key because display\..*\.fields\..*\.plugin_id is numeric \(see config schema type views\.field\.numeric.*",
        "'suffix' is a conditionally required key because display\..*\.fields\..*\.plugin_id is numeric \(see config schema type views\.field\.numeric.*",
        // @see \Drupal\views\Plugin\views\field\EntityField
        // @see \Drupal\taxonomy\Plugin\views\field\TermName (inherited)
        "'click_sort_column' is a conditionally required key because display\..*\.fields\..*\.plugin_id is (field|term_name) \(see config schema type views\.field\..*",
        "'delta_first_last' is a conditionally required key because display\..*\.fields\..*\.plugin_id is (field|term_name) \(see config schema type views\.field\..*",
        "'delta_limit' is a conditionally required key because display\..*\.fields\..*\.plugin_id is (field|term_name) \(see config schema type views\.field\..*",
        "'delta_offset' is a conditionally required key because display\..*\.fields\..*\.plugin_id is (field|term_name) \(see config schema type views\.field\..*",
        "'delta_reversed' is a conditionally required key because display\..*\.fields\..*\.plugin_id is (field|term_name) \(see config schema type views\.field\..*",
        "'field_api_classes' is a conditionally required key because display\..*\.fields\..*\.plugin_id is (field|term_name) \(see config schema type views\.field\..*",
        "'group_column' is a conditionally required key because display\..*\.fields\..*\.plugin_id is (field|term_name) \(see config schema type views\.field\..*",
        "'group_columns' is a conditionally required key because display\..*\.fields\..*\.plugin_id is (field|term_name) \(see config schema type views\.field\..*",
        "'group_rows' is a conditionally required key because display\..*\.fields\..*\.plugin_id is (field|term_name) \(see config schema type views\.field\..*",
        "'multi_type' is a conditionally required key because display\..*\.fields\..*\.plugin_id is (field|term_name) \(see config schema type views\.field\..*",
        "'separator' is a conditionally required key because display\..*\.fields\..*\.plugin_id is (field|term_name) \(see config schema type views\.field\..*",
        "'settings' is a conditionally required key because display\..*\.fields\..*\.plugin_id is (field|term_name) \(see config schema type views\.field\..*",
        "'type' is a conditionally required key because display\..*\.fields\..*\.plugin_id is (field|term_name) \(see config schema type views\.field\..*",
        // @see \Drupal\views\Plugin\views\field\EntityLink
        // @see \Drupal\views\Plugin\views\field\EntityLinkDelete (inherited)
        // @see \Drupal\views\Plugin\views\field\EntityLinkEdit (inherited)
        "'absolute' is a conditionally required key because display\..*\.fields\..*\.plugin_id is (entity_link|entity_link_delete|entity_link_edit) \(see config schema type views\.field\.entity_link.*",
        "'output_url_as_text' is a conditionally required key because display\..*\.fields\..*\.plugin_id is (entity_link|entity_link_delete|entity_link_edit) \(see config schema type views\.field\.entity_link.*",
        "'text' is a conditionally required key because display\..*\.fields\..*\.plugin_id is (entity_link|entity_link_delete|entity_link_edit) \(see config schema type views\.field\.entity_link.*",
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
        "'link_to_file' is a required key.",
        // @see \Drupal\views\Plugin\views\field\EntityField
        // @see \Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter
        // @see \Drupal\media\Plugin\Field\FieldFormatter\MediaThumbnailFormatter (inherited)
        "'image_loading' is a conditionally required key because display\..*\.fields\..*\.type is (image|media_thumbnail) \(see config schema type field\.formatter\.settings\..*",
        // @see \Drupal\views\Plugin\views\field\EntityField
        // @see \Drupal\Core\Field\Plugin\Field\FieldFormatter\TimestampFormatter
        "'time_diff' is a conditionally required key because display\..*\.fields\..*\.type is timestamp \(see config schema type field\.formatter\.settings\.timestamp.*",
        "'tooltip' is a conditionally required key because display\..*\.fields\..*\.type is timestamp \(see config schema type field\.formatter\.settings\.timestamp.*",
        // @see \Drupal\views\Plugin\views\field\EntityField
        // @see \Drupal\Core\Field\Plugin\Field\FieldFormatter\StringFormatter
        // @see \Drupal\Core\Field\Plugin\Field\FieldFormatter\LanguageFormatter (inherited)
        // @see \Drupal\user\Plugin\Field\FieldFormatter\UserNameFormatter
        "'link_to_entity' is a conditionally required key because display\..*\.fields\..*\.type is (string|language|user_name) \(see config schema type field\.formatter\.settings\..*",
        // @see \Drupal\views\Plugin\views\field\EntityField
        // @see \Drupal\Core\Field\Plugin\Field\FieldFormatter\TimestampAgoFormatter
        "'future_format' is a conditionally required key because display\..*\.fields\..*\.type is timestamp_ago \(see config schema type field\.formatter\.settings\.timestamp_ago.*",
        "'granularity' is a conditionally required key because display\..*\.fields\..*\.type is timestamp_ago \(see config schema type field\.formatter\.settings\.timestamp_ago.*",
        "'past_format' is a conditionally required key because display\..*\.fields\..*\.type is timestamp_ago \(see config schema type field\.formatter\.settings\.timestamp_ago.*",
      ],
      'display.*.display_options.pager.options.expose' => [
        "'items_per_page_label' is a required key.",
        "'items_per_page_options' is a required key.",
        "'items_per_page_options_all' is a required key.",
        "'items_per_page_options_all_label' is a required key.",
        "'offset_label' is a required key.",
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
      'display.*.display_options.filter_groups' => [
        "'operator' is a required key.",
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
        // @see \Drupal\views\Plugin\views\filter\Date
        // @see \Drupal\datetime\Plugin\views\filter\Date (inherited)
        "'type' is a conditionally required key because display\..*\.filters\..*\.plugin_id is (date|datetime) \(see config schema type views\.filter\..*",
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
        // @see \Drupal\views\Plugin\views\filter\Date
        // @see \Drupal\datetime\Plugin\views\filter\Date (inherited)
        "'type' is a conditionally required key because display\..*\.filters\..*\.plugin_id is (date|datetime) \(see config schema type views\.filter_value\.date.*",
        // @see \Drupal\views\Plugin\views\filter\NumericFilter
        // @see \Drupal\views\Plugin\views\filter\Date (inherited)
        // @see \Drupal\views\Plugin\views\filter\GroupByNumeric (inherited)
        // @see \Drupal\comment\Plugin\views\filter\StatisticsLastUpdated (inherited)
        // @see \Drupal\datetime\Plugin\views\filter\Date (inherited)
        "'min' is a conditionally required key because display\..*\.filters\..*\.plugin_id is (numeric|date|groupby_numeric|comment_ces_last_updated|datetime) \(see config schema type views\.filter_value\..*",
        "'max' is a conditionally required key because display\..*\.filters\..*\.plugin_id is (numeric|date|groupby_numeric|comment_ces_last_updated|datetime) \(see config schema type views\.filter_value\..*",
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
        "'default_row_class' is a required key.",
        "'row_class' is a required key.",
        "'grouping' is a required key.",
        // @see \Drupal\views\Plugin\views\style\GridResponsive
        "'alignment' is a conditionally required key because display\..*\.style\.type is grid_responsive \(see config schema type views\.style\.grid_responsive.*",
        "'cell_min_width' is a conditionally required key because display\..*\.style\.type is grid_responsive \(see config schema type views\.style\.grid_responsive.*",
        "'columns' is a conditionally required key because display\..*\.style\.type is grid_responsive \(see config schema type views\.style\.grid_responsive.*",
        "'grid_gutter' is a conditionally required key because display\..*\.style\.type is grid_responsive \(see config schema type views\.style\.grid_responsive.*",
        // @see \Drupal\views\Plugin\views\style\Table
        "'caption' is a conditionally required key because display\..*\.style\.type is table \(see config schema type views\.style\.table.*",
        "'columns' is a conditionally required key because display\..*\.style\.type is table \(see config schema type views\.style\.table.*",
        "'default' is a conditionally required key because display\..*\.style\.type is table \(see config schema type views\.style\.table.*",
        "'description' is a conditionally required key because display\..*\.style\.type is table \(see config schema type views\.style\.table.*",
        "'empty_table' is a conditionally required key because display\..*\.style\.type is table \(see config schema type views\.style\.table.*",
        "'info' is a conditionally required key because display\..*\.style\.type is table \(see config schema type views\.style\.table.*",
        "'order' is a conditionally required key because display\..*\.style\.type is table \(see config schema type views\.style\.table.*",
        "'override' is a conditionally required key because display\..*\.style\.type is table \(see config schema type views\.style\.table.*",
        "'sticky' is a conditionally required key because display\..*\.style\.type is table \(see config schema type views\.style\.table.*",
        "'summary' is a conditionally required key because display\..*\.style\.type is table \(see config schema type views\.style\.table.*",
      ],
      'display.*.display_options.style.options.info.*' => [
        "'align' is a required key.",
        "'sortable' is a required key.",
        "'default_sort_order' is a required key.",
        "'empty_column' is a required key.",
        "'responsive' is a required key.",
        "'separator' is a required key.",
      ],
      'display.*.display_options.row' => [
        "'options' is a required key.",
      ],
      'display.*.display_options.row.options' => [
        "'relationship' is a required key.",
        // @see \Drupal\views\Plugin\views\row\Fields
        "'default_field_elements' is a conditionally required key because display\..*\.row\.type is fields \(see config schema type views\.row\.fields.*",
        "'hide_empty' is a conditionally required key because display\..*\.row\.type is fields \(see config schema type views\.row\.fields.*",
        "'inline' is a conditionally required key because display\..*\.row\.type is fields \(see config schema type views\.row\.fields.*",
        "'separator' is a conditionally required key because display\..*\.row\.type is fields \(see config schema type views\.row\.fields.*",
        // @see \Drupal\comment\Plugin\views\row\Rss
        "'view_mode' is a conditionally required key because display\..*\.row\.type is comment_rss \(see config schema type views\.row\.comment_rss.*",
      ],
      'display.*.display_options.menu' => [
        "'enabled' is a required key.",
        "'expanded' is a required key.",
        "'parent' is a required key.",
        "'description' is a required key.",
        "'context' is a required key.",
        "'type' is a required key.",
        "'title' is a required key.",
        "'weight' is a required key.",
        "'menu_name' is a required key.",
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
        "'max-age' is a required key.",
        "'tags' is a required key.",
      ],
    ],
    'workflows.workflow.*' => [
      'type_settings' => [
        "'default_moderation_state' is a conditionally required key because type is content_moderation \(see config schema type workflow\.type_settings\.content_moderation.*",
      ],
      'type_settings.states.*' => [
        "'published' is a required key.",
        "'default_revision' is a required key.",
        "'extra' is a required key.",
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
    // If config validation errors are encountered for a contrib module, avoid
    // failing the test (which would be too disruptive for the ecosystem), but
    // trigger a deprecation notice instead.
    if (!empty($validation_errors) && $this->isContribViolation()) {
      @trigger_error(sprintf("The '%s' configuration contains validation errors. Invalid config is deprecated in drupal:10.2.0 and will be required to be valid in drupal:11.0.0. The following validation errors were found:\n\t\t- %s",
        $config_name,
        implode("\n\t\t- ", $validation_errors)
      ), E_USER_DEPRECATED);
    }
    else {
      $errors = array_merge($errors, $validation_errors);
    }
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
   * Whether the current test is for a contrib module.
   *
   * @return bool
   */
  private function isContribViolation(): bool {
    $test_file_name = (new \ReflectionClass($this))->getFileName();
    $root = dirname(__DIR__, 6);
    return !str_starts_with($test_file_name, $root . DIRECTORY_SEPARATOR . 'core');
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
