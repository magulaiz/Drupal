<?php

namespace Drupal\filter\Plugin\migrate\process;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\filter\FilterType;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Plugin\migrate\process\StaticMap;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

// cspell:ignore abbrfilter adsense autofloat biblio cincopa codefilter
// cspell:ignore commonmark deepzoom emogrifier emptyparagraphkiller forena
// cspell:ignore gotwo htmlpurifier htmltidy intlinks lazyloader linktitle
// cspell:ignore multicolumn multilink mytube openlayers opengraph sanitizable
// cspell:ignore shortcode spamspan typogrify wordfilter xbbcode

/**
 * @MigrateProcessPlugin(
 *   id = "filter_id"
 * )
 */
class FilterID extends StaticMap implements ContainerFactoryPluginInterface {

  /**
   * The filter plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface|\Drupal\Component\Plugin\FallbackPluginManagerInterface
   */
  protected $filterManager;

  /**
   * FilterID constructor.
   *
   * @param array $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $filter_manager
   *   The filter plugin manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translator
   *   (optional) The string translation service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PluginManagerInterface $filter_manager, TranslationInterface $translator = NULL) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->filterManager = $filter_manager;
    $this->stringTranslation = $translator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.filter'),
      $container->get('string_translation')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $plugin_id = parent::transform($value, $migrate_executable, $row, $destination_property);

    // If the static map is bypassed on failure, the returned plugin ID will be
    // an array if $value was. Plugin IDs cannot be arrays, so flatten it before
    // passing it into the filter manager.
    if (is_array($plugin_id)) {
      $plugin_id = implode(':', $plugin_id);
    }

    if ($this->filterManager->hasDefinition($plugin_id)) {
      return $plugin_id;
    }
    else {
      if (in_array(static::getSourceFilterType($value), [FilterType::TransformReversible, FilterType::TransformIrreversible], TRUE)) {
        $message = sprintf('Filter %s could not be mapped to an existing filter plugin; omitted since it is a transformation-only filter. Install and configure a successor after the migration.', $plugin_id);
        $migrate_executable->saveMessage($message, MigrationInterface::MESSAGE_INFORMATIONAL);
        $this->stopPipeline();
        return NULL;
      }
      $fallback = $this->filterManager->getFallbackPluginId($plugin_id);

      // @see \Drupal\filter\Plugin\migrate\process\FilterSettings::transform()
      $message = sprintf('Filter %s could not be mapped to an existing filter plugin; defaulting to %s and dropping all settings. Either redo the migration with the module installed that provides an equivalent filter, or modify the text format after the migration to remove this filter if it is no longer necessary.', $plugin_id, $fallback);
      $migrate_executable->saveMessage($message, MigrationInterface::MESSAGE_WARNING);

      return $fallback;
    }
  }

  /**
   * Gets the Drupal 8 filter type for a Drupal 7 filter.
   *
   * @param string $filter_id
   *   A Drupal 7 filter ID.
   *
   * @return \Drupal\filter\FilterType|null
   *   The filter type.
   *
   * @see \Drupal\filter\Plugin\FilterInterface::getType()
   */
  protected static function getSourceFilterType($filter_id) {
    return match ($filter_id) {
      'filter_html' => FilterType::HtmlRestrictor,
      'filter_url' => FilterType::MarkupLanguage,
      'filter_autop' => FilterType::MarkupLanguage,
      'filter_htmlcorrector' => FilterType::TransformIrreversible,
      'filter_html_escape' => FilterType::HtmlRestrictor,
      'php_code' => FilterType::MarkupLanguage,
      'abbrfilter' => FilterType::TransformIrreversible,
      'ace_editor' => FilterType::TransformIrreversible,
      'adsense' => FilterType::TransformIrreversible,
      'api_filter' => FilterType::TransformIrreversible,
      'api_tokens' => FilterType::TransformIrreversible,
      'filter_autofloat' => FilterType::TransformIrreversible,
      'bbcode' => FilterType::MarkupLanguage,
      'biblio_filter_reference', 'biblio_filter_inline_reference' => FilterType::TransformIrreversible,
      'caption' => FilterType::TransformReversible,
      'caption_filter' => FilterType::TransformIrreversible,
      'filter_cincopa' => FilterType::TransformIrreversible,
      'ckeditor_blocks' => FilterType::TransformIrreversible,
      'ckeditor_filter' => FilterType::HtmlRestrictor,
      'ckeditor_link_filter' => FilterType::TransformIrreversible,
      'ckeditor_swf_filter' => FilterType::TransformIrreversible,
      'codefilter' => FilterType::TransformIrreversible,
      'collapse_text_filter' => FilterType::TransformReversible,
      'columns_filter' => FilterType::TransformIrreversible,
      'commonmark' => FilterType::MarkupLanguage,
      'filter_hashtags' => FilterType::TransformIrreversible,
      'deepzoom' => FilterType::TransformIrreversible,
      'editor_align', 'editor_caption' => FilterType::TransformReversible,
      'elf' => FilterType::TransformReversible,
      'filter_emogrifier' => FilterType::TransformIrreversible,
      'emptyparagraphkiller' => FilterType::HtmlRestrictor,
      'entity_embed' => FilterType::TransformIrreversible,
      'filter_align' => FilterType::TransformReversible,
      'ext_link_page' => FilterType::TransformIrreversible,
      'filter_html_image_secure' => FilterType::HtmlRestrictor,
      'filter_transliteration' => FilterType::TransformIrreversible,
      'flickr_filter' => FilterType::TransformIrreversible,
      'float_filter' => FilterType::TransformReversible,
      'filter_footnotes' => FilterType::TransformIrreversible,
      'forena_report' => FilterType::TransformIrreversible,
      'filter_g2' => FilterType::TransformIrreversible,
      'geo_filter_filter' => FilterType::TransformIrreversible,
      'filter_google_analytics_counter' => FilterType::TransformIrreversible,
      'filter_google_analytics_referrer' => FilterType::TransformIrreversible,
      'gotwo_link' => FilterType::TransformIrreversible,
      'h5p_content' => FilterType::TransformIrreversible,
      'highlight_js' => FilterType::TransformIrreversible,
      'htmLawed' => FilterType::HtmlRestrictor,
      'htmlpurifier_basic', 'htmlpurifier_advanced' => FilterType::HtmlRestrictor,
      'htmltidy' => FilterType::HtmlRestrictor,
      'icon_filter' => FilterType::TransformIrreversible,
      'iframe' => FilterType::TransformIrreversible,
      'image_resize_filter' => FilterType::TransformReversible,
      'insert_view' => FilterType::TransformIrreversible,
      'intlinks title', 'intlinks hide bad' => FilterType::TransformIrreversible,
      'accordion', 'dialog', 'tabs' => FilterType::MarkupLanguage,
      'language_sections' => FilterType::MarkupLanguage,
      'lazy_filter' => FilterType::TransformIrreversible,
      'lazyloader_filter' => FilterType::TransformIrreversible,
      'filter_link_node' => FilterType::TransformIrreversible,
      'linktitle' => FilterType::TransformIrreversible,
      'filter_markdown' => FilterType::MarkupLanguage,
      'media_filter', 'media_filter_paragraph_fix' => FilterType::TransformIrreversible,
      'filter_mentions' => FilterType::TransformIrreversible,
      'menu_filter' => FilterType::TransformIrreversible,
      'mobile_codes' => FilterType::TransformIrreversible,
      'multicolumn' => FilterType::TransformIrreversible,
      'multilink_filter' => FilterType::TransformIrreversible,
      'mytube' => FilterType::TransformIrreversible,
      'node_embed' => FilterType::TransformIrreversible,
      'node_field_embed' => FilterType::TransformIrreversible,
      'external_links' => FilterType::TransformIrreversible,
      'noreferrer' => FilterType::TransformIrreversible,
      'oembed', 'oembed_legacy' => FilterType::TransformIrreversible,
      'office_html_strip' => FilterType::HtmlRestrictor,
      'office_html_convert' => FilterType::TransformIrreversible,
      'openlayers' => FilterType::TransformIrreversible,
      'opengraph_filter' => FilterType::TransformReversible,
      'pathologic' => FilterType::TransformIrreversible,
      'popup_tags' => FilterType::TransformIrreversible,
      'prettify' => FilterType::TransformIrreversible,
      'rel_to_abs' => FilterType::TransformReversible,
      'rollover_filter' => FilterType::TransformIrreversible,
      'sanitizable' => FilterType::TransformIrreversible,
      'smart_paging_filter', 'smart_paging_filter_autop' => FilterType::TransformIrreversible,
      'spamspan' => FilterType::TransformIrreversible,
      'mee_scald_widgets' => FilterType::TransformReversible,
      'script_filter' => FilterType::TransformReversible,
      'shortcode' => FilterType::MarkupLanguage,
      'shortcode_text_corrector' => FilterType::TransformIrreversible,
      'smiley' => FilterType::TransformReversible,
      'filter_svg_embed' => FilterType::TransformIrreversible,
      'spoiler' => FilterType::TransformIrreversible,
      'filter_toc' => FilterType::TransformIrreversible,
      'filter_tables' => FilterType::TransformIrreversible,
      'target_filter_url' => FilterType::TransformIrreversible,
      'textile' => FilterType::MarkupLanguage,
      'theme_filter' => FilterType::TransformIrreversible,
      'filter_tokens' => FilterType::TransformIrreversible,
      'transliteration' => FilterType::TransformIrreversible,
      'typogrify' => FilterType::TransformIrreversible,
      'uuid_link_filter' => FilterType::TransformIrreversible,
      'wysiwyg', 'wysiwyg_template_cleanup' => FilterType::HtmlRestrictor,
      'word_link' => FilterType::TransformReversible,
      'wordfilter' => FilterType::TransformIrreversible,
      'xbbcode' => FilterType::MarkupLanguage,
      default => NULL,
    };

  }

}
