<?php

namespace Drupal\Core\Template;

use Drupal\Component\Utility\NestedArray;

/**
 * Provides a shared implementation of several HtmlAttribute methods.
 *
 * Methods that depend on the storage method are omitted and should be
 * implemented directly.
 */
trait HtmlAttributeTrait {

  /**
   * Implements the magic __toString() method.
   */
  public function __toString() {
    $return = '';
    /** @var \Drupal\Core\Template\AttributeValueBase $value */
    foreach ($this->storage as $value) {
      $rendered = $value->render();
      if ($rendered) {
        $return .= ' ' . $rendered;
      }
    }
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function toArray(): array {
    $return = [];
    foreach ($this->storage as $name => $value) {
      $return[$name] = $value->value();
    }

    return $return;
  }

  /**
   * Returns a representation of the object for use in JSON serialization.
   *
   * @return string
   *   The safe string content.
   */
  public function jsonSerialize(): string {
    return (string) $this;
  }

  /**
   * Merges an Attribute object into the current storage.
   *
   * @param HtmlAttributeInterface $collection
   *   The Attribute object to merge.
   *
   * @return $this
   */
  public function merge(HtmlAttributeInterface $collection): self {
    $merged_attributes = NestedArray::mergeDeep($this->toArray(), $collection->toArray());
    foreach ($merged_attributes as $name => $value) {
      $this->storage[$name] = $this->createAttributeValue($name, $value);
    }
    return $this;
  }

}
