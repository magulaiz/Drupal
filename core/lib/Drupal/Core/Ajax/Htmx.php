<?php

namespace Drupal\Core\Ajax;

use Drupal\Core\Http\HtmxHeaderInterface;
use Drupal\Core\Http\HtmxResponseHeaders;
use Drupal\Core\Template\HtmxAttribute;

/**
 * Collects HTMX attributes and headers and manages complex behaviors.
 *
 * An instance of this object is required to add HTMX behaviors to a render
 * element.
 *
 * @code
 * $htmx = new Htmx();
 * $form_url = Url::fromRoute(
 * route_name: 'config.export_single',
 * route_parameters: ['config_type' => $config_type, 'config_name' => $config_name],
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

}
