<?php

namespace Drupal\Core\Ajax;

use Drupal\Core\Http\HtmxHeaderInterface;
use Drupal\Core\Http\HtmxResponseHeaders;
use Drupal\Core\Http\HttpMethods;
use Drupal\Core\Template\HtmxAttribute;
use Drupal\Core\Url;

/**
 * Collects HTMX attributes and headers and manages complex behaviors.
 *
 * An instance of this object is required to add HTMX behaviors to a render
 * element.
 *
 * Methods to configure common behaviors are provided:
 *
 * @code
 * $htmx = new Htmx();
 * $node_url = Url::fromRoute(
 *    route_name: entity.node.canonical',
 *    route_parameters: ['node' => 123],
 *  );
 * $htmx->insert($node_url, 'div.example', 'article.page')
 *
 * $build = [
 *    '#htmx' => $htmx,
 *  ];
 * @endcode
 *
 * Attributes may also be specified explicitly:
 *
 * @code
 * $htmx = new Htmx();
 * $form_url = Url::fromRoute(
 *   route_name: 'config.export_single',
 *   route_parameters: ['config_type' => $config_type, 'config_name' => $config_name],
 * );
 *
 * $htmx->attributes
 *   ->post($form_url)
 *   ->select('select[data-drupal-selector="edit-config-name"]')
 *   ->target('select[data-drupal-selector="edit-config-name"]')
 *   ->swap('outerHTML');
 *
 * $build = [
 *   '#htmx' => $htmx,
 * ];
 * @endcode
 *
 * HTMX headers are added in a similar way.
 *
 * @code
 * // Also update the browser URL.
 * $push = Url::fromRoute(
 *   route_name: 'config.export_single',
 *   route_parameters: ['config_type' => $default_type, 'config_name' => $default_name],
 * );
 *
 * $htmx = new Htmx();
 * $htmx->headers->pushUrl($push);
 * $build = [
 *   '#htmx' => $htmx,
 * ];
 * @endcode
 *
 * @see \Drupal\Core\Template\HtmxAttribute
 * @see \Drupal\Core\Http\HtmxResponseHeaders
 */
class Htmx {

  public function __construct(
    public readonly HtmxAttribute $attributes = new HtmxAttribute(),
    public readonly HtmxHeaderInterface $headers = new HtmxResponseHeaders(),
  ) {}

  public function setRequest(Url $url, HttpMethods $method) {
    switch ($method) {
      case HttpMethods::Get:
        $this->attributes->get($url);
        break;

      case HttpMethods::Put:
        $this->attributes->put($url);
        break;

      case HttpMethods::Patch:
        $this->attributes->patch($url);
        break;

      case HttpMethods::Post:
        $this->attributes->post($url);
        break;

      case HttpMethods::Delete:
        $this->attributes->delete($url);
        break;
    }
  }

  /**
   * Configures HTMX properties to select and insert content from a request.
   *
   * Selected content will be inserted in the target element as the last content
   * before the closing tag.
   *
   * @param \Drupal\Core\Url $url
   *   The Url which is used to request the content.
   * @param string $selector
   *   A CSS selector that selects the new content within the returned response.
   * @param string $target
   *   A CSS selector that selects the target element in the current page.
   * @param \Drupal\Core\Http\HttpMethods $method
   *   The HTTP method to use. Defaults to GET.
   *
   * @return \Drupal\Core\Ajax\Htmx
   *   Returns this object for method chaining.
   */
  public function insert(Url $url, string $selector, string $target, HttpMethods $method = HttpMethods::Get): Htmx {
    $this->setRequest($url, $method);
    $this->attributes->select($selector);
    $this->attributes->target($target);
    $this->attributes->swap('beforeend');

    return $this;
  }

}
