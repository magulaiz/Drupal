<?php

namespace Drupal\Core;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\Request;

class Url extends UrlBase {

  protected bool $unparsed = FALSE;
  protected RouteMatchInterface $routeMatch;

  public static function fromRouteMatch(RouteMatchInterface $route_match) {
    $instance = parent::fromRouteMatch($route_match);
    $instance->routeMatch = $route_match;
    return $instance;
  }

  public static function fromUserInput($user_input, $options = []) {
    if (str_starts_with($user_input, '/')) {
      // Use unparsed url for now.
      $path = substr($user_input, 1);
      $instance = parent::fromUri('base:' . $path, $options);
      $instance->unparsed = TRUE;
      return $instance;
    }
    else {
      // Pass on user input starting with '#' and '?'.
      // @todo Consider optimizing this case.
      return parent::fromUserInput($user_input, $options);
    }
  }

  public static function fromUri($uri, $options = []) {
    // Use unparsed url for now if possible.
    $tryUnparsed = str_starts_with($uri, 'internal:/');
    if ($tryUnparsed) {
      $uri = 'base:' . substr($uri, 10);
    }
    $instance = parent::fromUserInput($uri, $options);
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

      $url = Url::fromUri("internal:/$path");
      $this->routeName = $url->getRouteName();
      $this->routeParameters = $url->getRouteParameters();
      $this->options = $url->getOptions();

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
