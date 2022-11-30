<?php

namespace Drupal\Core;

use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\Request;

class Url extends UrlBase {

  protected bool $unparsed = FALSE;

  public static function fromUri($uri, $options = []) {
    // Use unparsed url for now if possible.
    $tryUnparsed = str_starts_with($uri, 'internal:/');
    if ($tryUnparsed) {
      $uri = 'base:' . substr($uri, 10);
    }
    $instance = parent::fromUri($uri, $options);
    $instance->unparsed = $tryUnparsed;
    return $instance;
  }

  public static function createFromRequest(Request $request) {
    // @todo Consider optimizing this case.
    return parent::createFromRequest($request);
  }

  protected function ensureParsed() {
    if ($this->unparsed) {
      $uri = $this->uri;
      assert(str_starts_with($uri, 'base:'));
      $path = substr($uri, 5);
      $path = ltrim($path, '/');

      $url = UrlBase::fromUri("internal:/$path");
      $this->routeName = $url->getRouteName();
      $this->routeParameters = $url->getRouteParameters();
      $this->options = $url->getOptions();

      $this->external = $url->isExternal();
      $this->unrouted = !$url->isRouted();
      $this->uri = $this->unrouted ? $url->getUri() : NULL;
      $this->internalPath = NULL;

      $this->unparsed = FALSE;
    }
  }

  public function getInternalPath() {
    if ($this->unparsed) {
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
    $this->ensureParsed();
    return parent::toUriString();
  }

  public function isRouted() {
    $this->ensureParsed();
    return parent::isRouted();
  }

  public function getRouteName() {
    $this->ensureParsed();
    return parent::getRouteName();
  }

  public function getRouteParameters() {
    $this->ensureParsed();
    return parent::getRouteParameters();
  }

  public function setRouteParameters($parameters) {
    $this->ensureParsed();
    return parent::setRouteParameters($parameters);
  }

  public function setRouteParameter($key, $value) {
    $this->ensureParsed();
    return parent::setRouteParameter($key, $value);
  }

  public function getUri() {
    $this->ensureParsed();
    return parent::getUri();
  }

  public function access(AccountInterface $account = NULL, $return_as_object = FALSE) {
    $this->ensureParsed();
    return parent::access($account, $return_as_object);
  }

}
