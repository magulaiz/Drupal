<?php

namespace Drupal\Core\Http;

use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\HeaderBag;

/**
 * Builds HTMX response headers.
 *
 * Principally used in the composite Htmx value object.
 *
 * @see \Drupal\Core\Ajax\Htmx
 * @see https://htmx.org/reference/
 */
class HtmxResponseHeaders implements HtmxHeaderInterface {

  /**
   * Storage.
   */
  private HeaderBag $headers;

  /**
   * Initialize empty storage.
   */
  public function __construct() {
    $this->headers = new HeaderBag();
  }

  /**
   * {@inheritdoc}
   */
  public function getIterator(): \Traversable {
    return $this->headers->getIterator();
  }

  /**
   * {@inheritdoc}
   */
  public function count(): int {
    return $this->headers->count();
  }

  /**
   * {@inheritdoc}
   */
  public function __toString(): string {
    return (string) $this->headers;
  }

  /**
   * {@inheritdoc}
   */
  public function hasHeader($name): bool {
    return $this->headers->has($name);
  }

  /**
   * {@inheritdoc}
   */
  public function toArray(): array {
    $drupalHeaders = [];
    foreach ($this->headers as $name => $value) {
      // Set replace to true.
      $drupalHeaders[] = [$name, $value, TRUE];
    }
    return $drupalHeaders;
  }

  /**
   * Set HX-Location header.
   *
   * @param \Drupal\Core\Url|\Drupal\htmx\Http\HtmxLocationResponseData $data
   *   Use Url if only a path is needed.
   *
   * @return \Drupal\htmx\Http\HtmxResponseHeaders
   *   Self for chaining.
   *
   * @see https://htmx.org/headers/hx-location/
   */
  public function location(Url|HtmxLocationResponseData $data): HtmxResponseHeaders {
    if ($data instanceof HtmxLocationResponseData) {
      $this->headers->set('HX-Location', (string) $data);
    }
    else {
      $this->headers->set('HX-Location', $data->toString());
    }
    return $this;
  }

  /**
   * Set HX-Push-Url header.
   *
   * @param \Drupal\Core\Url|false $data
   *   URL to push to the location bar or false to prevent a history update.
   *
   * @return \Drupal\htmx\Http\HtmxResponseHeaders
   *   Self for chaining.
   *
   * @see https://htmx.org/headers/hx-push-url/
   */
  public function pushUrl(Url|false $data): HtmxResponseHeaders {
    if ($data instanceof Url) {
      $this->headers->set('HX-Push-Url', $data->toString());
    }
    else {
      $this->headers->set('HX-Push-Url', 'false');
    }
    return $this;
  }

  /**
   * Set HX-Replace-Url header.
   *
   * @param \Drupal\Core\Url|false $data
   *   URL for history replacement, false  prevents updates to the current URL.
   *
   * @return \Drupal\htmx\Http\HtmxResponseHeaders
   *   Self for chaining.
   *
   * @see https://htmx.org/headers/hx-replace-url/
   */
  public function replaceUrl(Url|false $data): HtmxResponseHeaders {
    if ($data instanceof Url) {
      $this->headers->set('HX-Replace-Url', $data->toString());
    }
    else {
      $this->headers->set('HX-Replace-Url', 'false');
    }
    return $this;
  }

  /**
   * Set HX-Redirect header.
   *
   * @param \Drupal\Core\Url $url
   *   Destination for a client side redirection.
   *
   * @return \Drupal\htmx\Http\HtmxResponseHeaders
   *   Self for chaining.
   */
  public function redirect(Url $url): HtmxResponseHeaders {
    $this->headers->set('HX-Redirect', $url->toString());
    return $this;
  }

  /**
   * Set HX-Refresh header.
   *
   * @param bool $refresh
   *   If set to “true” the client-side will do a full refresh of the page.
   *
   * @return \Drupal\htmx\Http\HtmxResponseHeaders
   *   Self for chaining.
   */
  public function refresh(bool $refresh): HtmxResponseHeaders {
    $this->headers->set('HX-Refresh', $refresh ? 'true' : 'false');
    return $this;
  }

  /**
   * Set HX-Reswap header.
   *
   * @param string $strategy
   *   Specify how the response will be swapped (see hx-swap).
   *
   * @return \Drupal\htmx\Http\HtmxResponseHeaders
   *   Self for chaining.
   */
  public function reswap(string $strategy): HtmxResponseHeaders {
    $this->headers->set('HX-Reswap', $strategy);
    return $this;
  }

  /**
   * Set HX-Retarget header.
   *
   * @param string $strategy
   *   CSS selector that replaces the target to a different element on the page.
   *
   * @return \Drupal\htmx\Http\HtmxResponseHeaders
   *   Self for chaining.
   */
  public function retarget(string $strategy): HtmxResponseHeaders {
    $this->headers->set('HX-Retarget', $strategy);
    return $this;
  }

  /**
   * Set HX-Reselect header.
   *
   * @param string $strategy
   *   CSS selector that changes the selection taken from the response.
   *
   * @return \Drupal\htmx\Http\HtmxResponseHeaders
   *   Self for chaining.
   */
  public function reselect(string $strategy): HtmxResponseHeaders {
    $this->headers->set('HX-Reselect', $strategy);
    return $this;
  }

  /**
   * Set HX-Trigger header.
   *
   * See the documentation for the structure of the array.
   *
   * @param string|array $data
   *   An event name or an array which will be JSON encoded.
   *
   * @return \Drupal\htmx\Http\HtmxResponseHeaders
   *   Self for chaining.
   *
   * @see https://htmx.org/headers/hx-trigger/
   */
  public function trigger(string|array $data): HtmxResponseHeaders {
    if (is_array($data)) {
      $this->headers->set('HX-Trigger', json_encode($data));
    }
    else {
      $this->headers->set('HX-Trigger', $data);
    }
    return $this;
  }

  /**
   * Set HX-Trigger-After-Settle header.
   *
   * See the documentation for the structure of the array.
   *
   * @param string|array $data
   *   An event name or an array which will be JSON encoded.
   *
   * @return \Drupal\htmx\Http\HtmxResponseHeaders
   *   Self for chaining.
   *
   * @see https://htmx.org/headers/hx-trigger/
   */
  public function triggerAfterSettle(string|array $data): HtmxResponseHeaders {
    if (is_array($data)) {
      $this->headers->set('HX-Trigger-After-Settle', json_encode($data));
    }
    else {
      $this->headers->set('HX-Trigger-After-Settle', $data);
    }
    return $this;
  }

  /**
   * Set HX-Trigger-After-Swap header.
   *
   * See the documentation for the structure of the array.
   *
   * @param string|array $data
   *   An event name or an array which will be JSON encoded.
   *
   * @return \Drupal\htmx\Http\HtmxResponseHeaders
   *   Self for chaining.
   *
   * @see https://htmx.org/headers/hx-trigger/
   */
  public function triggerAfterSwap(string|array $data): HtmxResponseHeaders {
    if (is_array($data)) {
      $this->headers->set('HX-Trigger-After-Swap', json_encode($data));
    }
    else {
      $this->headers->set('HX-Trigger-After-Swap', $data);
    }
    return $this;
  }

}
