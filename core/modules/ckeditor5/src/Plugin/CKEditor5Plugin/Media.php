<?php

declare(strict_types = 1);

namespace Drupal\ckeditor5\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\editor\EditorInterface;
use Drupal\media\Entity\MediaType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefinition;

/**
 * CKEditor 5 Media plugin.
 *
 * Provides drupal-media element and options provided by the CKEditor 5 build.
 *
 * @internal
 *   Plugin classes are internal.
 */
class Media extends CKEditor5PluginDefault implements ContainerFactoryPluginInterface {

  use DynamicPluginConfigWithCsrfTokenUrlTrait;

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * Media constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param \Drupal\ckeditor5\Plugin\CKEditor5PluginDefinition $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   */
  public function __construct(array $configuration, string $plugin_id, CKEditor5PluginDefinition $plugin_definition, EntityDisplayRepositoryInterface $entity_display_repository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityDisplayRepository = $entity_display_repository;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_display.repository'));
  }

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

    $media_bundles = MediaType::loadMultiple();
    $bundles_per_view_mode = [];
    $all_view_modes = $this->entityDisplayRepository->getViewModeOptions('media');
    $allowed_view_modes = $media_embed_filter->settings['allowed_view_modes'];

    foreach (array_keys($media_bundles) as $bundle) {
      $allowed_view_modes_by_bundle = array_intersect_key($this->entityDisplayRepository->getViewModeOptionsByBundle('media', $bundle), $media_embed_filter->settings['allowed_view_modes']);

      $dynamic_plugin_config['drupalMedia']['viewModes'][$bundle] = $allowed_view_modes_by_bundle;

      foreach (array_keys($allowed_view_modes_by_bundle) as $view_mode) {
        // Get the bundles that have this view mode enabled.
        $bundles_per_view_mode[$view_mode][] = $bundle;
      }
    }

    // Create view mode options.
    foreach (array_keys($all_view_modes) as $view_mode) {
      if (array_key_exists($view_mode, $bundles_per_view_mode)) {
        $specific_bundles = $bundles_per_view_mode[$view_mode];
        if ($view_mode !== 'default') {
          $dynamic_plugin_config['drupalElementStyles']['viewMode'][] = [
            'name' => $view_mode,
            'title' => $all_view_modes[$view_mode],
            'attributeName' => 'data-view-mode',
            'attributeValue' => $view_mode,
            'modelElements' => ['drupalMedia'],
            'modelAttributes' => [
              'drupalMediaType' => $specific_bundles,
            ],
          ];
        }
      }
    }
    // Add default option no matter what so user always has the
    // ability to return to default view.
    $dynamic_plugin_config['drupalElementStyles']['viewMode'][] = [
      'isDefault' => TRUE,
      'name' => 'Default',
      'title' => 'Default',
      'attributeName' => 'data-view-mode',
      'attributeValue' => 'default',
      'modelElements' => ['drupalMedia'],
      'modelAttributes' => [
        'drupalMediaType' => array_keys($media_bundles),
      ],
    ];

    $items = [];

    foreach (array_keys($allowed_view_modes) as $view_mode) {
      $items[] = "drupalElementStyle:viewMode:$view_mode";
    }
    if (!empty($allowed_view_modes)) {
      // Configure dropdown menu.
      $dynamic_plugin_config['drupalMedia']['toolbar'][] = [
        'name' => 'drupalMedia:viewMode',
        'display' => 'list',
        'defaultItem' => 'drupalElementStyle:viewMode:default',
        'defaultText' => 'View mode',
        'items' => $items,
      ];
    }
    $dynamic_plugin_config['drupalMedia']['metadataUrl'] = self::getUrlWithReplacedCsrfTokenPlaceholder(
      Url::fromRoute('ckeditor5.media_entity_metadata')
        ->setRouteParameter('editor', $editor->id())
    );
    $dynamic_plugin_config['drupalMedia']['previewCsrfToken'] = \Drupal::csrfToken()->get('X-Drupal-MediaPreview-CSRF-Token');
    return $dynamic_plugin_config;
  }

}
