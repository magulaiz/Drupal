<?php

declare(strict_types=1);

namespace Drupal\ckeditor5\Plugin\CKEditor4To5Upgrade;

use Drupal\ckeditor5\HTMLRestrictions;
use Drupal\ckeditor5\Plugin\CKEditor4To5UpgradePluginInterface;
use Drupal\ckeditor5\Plugin\CKEditor5Plugin\CodeBlock;
use Drupal\Core\Plugin\PluginBase;
use Drupal\filter\FilterFormatInterface;

/**
 * Provides the CKEditor 4 to 5 upgrade path for contrib plugins now in core.
 *
 * @CKEditor4To5Upgrade(
 *   id = "contrib",
 *   cke4_buttons = {
 *     "Code"
 *   },
 *   cke4_plugin_settings = {
 *   },
 *   cke5_plugin_elements_subset_configuration = {
 *    "ckeditor5_codeBlock",
 *   }
 * )
 *
 * @internal
 *   Plugin classes are internal.
 */
class Contrib extends PluginBase implements CKEditor4To5UpgradePluginInterface {

  /**
   * {@inheritdoc}
   */
  public function mapCKEditor4ToolbarButtonToCKEditor5ToolbarItem(string $cke4_button, HTMLRestrictions $text_format_html_restrictions): ?array {
    switch ($cke4_button) {
      // @see https://www.drupal.org/project/codetag
      case 'Code':
        return ['code'];

      default:
        throw new \OutOfBoundsException();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function mapCKEditor4SettingsToCKEditor5Configuration(string $cke4_plugin_id, array $cke4_plugin_settings): ?array {
    throw new \OutOfBoundsException();
  }

  /**
   * {@inheritdoc}
   */
  public function computeCKEditor5PluginSubsetConfiguration(string $cke5_plugin_id, FilterFormatInterface $text_format): ?array {
    switch ($cke5_plugin_id) {
      case 'ckeditor5_codeBlock':
        $restrictions = $text_format->getHtmlRestrictions();
        if ($restrictions === FALSE) {
          // The default is to allow all languages if there are no restrictions.
          // @see \Drupal\ckeditor5\Plugin\CKEditor5Plugin\CodeBlock::DEFAULT_CONFIGURATION
          return NULL;
        }

        $configuration = CodeBlock::DEFAULT_CONFIGURATION;
        $classes = $restrictions['allowed']['code']['class'] ?? NULL;
        if (is_array($classes)) {
          // Remove languages that don't have a class.
          $configuration['enabled_languages'] = array_filter(
            $configuration['enabled_languages'],
            function ($enabled_language) use ($classes) {
              return in_array('language-' . $enabled_language, $classes, TRUE);
            }
          );
        }
        return $configuration;

      default:
        throw new \OutOfBoundsException();
    }
  }

}
