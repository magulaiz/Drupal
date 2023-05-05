<?php

namespace Drupal\Core\Asset;

use Drupal\Core\Cache\QueryStringInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\State\StateInterface;

/**
 * Renders CSS assets.
 */
class CssCollectionRenderer implements AssetCollectionRendererInterface {

  /**
   * The query string cache.
   *
   * @var \Drupal\Core\Cache\QueryStringInterface
   */
  protected QueryStringInterface $queryStringCache;

  /**
   * The file URL generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * Constructs a CssCollectionRenderer.
   *
   * @param \Drupal\Core\Cache\QueryStringInterface|\Drupal\Core\State\StateInterface $query_string_cache
   *   The query string cache.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $file_url_generator
   *   The file URL generator.
   */
  public function __construct(QueryStringInterface|StateInterface $query_string_cache, FileUrlGeneratorInterface $file_url_generator) {
    if ($query_string_cache instanceof StateInterface) {
      @trigger_error('Calling ' . __METHOD__ . '() with $query_string_cache argument as \Drupal\Core\State\StateInterface instead of \Drupal\Core\Cache\QueryStringInterface is deprecated in drupal:10.1.0 and will be required in drupal:11.0.0. See https://www.drupal.org/node/3358337', E_USER_DEPRECATED);
      $query_string_cache = \Drupal::service('cache.query_string');
    }
    $this->queryStringCache = $query_string_cache;
    $this->fileUrlGenerator = $file_url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public function render(array $css_assets) {
    $elements = [];

    // A dummy query-string is added to filenames, to gain control over
    // browser-caching. The string changes on every update or full cache
    // flush, forcing browsers to load a new copy of the files, as the
    // URL changed.
    $query_string = $this->queryStringCache->get();

    // Defaults for LINK and STYLE elements.
    $link_element_defaults = [
      '#type' => 'html_tag',
      '#tag' => 'link',
      '#attributes' => [
        'rel' => 'stylesheet',
      ],
    ];

    foreach ($css_assets as $css_asset) {
      $element = $link_element_defaults;
      $element['#attributes']['media'] = $css_asset['media'];

      switch ($css_asset['type']) {
        // For file items, output a LINK tag for file CSS assets.
        case 'file':
          $element['#attributes']['href'] = $this->fileUrlGenerator->generateString($css_asset['data']);
          // Only add the cache-busting query string if this isn't an aggregate
          // file.
          if (!isset($css_asset['preprocessed'])) {
            $query_string_separator = str_contains($css_asset['data'], '?') ? '&' : '?';
            $element['#attributes']['href'] .= $query_string_separator . $query_string;
          }
          break;

        case 'external':
          $element['#attributes']['href'] = $css_asset['data'];
          break;

        default:
          throw new \Exception('Invalid CSS asset type.');
      }

      // Merge any additional attributes.
      if (!empty($css_asset['attributes'])) {
        $element['#attributes'] += $css_asset['attributes'];
      }

      $elements[] = $element;
    }

    return $elements;
  }

}
