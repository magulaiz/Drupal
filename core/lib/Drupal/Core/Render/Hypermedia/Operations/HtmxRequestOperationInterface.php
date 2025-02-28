<?php

namespace Drupal\Core\Render\Hypermedia\Operations;


use Drupal\Core\Http\HttpMethod;
use Drupal\Core\Url;

/**
 * This interface segments HTMX operations that perform and process a request.
 *
 * There can only be one HTMX request attribute and accompanying processing
 * attributes on an HTML element.
 */
interface HtmxRequestOperationInterface extends HtmxOperationInterface {

  /**
   * Configures the method and url for the request.
   *
   * @param \Drupal\Core\Http\HttpMethod $method
   *   The request method.
   * @param \Drupal\Core\Url $url
   *   The request URL.
   *
   * @return static
   *   The instance using the trait.
   */
  public function setRequest(HttpMethod $method, Url $url): static;

}
