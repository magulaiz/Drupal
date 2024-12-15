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
  public function hasHeader(string $name): bool;

  /**
   * Returns all storage elements as a Drupal 'http_header' array.
   *
   * @return mixed[]
   *   An associative array of headers.
   */
  public function toArray(): array;

  /**
   * Merges an HtmxHeaderInterface object into the current storage.
   *
   * @param HtmlAttributeInterface $headers
   *   The Attribute object to merge.
   *
   * @return HtmxHeaderInterface
   *   A combined header collection.
   */
  public function merge(HtmxHeaderInterface $headers): HtmxHeaderInterface;

}
