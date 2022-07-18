<?php

declare(strict_types = 1);

namespace Drupal\editor\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\file\FileInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a filter to lazy load tracked images.
 *
 * @Filter(
 *   id = "editor_image_lazy_load",
 *   title = @Translation("Lazy load tracked images uploaded via a Text Editor"),
 *   description = @Translation("Instruct browsers to lazy load images, unless overridden by <code>&lt;img loading=&quot;eager&quot;&gt;</code>."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE,
 *   weight = 15
 * )
 */
final class EditorImageLazyLoad extends FilterBase implements ContainerFactoryPluginInterface {

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The image factory.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

  /**
   * Constructs a EditorImageLazyLoad object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Drupal\Core\Image\ImageFactory $image_factory
   *   The image factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityRepositoryInterface $entity_repository, ImageFactory $image_factory) {
    $this->entityRepository = $entity_repository;
    $this->imageFactory = $image_factory;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.repository'),
      $container->get('image.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode): FilterProcessResult {
    $result = new FilterProcessResult($text);

    // If there are no images, return early.
    if (stripos($text, '<img ') === FALSE && stripos($text, 'data-entity-type="file"') === FALSE) {
      return $result;
    }

    return $result->setProcessedText($this->transformImages($text));
  }

  /**
   * Transform markup of images to include loading="lazy".
   *
   * @param string $text
   *   The markup to transform.
   *
   * @return string
   *   The transformed text with loading attribute added.
   */
  private function transformImages(string $text): string {
    $dom = Html::load($text);
    $xpath = new \DOMXPath($dom);
    // Only set loading="lazy" if no existing loading attribute is specified.
    foreach ($xpath->query('//img[not(@loading) and @data-entity-type="file" and @data-entity-uuid]') as $element) {
      assert($element instanceof \DOMElement);
      $uuid = $element->getAttribute('data-entity-uuid');
      $file = $this->entityRepository->loadEntityByUuid('file', $uuid);
      if ($file instanceof FileInterface) {
        $image = $this->imageFactory->get($file->getFileUri());
        $width = $image->getWidth();
        $height = $image->getHeight();
        // Set dimensions to avoid content layout shift (CLS).
        // @see https://web.dev/cls/
        if ($width !== NULL && !$element->hasAttribute('width')) {
          $element->setAttribute('width', (string) $width);
        }
        if ($height !== NULL && !$element->hasAttribute('height')) {
          $element->setAttribute('height', (string) $height);
        }
        // If dimensions are specified, then set lazy loading.
        if ($element->hasAttribute('width') && $element->hasAttribute('height')) {
          $element->setAttribute('loading', 'lazy');
        }
      }
    }
    return Html::serialize($dom);
  }

}
