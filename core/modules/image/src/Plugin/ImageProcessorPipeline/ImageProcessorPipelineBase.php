<?php

namespace Drupal\image\Plugin\ImageProcessorPipeline;

use Drupal\Core\Image\ImageInterface;
use Drupal\Core\KeyValueStore\MemoryStorage;
use Drupal\Core\Plugin\PluginBase;
use Drupal\image\ImageProcessorException;
use Drupal\image\ImageProcessorPipelineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * ImageProcessorPipeline base class.
 */
class ImageProcessorPipelineBase extends PluginBase implements ImageProcessorPipelineInterface {

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * A pool of variables required to derive images.
   *
   * @var \Drupal\Core\KeyValueStore\MemoryStorage
   */
  protected $variables;

  /**
   * The Image object being processed.
   *
   * @var \Drupal\Core\Image\ImageInterface
   */
  protected $image;

  /**
   * Constructs a ImageProcessorPipeline plugin.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   The event dispatcher.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, EventDispatcherInterface $dispatcher) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->variables = new MemoryStorage('image_pipeline_variables');
    $this->eventDispatcher = $dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('event_dispatcher')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setVariable(string $variable, $value): ImageProcessorPipelineInterface {
    $this->variables->set($variable, $value);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getVariable(string $variable) {
    if (!$this->variables->has($variable)) {
      throw new ImageProcessorException("Variable {$variable} not set");
    }
    return $this->variables->get($variable);
  }

  /**
   * {@inheritdoc}
   */
  public function hasVariable(string $variable): bool {
    return $this->variables->has($variable);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteVariable(string $variable): ImageProcessorPipelineInterface {
    $this->variables->delete($variable);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setImage(ImageInterface $image): ImageProcessorPipelineInterface {
    $this->image = $image;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getImage(): ImageInterface {
    return $this->image;
  }

  /**
   * {@inheritdoc}
   */
  public function hasImage(): bool {
    return (bool) $this->image;
  }

  /**
   * {@inheritdoc}
   */
  public function dispatch(string $event, array $arguments = []): ImageProcessorPipelineInterface {
    try {
      $this->eventDispatcher->dispatch(new $event($this, $arguments));
    }
    catch (ImageProcessorException $e) {
      throw new ImageProcessorException("Failure processing '$event': " . $e->getMessage(), $e->getCode(), $e);
    }
    return $this;
  }

}
