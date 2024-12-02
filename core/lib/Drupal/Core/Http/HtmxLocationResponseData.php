<?php

namespace Drupal\Core\Http;

use Drupal\Core\Url;

/**
 * Optional data object for HX-Location.
 */
class HtmxLocationResponseData implements \Stringable {

  /**
   * Data for HX-Location headers.
   *
   * @param \Drupal\Core\Url $path
   *   The path for the GET request.
   * @param string $source
   *   The source element of the request.
   * @param string $event
   *   An event that “triggered” the request.
   * @param array<string, string> $headers
   *   Headers to submit with the request.
   * @param string $handler
   *   A callback that will handle the response HTML.
   * @param string $target
   *   The target for the swap.
   * @param string $swap
   *   The swap strategy.
   * @param string $select
   *   A selector for the content to swap into the target.
   * @param array<string, string> $values
   *   A set of values to submit with the request.
   *
   * @see https://htmx.org/headers/hx-location/
   */
  public function __construct(
    public readonly Url $path,
    public readonly string $source = '',
    public readonly string $event = '',
    public readonly array $headers = [],
    public readonly string $handler = '',
    public readonly string $target = '',
    public readonly string $swap = '',
    public readonly string $select = '',
    public readonly array $values = [],
  ) {}

  /**
   * Returns non-empty data, JSON encoded.
   *
   * @return string
   *   The encoded data.
   */
  public function __toString(): string {
    $data = [
      'path' => $this->path->toString(),
      'source' => $this->source,
      'event' => $this->event,
      'headers' => $this->headers,
      'handler' => $this->handler,
      'target' => $this->target,
      'swap' => $this->swap,
      'select' => $this->select,
      'values' => $this->values,
    ];
    $data = array_filter($data, static fn ($item) => $item !== '' && $item !== []);
    return json_encode($data);
  }

}
