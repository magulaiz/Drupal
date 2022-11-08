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
 * Provides a filter to lazy load images.
 *
 * @Filter(
 *   id = "editor_image_lazy_load",
 *   title = @Translation("Lazy load images"),
 *   description = @Translation("Instruct browsers to lazy load images if width and height is specified or can be calculated from the source image (width and height attributes will be added in such a case). Can be overridden by <code>&lt;img loading=&quot;eager&quot;&gt;</code>."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE,
 *   weight = 15
 * )
 */
final class EditorImageLazyLoad extends FilterBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs a EditorImageLazyLoad object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   The entity repository.
   * @param \Drupal\Core\Image\ImageFactory $imageFactory
   *   The image factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, protected EntityRepositoryInterface $entityRepository, protected ImageFactory $imageFactory) {
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
   * Transforms markup of images to include loading="lazy" unless dimensionless
   * or overridden.
   *
   * @param string $text
   *   The markup to transform.
   *
   * @return string
   *   The transformed text with loading attribute added
   *   (and potentially width and height too).
   */
  private function transformImages(string $text): string {
    $dom = Html::load($text);
    $xpath = new \DOMXPath($dom);
    // Search for files to add dimensions. Only add lazy load to images with
    // dimensions to avoid Cumulative Layout Shift (CLS).
    // @see https://web.dev/cls/
    foreach ($xpath->query('//img[@data-entity-type="file" and @data-entity-uuid]') as $element) {
      assert($element instanceof \DOMElement);
      if ($element->hasAttribute('width') || $element->hasAttribute('height')) {
        continue;
      }
      $uuid = $element->getAttribute('data-entity-uuid');
      $file = $this->entityRepository->loadEntityByUuid('file', $uuid);
      if ($file instanceof FileInterface) {
        $image = $this->imageFactory->get($file->getFileUri());
        $width = $image->getWidth();
        $height = $image->getHeight();

        if ($width !== NULL) {
          $element->setAttribute('width', (string) $width);
        }
        if ($height !== NULL) {
          $element->setAttribute('height', (string) $height);
        }
      }
    }
    // If dimensions exist and loading isn't already set, then lazy load.
    foreach ($xpath->query('//img[not(@loading="eager") and @width and @height]') as $element) {
      assert($element instanceof \DOMElement);
      $element->setAttribute('loading', 'lazy');
    }
    return Html::serialize($dom);
  }

}
