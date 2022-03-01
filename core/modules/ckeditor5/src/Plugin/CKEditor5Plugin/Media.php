<?php

declare(strict_types = 1);

namespace Drupal\ckeditor5\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\Core\Url;
use Drupal\editor\EditorInterface;
use Drupal\media\Entity\MediaType;

/**
 * CKEditor 5 Media plugin.
 *
 * Provides drupal-media element and options provided by the CKEditor 5 build.
 *
 * @internal
 *   Plugin classes are internal.
 */
class Media extends CKEditor5PluginDefault {

  use DynamicPluginConfigWithCsrfTokenUrlTrait;

  /**
   * {@inheritdoc}
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array {
    $dynamic_plugin_config = $static_plugin_config;
    $dynamic_plugin_config['drupalMedia']['previewURL'] = Url::fromRoute('media.filter.preview')
      ->setRouteParameter('filter_format', $editor->getFilterFormat()->id())
      ->toString(TRUE)
      ->getGeneratedUrl();
    $media_embed_filter = $editor->getFilterFormat()->filters('media_embed');
    $this->entityDisplayRepository = \Drupal::service('entity_display.repository');

    $media_bundles = MediaType::loadMultiple();
    $bundles_per_view_mode = [];
    $all_view_modes = $this->entityDisplayRepository->getViewModeOptions('media');

    foreach (array_keys($media_bundles) as $bundle) {
      $allowed_view_modes = array_intersect_key($this->entityDisplayRepository->getViewModeOptionsByBundle('media', $bundle), $media_embed_filter->settings['allowed_view_modes']);

      $dynamic_plugin_config['drupalMedia']['viewModes'][$bundle] = $allowed_view_modes;

      foreach (array_keys($allowed_view_modes) as $view_mode) {
        // Get the bundles that have this view mode enabled.
        $bundles_per_view_mode[$view_mode][] = $bundle;
      }
    }

    $dynamic_plugin_config['drupalMedia']['debug'] = $bundles_per_view_mode;

    // Create view mode options.
    foreach (array_keys($all_view_modes) as $view_mode) {
      $specific_bundles = $bundles_per_view_mode[$view_mode];
      $dynamic_plugin_config['drupalElementStyles']['options']['viewMode'][] = [
        'name' => $view_mode,
        'title' => $view_mode . ' view mode',
        'attributeName' => 'data-view-mode',
        'attributeValue' => $view_mode,
        'modelElements' => ['drupalMedia'],
        'modelAttributes' => [
          'drupalMediaBundle' => $specific_bundles,
        ],
      ];
    }
    $items = [];

    foreach (array_keys($all_view_modes) as $view_mode) {
      $items[] = "drupalElementStyle:viewMode:$view_mode";
    }

    // Configure dropdown menu.
    $dynamic_plugin_config['drupalMedia']['toolbar'][] = [
      'name' => 'drupalMedia:viewMode',
      'display' => 'list',
      'defaultItem' => 'drupalElementStyle:viewMode:default',
      'defaultText' => 'Select view mode',
      'items' => $items,
    ];

    $dynamic_plugin_config['drupalMedia']['metadataUrl'] = self::getUrlWithReplacedCsrfTokenPlaceholder(
      Url::fromRoute('ckeditor5.media_entity_metadata')
        ->setRouteParameter('editor', $editor->id())
    );
    $dynamic_plugin_config['drupalMedia']['previewCsrfToken'] = \Drupal::csrfToken()->get('X-Drupal-MediaPreview-CSRF-Token');
    return $dynamic_plugin_config;
  }

}
