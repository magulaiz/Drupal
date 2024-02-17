<?php

namespace Drupal\Core\PageCache\ResponsePolicy;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\PageCache\ResponsePolicyInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A policy adding vary header to response.
 */
class Vary implements ResponsePolicyInterface {

  /**
   * Array of Vary: headers to add to response.
   *
   * @var array
   */
  protected array $vary = [];

  /**
   * Add vary header.
   */
  public function add($header) {
    if (!in_array($header, $this->vary)) {
      $this->vary[] = $header;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function check(Response $response, Request $request): void {
    if ($this->vary) {
      $response->setVary($this->vary);
      if ($response instanceof CacheableResponseInterface) {
        $metadata = new CacheableMetadata();
        $contexts = [];
        foreach ($this->vary as $vary_header) {
          $contexts[] = 'headers:' . $vary_header;
        }
        $metadata->addCacheContexts($contexts);
        $response->addCacheableDependency($metadata);
      }
    }
  }

}
