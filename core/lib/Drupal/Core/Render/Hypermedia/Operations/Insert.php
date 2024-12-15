<?php

namespace Drupal\Core\Render\Hypermedia\Operations;

use Drupal\Core\Http\HttpMethod;
use Drupal\Core\Render\Hypermedia\HtmxInterface;
use Drupal\Core\Url;

/**
 * Inserts the selected element as the last child of the target element.
 *
 * There are three required properties and one optional property.
 * - selector: A CSS selector used to select the element from the response that
 *   should be inserted into the DOM.
 * - target: A CSS selector used to select the existing element in the DOM to
 *   receive the insertion.
 * - url: A Url object used to configure the request.
 * - method: (Optional) The HTTP method to be used for the request. Defaults to
 *   HttpMethod::Get.
 */
class Insert implements HtmxRequestOperationInterface {
  use HtmxRequestTrait;

  public function __construct(protected string $selector, protected string $target, Url $url, HttpMethod $method = HttpMethod::Get) {
    $this->setRequest($method, $url);
  }

  /**
   * {@inheritdoc}
   */
  public function setProperties(HtmxInterface $htmx): void {
    $this->configureRequest($htmx);
    $htmx->attributes()->select($this->selector);
    $htmx->attributes()->target($this->target);
    $htmx->attributes()->swap('beforeend');
  }

}
