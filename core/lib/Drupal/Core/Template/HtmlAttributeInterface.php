<?php

namespace Drupal\Core\Template;


use Drupal\Component\Render\MarkupInterface;

/**
 * An interface for classes that manage HTML Attributes.
 *
 * @extends \IteratorAggregate<string, \Drupal\Core\Template\AttributeValueBase>
 */
interface HtmlAttributeInterface extends  \Countable, \IteratorAggregate, MarkupInterface {

  /**
   * Checks if the storage has an attribute with the given name.
   *
   * @param string $name
   *   The name of the attribute to check for.
   *
   * @return bool
   *   Returns TRUE if the attribute exists, or FALSE otherwise.
   */
  public function hasAttribute($name): bool;

  /**
   * Returns all storage elements as an array.
   *
   * @return mixed[]
   *   An associative array of attributes.
   */
  public function toArray(): array;

  /**
   * Merges an Attribute object into the current storage.
   *
   * @param HtmlAttributeInterface $collection
   *   The Attribute object to merge.
   *
   * @return $this
   */
  public function merge(HtmlAttributeInterface $collection): HtmlAttributeInterface;

}
