<?php

declare(strict_types=1);

namespace Drupal\system\StackMiddleware;

use Drupal\Core\StackMiddleware\NegotiationMiddleware as CoreNegotiationMiddleware;
use Symfony\Component\HttpFoundation\Request;

/**
 * Attempts to provide a default format based on the request host name.
 *
 * Sites which serve more applications than a website often use an alternative
 * response format, like "hal_json", to transmit resources. This means that
 * Drupal often has more than one available response format per path. Out of the
 * box, Drupal will always choose HTML unless otherwise specified in the request
 * via the "_format" query parameter. In other words, the only way to get a JSON
 * response for a path with more than one format available is to use the
 * "_format" query string. For example, "/node/1?_format=json".
 *
 * This class makes it possible to declare an custom default format based
 * on the request host name. For example, one could specify that the default
 * format for requests to "api.example.com" is "hal_json" instead of "html". By
 * doing so, a request to "api.example.com/node/1" would return a JSON response
 * and the only way to get an HTML response would be to use the "_format" query
 * string like so: "/node/1?_format=html".
 *
 * It is possible to have different default formats per-host name. For example,
 * "www.example.com" could default to HTML and "api.example.com" could default
 * to JSON.
 *
 * @see system.services.yml
 */
final class NegotiationMiddleware extends CoreNegotiationMiddleware {

  /**
   * Default formats.
   *
   * Use getDefaultHostFormats() instead of accessing this property.
   *
   * @var array
   *
   * @see setDefaultHostFormats()
   * @see getDefaultHostFormats()
   */
  protected $hostFormats;

  /**
   * Set default host name formats.
   *
   * @param array $host_formats
   *   A map of formats to host names which will default to that format.
   */
  public function setDefaultHostFormats($host_formats = []) {
    $this->hostFormats = $host_formats;
  }

  /**
   * Get default host name formats.
   *
   * If the configured format is not available, it will be filtered out of the
   * return value.
   *
   * @example
   * [
   *   'json' => ['api.example.com', 'backend.example.com'],
   *   'xml' => ['soap.example.com'],
   * ]
   *
   * @return array
   *   An array keyed by a normalized format name whose values are an array of
   *   host names using that format as their default.
   */
  protected function getDefaultHostFormats() {
    return array_filter($this->hostFormats, function (string $format) {
      return isset($this->formats[$format]);
    }, ARRAY_FILTER_USE_KEY);
  }

  /**
   * {@inheritdoc}
   */
  protected function getContentType(Request $request) {
    // If the request explicitly set a format, defer to it.
    if ($type = parent::getContentType($request)) {
      return $type;
    }
    // The request didn't set a format. Get the request's host name. Then, try
    // to find a configured default for that host name.
    $host = $request->getHost();
    foreach ($this->getDefaultHostFormats() as $format => $host_names) {
      // If there's case-insensitive match, then return the configured default.
      if (in_array(strtolower($host), array_map('strtolower', $host_names), TRUE)) {
        return $format;
      }
    }
    // Otherwise, no opinion.
    return NULL;
  }

}
