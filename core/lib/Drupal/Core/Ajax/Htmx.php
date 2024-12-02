<?php

namespace Drupal\Core\Ajax;

use Drupal\Core\Http\HtmxHeaderInterface;
use Drupal\Core\Http\HtmxResponseHeaders;
use Drupal\Core\Template\HtmxAttribute;

/**
 * A value object that collects HTMX attributes and headers.
 */
class Htmx {

  public function __construct(
    public readonly HtmxAttribute $attributes = new HtmxAttribute(),
    public readonly HtmxHeaderInterface $headers = new HtmxResponseHeaders(),
  ) {}

}
