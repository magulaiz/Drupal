<?php

namespace Drupal\Core\Render;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Cache\VariationCacheFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Wraps the caching logic for the render caching system.
 *
 * @internal
 */
class RenderCache implements RenderCacheInterface {

  /**
   * Constructs a new RenderCache object.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\Core\Cache\VariationCacheFactoryInterface $cacheFactory
   *   The variation cache factory.
   * @param \Drupal\Core\Cache\Context\CacheContextsManager $cacheContextsManager
   *   The cache contexts manager.
   */
  public function __construct(
    protected RequestStack $requestStack,
    protected VariationCacheFactoryInterface $cacheFactory,
    protected CacheContextsManager $cacheContextsManager,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function get(array $elements) {
    if (!$this->isElementCacheable($elements)) {
      return FALSE;
    }

    $bin = $elements['#cache']['bin'] ?? 'render';
    if (($cache_bin = $this->cacheFactory->get($bin)) && $cache = $cache_bin->get($elements['#cache']['keys'], CacheableMetadata::createFromRenderArray($elements))) {
      if (!$this->requestStack->getCurrentRequest()->isMethodCacheable()) {
        if (!empty(array_filter($cache->tags, fn (string $tag) => str_starts_with($tag, 'CACHE_MISS_IF_UNCACHEABLE_HTTP_METHOD:')))) {
          return FALSE;
        }
      }
      return $cache->data;
    }
    return FALSE;
  }

  /**
   * Retrieves multiple cached render arrays at once, following redirects until resolution.
   *
   * Steps:
   * - For each render element, compute a cache ID (CID) based on its keys and cacheability.
   * - Group elements by their cache bin.
   * - For each bin, use getMultiple() to fetch all items in one go.
   * - Follow any redirects discovered, updating CIDs and refetching until resolved.
   * - If the current HTTP request method is not cacheable, skip items tagged
   *   with 'CACHE_MISS_IF_UNCACHEABLE_HTTP_METHOD:'.
   *
   * Only items that resolve to a final cached value are returned. Misses or
   * disallowed items (due to request method constraints) are omitted entirely.
   *
   * @param array $multiple_elements
   *   An associative array keyed by arbitrary identifiers. Each value should be
   *   a render array that includes '#cache' with 'keys' and optionally 'bin', e.g.:
   *   $multiple_elements = [
   *     'item_a' => [
   *       '#cache' => ['keys' => ['key1', 'key2'], 'bin' => 'render'],
   *       // ... additional render array data ...
   *     ],
   *     'item_b' => [
   *       '#cache' => ['keys' => ['another_key']], // defaults to 'render' bin
   *       // ... additional render array data ...
   *     ],
   *   ];
   *
   * @return array
   *   An associative array keyed by the same keys as $multiple_elements for items that
   *   are found and allowed. Items that never resolve or violate request conditions
   *   are not returned.
   */
  public function getMultiple(array $multiple_elements): array {
    $bin_map = [];
    foreach ($multiple_elements as $item_key => $elements) {
      if (!$this->isElementCacheable($elements)) {
        continue;
      }

      $bin = $elements['#cache']['bin'] ?? 'render';
      $keys = $elements['#cache']['keys'];
      $cacheability = CacheableMetadata::createFromRenderArray($elements);

      $bin_map[$bin][$item_key] = [$keys, $cacheability];
    }

    $results = [];
    $is_method_cacheable = $this->requestStack->getCurrentRequest()->isMethodCacheable();

    foreach ($bin_map as $bin => $items) {
      $cache_bin = $this->cacheFactory->get($bin);
      if (!$cache_bin) {
        continue;
      }

      $fetched_items = $cache_bin->getMultiple($items);

      foreach ($fetched_items as $item_key => $cache) {
        if (
          !$is_method_cacheable &&
          !empty(array_filter($cache->tags, fn (string $tag) => str_starts_with($tag, 'CACHE_MISS_IF_UNCACHEABLE_HTTP_METHOD:')))
        ) {
          continue;
        }
        $results[$item_key] = $cache->data;
      }
    }

    return $results;
  }

  /**
   * {@inheritdoc}
   */
  public function set(array &$elements, array $pre_bubbling_elements) {
    // Avoid setting cache items on POST requests, this ensures that cache items
    // with a very low hit rate won't enter the cache. All render elements
    // except forms will still be retrieved from cache when available.
    if (!$this->requestStack->getCurrentRequest()->isMethodCacheable() || !$this->isElementCacheable($elements)) {
      return FALSE;
    }

    $bin = $elements['#cache']['bin'] ?? 'render';
    $cache_bin = $this->cacheFactory->get($bin);
    $data = $this->getCacheableRenderArray($elements);
    $cache_bin->set(
      $elements['#cache']['keys'],
      $data,
      CacheableMetadata::createFromRenderArray($data)->addCacheTags(['rendered']),
      CacheableMetadata::createFromRenderArray($pre_bubbling_elements)
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableRenderArray(array $elements) {
    $data = [
      '#markup' => $elements['#markup'],
      '#attached' => $elements['#attached'],
      '#cache' => [
        'contexts' => $elements['#cache']['contexts'],
        'tags' => $elements['#cache']['tags'],
        'max-age' => $elements['#cache']['max-age'],
      ],
    ];

    // Preserve cacheable items if specified. If we are preserving any cacheable
    // children of the element, we assume we are only interested in their
    // individual markup and not the parent's one, thus we empty it to minimize
    // the cache entry size.
    if (!empty($elements['#cache_properties']) && is_array($elements['#cache_properties'])) {
      $data['#cache_properties'] = $elements['#cache_properties'];

      // Extract all the cacheable items from the element using cache
      // properties.
      $cacheable_items = array_intersect_key($elements, array_flip($elements['#cache_properties']));
      $cacheable_children = Element::children($cacheable_items);
      if ($cacheable_children) {
        $data['#markup'] = '';
        // Cache only cacheable children's markup.
        foreach ($cacheable_children as $key) {
          // We can assume that #markup is safe at this point.
          $cacheable_items[$key] = ['#markup' => Markup::create($cacheable_items[$key]['#markup'])];
        }
      }
      $data += $cacheable_items;
    }

    $data['#markup'] = Markup::create($data['#markup']);
    return $data;
  }

  /**
   * Checks whether a renderable array can be cached.
   *
   * This allows us to not even have to instantiate the cache backend if a
   * renderable array does not have any cache keys or specifies a zero cache
   * max age.
   *
   * @param array $element
   *   A renderable array.
   *
   * @return bool
   *   Whether the renderable array is cacheable.
   */
  protected function isElementCacheable(array $element) {
    // If the maximum age is zero, then caching is effectively prohibited.
    if (isset($element['#cache']['max-age']) && $element['#cache']['max-age'] === 0) {
      return FALSE;
    }
    return isset($element['#cache']['keys']);
  }

}
