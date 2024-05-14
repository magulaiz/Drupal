<?php

namespace Drupal\Core\Cache;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This service allows fetching any cache bin service.
 *
 * This factory doesn't create a bin itself but allows the retrieval of a bin
 * service using only the bin name. This is useful in situations where your
 * code may need to use different bins based on input. An example would be
 * the render system having the cache bin defined in the render array.
 *
 * @see \Drupal\Core\Cache\ListCacheBinsPass::process()
 */
class DelegatedCacheFactory extends \ArrayObject implements CacheFactoryInterface {

  /**
   * Constructor.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Service container.
   */
  public function __construct(
    protected ContainerInterface $container,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function get($bin) {
    // The corresponding offsetSet() call is in ListCacheBinsPass::process().
    return $this->container->get($this->offsetGet($bin));
  }

}
