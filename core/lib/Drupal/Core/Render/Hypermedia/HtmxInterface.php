<?php

namespace Drupal\Core\Render\Hypermedia;

use Drupal\Core\Http\HtmxHeaderInterface;
use Drupal\Core\Render\Hypermedia\Operations\HtmxOperationInterface;
use Drupal\Core\Render\Hypermedia\Operations\HtmxRequestOperationInterface;
use Drupal\Core\Template\HtmlAttributeInterface;
use Drupal\Core\Template\HtmxAttribute;

/**
 * An interface for objects that manage HTMX behaviors.
 */
interface HtmxInterface {

  /**
   * Accessor for the HtmxAttribute.
   *
   * @return \Drupal\Core\Template\HtmxAttribute
   */
  public function attributes(): HtmxAttribute;

  /**
   * Accessor for the headers, typically an instance of HtmxResponseHeader.
   *
   * @return \Drupal\Core\Http\HtmxHeaderInterface
   */
  public function headers(): HtmxHeaderInterface;

  /**
   * Merges this object's attributes with an array or collection of attributes.
   *
   * @param \Drupal\Core\Template\HtmlAttributeInterface|array $attributes
   *   The data to merge.
   *
   * @return \Drupal\Core\Template\HtmlAttributeInterface|array
   *   Returns the data in the form it was recieved.
   */
  public function getCombinedAttributes(HtmlAttributeInterface|array $attributes): HtmlAttributeInterface|array;

  /**
   * Produces a merged Drupal 'http_header' array.
   *
   * @param array $headers
   *   A Drupal 'http_header' array to augment.
   *
   * @return array
   *   This objects headers merged with the received headers.
   */
  public function getCombinedHeaders(array $headers): array;

  /**
   * Merges this objects data with another instance of HtmxInterface.
   *
   * @param \Drupal\Core\Render\Hypermedia\HtmxInterface $htmx
   *   The source of additional data.
   *
   * @return \Drupal\Core\Render\Hypermedia\HtmxInterface
   *   An instance with attributes and headers from both objects.
   */
  public function merge(HtmxInterface $htmx): HtmxInterface;

  /**
   * Sets the HTMX request operation for the element.
   *
   * @param \Drupal\Core\Render\Hypermedia\Operations\HtmxRequestOperationInterface $operation
   *   The operation.
   *
   * @return \Drupal\Core\Render\Hypermedia\HtmxInterface
   *   Returns self to enable chained methods.
   */
  public function setRequestOperation(HtmxRequestOperationInterface $operation): HtmxInterface;

  /**
   * Set an additional operation that don't use a request.
   *
   * These should be accumulated in a stack that is processed in
   * ::processOperations.
   *
   * @param \Drupal\Core\Render\Hypermedia\Operations\HtmxOperationInterface $operation
   *   The operation
   *
   * @return \Drupal\Core\Render\Hypermedia\HtmxInterface
   *   Returns self to enable chained methods.
   */
  public function setAdditionalOperation(HtmxOperationInterface $operation): HtmxInterface;

  /**
   * Checks for request or additional operations.
   *
   * @return bool
   *   True if any operations are set.
   */
  public function hasOperations(): bool;

  /**
   * Step through all the operations, calling their ::setProperties method.
   */
  public function processOperations(): void;

}
