<?php

declare(strict_types=1);

namespace Drupal\ckeditor5\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\HTMLRestrictions;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\editor\EditorInterface;

/**
 * CKEditor 5 Wildcard HTML support plugin configuration.
 *
 * This plugin ensures that all wilcards supported by Drupal, are at least
 * retained. CKEditor 5 interprets wildcards from a "CKEditor 5 model element"
 * perspective, Drupal interprets wildcards from a "HTML element" perspective.
 * GHS is used to reconcile those two perspectives, to ensure all expected HTML
 * elements truly are supported.
 *
 * @see https://ckeditor.com/docs/ckeditor5/latest/api/html-support.html
 *
 * @internal
 *   Plugin classes are internal.
 */
class WildcardHtmlSupport extends CKEditor5PluginDefault {

  /**
   * {@inheritdoc}
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor, HTMLRestrictions $allowed_elements = NULL): array {
    // This plugin is an edge case because its configuration is based on the
    // combined `elements` configuration of all other active plugins. This
    // information is provided via a third argument. This third argument
    // intentionally deviates from the definition in
    // CKEditor5PluginManagerInterface.
    // @see \Drupal\ckeditor5\Plugin\CKEditor5PluginManager::getProvidedElements()
    if ($allowed_elements === NULL) {
      throw new \LogicException();
    }

    // Compute the net new elements that the wildcard tags resolve into.
    $concrete_allowed_elements = $allowed_elements->getConcreteSubset();
    $net_new_elements = $allowed_elements->diff($concrete_allowed_elements);

    return [
      'htmlSupport' => [
        'allow' => $net_new_elements->toGeneralHtmlSupportConfig(),
      ],
    ];
  }

}
