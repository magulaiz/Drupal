<?php

declare(strict_types=1);

namespace Drupal\ckeditor5\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\HTMLRestrictions;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\editor\EditorInterface;

/**
 * CKEditor 5 HTML Support plugin configuration.
 *
 * @internal
 *   Plugin classes are internal.
 */
class HtmlSupport extends CKEditor5PluginDefault {

  /**
   * {@inheritdoc}
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor, HTMLRestrictions $allowed_elements = NULL): array {
    // @see \Drupal\ckeditor5\Plugin\CKEditor5PluginManager::getProvidedElements()
    if ($allowed_elements === NULL) {
      throw new \LogicException();
    }

    // If the editor has no HTML restrictions, allow any kind of arbitrary HTML.
    // Otherwise, resolve any remaining wildcards based on Drupal's assumptions
    // on wildcards to ensure all HTML tags that Drupal thinks are supported are
    // truly supported by CKEditor 5.
    if ($editor->getFilterFormat()->getHtmlRestrictions() === FALSE) {
      return [
        'htmlSupport' => [
          'allow' => [
            [
              'name' => [
                'regexp' => [
                  'pattern' => '/.*/',
                ],
              ],
              'attributes' => TRUE,
              'classes' => TRUE,
              'styles' => TRUE,
            ],
          ],
        ],
      ];
    }

    // Get HTML restrictions that has a list of elements with all attributes
    // disallowed for all elements so that elements impacted by the union with
    // wildcard restrictions can be identified.
    $elements = new HTMLRestrictions(array_map(function () {
      return FALSE;
    }, $allowed_elements->getAllowedElements(FALSE)));
    $wildcards = HTMLRestrictions::getWildcardSubset($allowed_elements);
    // Resolve all wildcards to supported elements. Remove elements without
    // attributes.
    $additional_ghs_restrictions = new HTMLRestrictions(array_filter($elements->merge($wildcards)->getAllowedElements(), function ($element) {
      return !is_bool($element);
    }));

    return [
      'htmlSupport' => [
        'allow' => $additional_ghs_restrictions->toGeneralHtmlSupportConfig(),
      ],
    ];
  }

}
