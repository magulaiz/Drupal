<?php

namespace Drupal\Core;

use Drupal\Core\Session\AccountInterface;

class Url extends UrlBase {

  protected bool $routeChecked = TRUE;

  protected static function fromInternalUri(array $uri_parts, array $options) {
    // Defer route checking, use 'base:' schema.
    // @see \Drupal\Core\UrlBase::fromUri
    $path = $uri_parts['path'];
    $path = ltrim($path, '/');
    // Workaround a bug like in parent class.
    // @see \Drupal\Core\UrlBase::fromUri
    if (preg_match('|^\d|', $path)) {
      $path = "/$path";
    }
    $uri = "base:$path";
    $url = new static($uri, [], $options);
    $url->routeChecked = FALSE;
    $url->setUnrouted();
    return $url;
  }

  public function ensureRouteChecked() {
    // Do deferred route checking once requested.
    if (!$this->routeChecked) {
      assert($this->unrouted === TRUE);
      assert(str_starts_with($this->uri, 'base:'), $this->uri);
      // Like ::toString, but without calling ::getUri
      $path = $this->toString();
      $path = ltrim($path, '/');

      // @fixme Add options
      $url = UrlBase::fromUri("internal:$path");
      if ($url->isRouted()) {
        $this->routeName = $url->getRouteName();
        $this->routeParameters = $url->getRouteParameters();
      }
      else {
        // An internal: URL can still result in a base: URL if no route.
        $this->routeName = $url->getUri();
        $this->routeParameters = [];
      }

      $this->options = $url->getOptions();

      $this->external = $url->isExternal();
      $this->unrouted = !$url->isRouted();
      $this->uri = $this->unrouted ? $url->getUri() : NULL;
      $this->internalPath = NULL;

      $this->routeChecked = TRUE;
    }
  }

  /**
   * @return bool
   */
  public function isRouteChecked() {
    return $this->routeChecked;
  }

  public function getInternalPath() {
    if (!$this->routeChecked) {
      $uri = $this->uri;
      assert(substr($uri, 0, 5) === 'base:');
      $path = substr($uri, 5);
      $path = ltrim($path, '/');
      return $path;
    }
    else {
      return parent::getInternalPath();
    }
  }

  public function toString($collect_bubbleable_metadata = FALSE) {
    if ($this->unrouted) {
      // Copied from parent, but replace ::getUri() with ::uri
      return $this->unroutedUrlAssembler()->assemble($this->uri, $this->getOptions(), $collect_bubbleable_metadata);
    }

    return $this->urlGenerator()->generateFromRoute($this->getRouteName(), $this->getRouteParameters(), $this->getOptions(), $collect_bubbleable_metadata);
  }


  public function toUriString() {
    $this->ensureRouteChecked();
    return parent::toUriString();
  }

  public function isRouted() {
    $this->ensureRouteChecked();
    return parent::isRouted();
  }

  public function getRouteName() {
    $this->ensureRouteChecked();
    return parent::getRouteName();
  }

  public function getRouteParameters() {
    $this->ensureRouteChecked();
    return parent::getRouteParameters();
  }

  public function setRouteParameters($parameters) {
    $this->ensureRouteChecked();
    return parent::setRouteParameters($parameters);
  }

  public function setRouteParameter($key, $value) {
    $this->ensureRouteChecked();
    return parent::setRouteParameter($key, $value);
  }

  public function getUri() {
    $this->ensureRouteChecked();
    return parent::getUri();
  }

  public function access(AccountInterface $account = NULL, $return_as_object = FALSE) {
    $this->ensureRouteChecked();
    return parent::access($account, $return_as_object);
  }

}
