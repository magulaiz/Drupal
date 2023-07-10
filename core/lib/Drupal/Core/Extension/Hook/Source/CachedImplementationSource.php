<?php

declare(strict_types = 1);

namespace Drupal\Core\Extension\Hook\Source;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ExtensionEvents;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Decorator to cache the implementation sources.
 */
class CachedImplementationSource implements ImplementationSourceInterface, EventSubscriberInterface {

  const CACHE_ID = 'hook_implementation_source';

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Extension\Hook\Source\ImplementationSourceInterface $decorated
   *   Decorated source.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   Cache backend.
   * @param string $cacheId
   *   Cache id.
   */
  public function __construct(
    #[AutowireDecorated]
    private readonly ImplementationSourceInterface $decorated,
    #[Autowire('@cache.bootstrap')]
    private readonly CacheBackendInterface $cacheBackend,
    #[Autowire(self::CACHE_ID)]
    private readonly string $cacheId,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function getImplementations(): array {
    $cache = $this->cacheBackend->get($this->cacheId);
    if ($cache) {
      return $cache->data;
    }
    $implementations = $this->decorated->getImplementations();
    $this->cacheBackend->set($this->cacheId, $implementations);
    return $implementations;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      ExtensionEvents::MODULE_LIST_WAS_UPDATED => 'reset',
      ExtensionEvents::HOOKS_REBUILD_REQUESTED => 'reset',
    ];
  }

  /**
   * Clears the cache.
   */
  public function reset(): void {
    $this->cacheBackend->delete(self::CACHE_ID);
  }

}
