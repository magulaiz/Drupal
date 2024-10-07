<?php

namespace Drupal\Core\Routing;

use Drupal\Component\Utility\UrlHelper;

/**
 * Provides a trait which ensures that a URL is safe to redirect to.
 */
trait LocalAwareRedirectResponseTrait {

  /**
   * The request context.
   *
   * @var \Drupal\Core\Routing\RequestContext
   *
   * @deprecated in drupal:11.1.0 and is removed from drupal:12.0.0.
   *   Use ::baseUrl instead.
   * @see https://www.drupal.org/node/3279668
   */
  protected $requestContext;

  /**
   * The base URL of the application.
   *
   * @var string
   */
  protected string $baseUrl;

  /**
   * Determines whether a path is local.
   *
   * @param string $url
   *   The internal path or external URL being linked to, such as "node/34" or
   *   "http://example.com/foo".
   *
   * @return bool
   *   TRUE or FALSE, where TRUE indicates a local path.
   */
  protected function isLocal($url) {
    return !UrlHelper::isExternal($url) || UrlHelper::externalIsLocal($url, $this->getBaseUrl());
  }

  /**
   * Returns the base URL.
   *
   * @return string
   *   The base URL.
   */
  protected function getBaseUrl(): string {
    if (!isset($this->baseUrl)) {
      $this->baseUrl = \Drupal::app()->getBaseUrl();
    }
    return $this->baseUrl;
  }

  /**
   * Sets the base URL.
   *
   * @param string $base_url
   *   The base URL of the application.
   *
   * @return $this
   */
  public function setBaseUrl(string $base_url): static {
    $this->baseUrl = $base_url;

    return $this;
  }

  /**
   * Returns the request context.
   *
   * @return \Drupal\Core\Routing\RequestContext
   *   The request context.
   *
   * @deprecated in drupal:11.1.0 and is removed from drupal:12.0.0.
   *   Use ::getBaseUrl() instead.
   * @see https://www.drupal.org/node/3279668
   */
  protected function getRequestContext() {
    @trigger_error(__METHOD__ . ' is deprecated in drupal:11.1.0 and is removed from drupal:12.0.0. Use ' . __CLASS__ . '::getBaseUrl() instead. See https://www.drupal.org/node/3279668', E_USER_DEPRECATED);
    if (!isset($this->requestContext)) {
      $this->requestContext = \Drupal::service('router.request_context');
    }
    return $this->requestContext;
  }

  /**
   * Sets the request context.
   *
   * @param \Drupal\Core\Routing\RequestContext $request_context
   *   The request context.
   *
   * @return $this
   *
   * @deprecated in drupal:11.1.0 and is removed from drupal:12.0.0.
   *   Use ::setBaseUrl() instead.
   * @see https://www.drupal.org/node/3279668
   */
  public function setRequestContext(RequestContext $request_context) {
    @trigger_error(__METHOD__ . ' is deprecated in drupal:11.1.0 and is removed from drupal:12.0.0. Use ' . __CLASS__ . '::setBaseUrl() instead. See https://www.drupal.org/node/3279668', E_USER_DEPRECATED);
    $this->requestContext = $request_context;

    return $this;
  }

}
