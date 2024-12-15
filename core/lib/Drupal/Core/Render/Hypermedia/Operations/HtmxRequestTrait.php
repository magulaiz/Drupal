<?php

namespace Drupal\Core\Render\Hypermedia\Operations;

use Drupal\Core\Http\HttpMethod;
use Drupal\Core\Render\Hypermedia\HtmxInterface;
use Drupal\Core\Url;

/**
 * Provides reusable methods for HtmxRequestOperationInterface objects.
 */
trait HtmxRequestTrait {

  /**
   * The method to use for the request.
   */
  protected HttpMethod $method;

  /**
   * The url to use for the request.
   */
  protected Url $url;

  /**
   * Setter for the required properties.
   *
   * @param \Drupal\Core\Http\HttpMethod $method
   *   The request method.
   * @param \Drupal\Core\Url $url
   *   The request URL.
   */
  public function setRequest(HttpMethod $method, Url $url): void {
    $this->method = $method;
    $this->url = $url;
  }

  /**
   * Map the stored parameters to HTMX attributes.
   */
  public function configureRequest(HtmxInterface $htmx): void {
    switch ($this->method) {
      case HttpMethod::Get:
        $htmx->attributes()->get($this->url);
        break;

      case HttpMethod::Put:
        $htmx->attributes()->put($this->url);
        break;

      case HttpMethod::Patch:
        $htmx->attributes()->patch($this->url);
        break;

      case HttpMethod::Post:
        $htmx->attributes()->post($this->url);
        break;

      case HttpMethod::Delete:
        $htmx->attributes()->delete($this->url);
        break;
    }
  }

}
