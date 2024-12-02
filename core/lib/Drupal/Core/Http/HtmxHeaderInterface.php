<?php

namespace Drupal\Core\Http;

/**
 * An interface for classes that manage HTMX Headers.
 *
 * @extends \IteratorAggregate<string, list<string|null>>
 */
interface HtmxHeaderInterface extends \IteratorAggregate, \Countable, \Stringable {

  /**
   * Checks if the storage has a header with the given name.
   *
   * @param string $name
   *   The name of the header to check for.
   *
   * @return bool
   *   Returns TRUE if the header exists, or FALSE otherwise.
   */
  public function hasHeader($name): bool;

  /**
   * Returns all storage elements as a Drupal 'http_header' array.
   *
   * @return mixed[]
   *   An associative array of headers.
   */
  public function toArray(): array;

}
