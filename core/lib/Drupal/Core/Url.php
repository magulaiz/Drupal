<?php

namespace Drupal\Core;

use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\Request;

class Url extends UrlBase {

  protected bool $routeChecked = TRUE;

  protected static function fromInternalUri(array $uri_parts, array $options) {
    // Defer route checking, use 'base:' schema.
    // @see \Drupal\Core\UrlBase::fromUri
    $path = $uri_parts['path'];
    // Workaround a bug like in parent class.
    // @see \Drupal\Core\UrlBase::fromUri
    if (preg_match('|^\d|', $path)) {
      $path = "/$path";
    }
    $uri = "base:$path";
    $url = new static($uri, [], $options);
    $url->routeChecked = FALSE;
    return $url;
  }

  public function ensureRouteChecked() {
    // Do deferred route checking once requested.
    if (!$this->routeChecked) {
      $uri = $this->uri;
      assert(str_starts_with($uri, 'base:'));
      $path = substr($uri, 5);
      $path = ltrim($path, '/');

      $url = UrlBase::fromUri("internal:/$path");
      if ($url->isRouted()) {
        $this->routeName = $url->getRouteName();
      }
      else {
        // An internal: URL can still result in a base: URL if no route.
        $this->routeName = $url->getUri();
      }

      $this->routeParameters = $url->getRouteParameters();
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
    if ($this->routeChecked) {
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
