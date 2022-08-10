<?php

namespace Drupal\Core\Plugin\Context;

use Drupal\Component\Plugin\Context\ContextInterface as ComponentContextInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\ObjectWithRefinableCacheabilityInterface;

/**
 * Context data and definitions for plugins supporting caching and return docs.
 *
 * @see \Drupal\Component\Plugin\Context\ContextInterface
 * @see \Drupal\Core\Plugin\Context\ContextDefinitionInterface
 */
interface ContextInterface extends ComponentContextInterface, CacheableDependencyInterface, ObjectWithRefinableCacheabilityInterface {

  /**
   * {@inheritdoc}
   *
   * @return \Drupal\Core\Plugin\Context\ContextDefinitionInterface
   */
  public function getContextDefinition();

  /**
   * Gets the context value as typed data object.
   *
   * @return \Drupal\Core\TypedData\TypedDataInterface
   */
  public function getContextData();

  /**
   * Creates a new context with a different value.
   *
   * @param \Drupal\Core\Plugin\Context\ContextInterface $old_context
   *   The context object used to create a new object. Cacheability metadata
   *   will be copied over.
   * @param mixed $value
   *   The value of the new context object.
   *
   * @return static
   */
  public static function createFromContext(ContextInterface $old_context, $value);

}
