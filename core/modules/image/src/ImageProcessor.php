<?php

namespace Drupal\image;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\image\Annotation\ImageProcessorPipeline;

/**
 * Service to manage ImageProcessorPipeline plugins.
 */
class ImageProcessor extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cacheDiscovery, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/ImageProcessorPipeline',
      $namespaces,
      $module_handler,
      ImageProcessorPipelineInterface::class,
      ImageProcessorPipeline::class
    );
    $this->alterInfo('image_processor_pipeline_plugin_info');
    $this->setCacheBackend($cacheDiscovery, 'image_processor_pipeline_plugins');
  }

}
