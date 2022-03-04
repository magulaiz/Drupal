<?php

declare(strict_types=1);

namespace Drupal\ckeditor5\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\HTMLRestrictions;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\editor\EditorInterface;

/**
 * CKEditor 5 Wildcard HTML support plugin configuration.
 *
 * @internal
 *   Plugin classes are internal.
 */
class WildcardHtmlSupport extends CKEditor5PluginDefault {

  /**
   * {@inheritdoc}
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor, HTMLRestrictions $allowed_elements = NULL): array {
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
