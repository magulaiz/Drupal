<?php

namespace Drupal\Core\Plugin\Context;

use Drupal\Component\Plugin\Context\ContextDefinitionInterface as ComponentContextDefinitionInterface;

/**
 * Interface to define definition objects in ContextInterface via TypedData.
 *
 * @see \Drupal\Component\Plugin\Context\ContextDefinitionInterface
 * @see \Drupal\Core\Plugin\Context\ContextInterface
 */
interface ContextDefinitionInterface extends ComponentContextDefinitionInterface {

  /**
   * Returns an options provider if there are defined options.
   *
   * @param \Drupal\Core\Plugin\Context\ContextInterface[] $contexts
   *   (optional) The array of contexts to which the defined context belongs.
   *
   * @return \Drupal\Core\TypedData\OptionsProviderInterface|null
   *   The options provider, or NULL if no options are defined.
   *
   * @see ::getOptionsProviderDefinition()
   */
  public function getOptionsProvider(?array $contexts = NULL);

  /**
   * Returns the set options provider definition.
   *
   * @return string|null
   *   The options provider definition, or NULL if no options provider has been
   *   defined.
   *
   * @see ::getOptionsProvider()
   */
  public function getOptionsProviderDefinition();

  /**
   * Returns the data definition of the defined context.
   *
   * @return \Drupal\Core\TypedData\DataDefinitionInterface
   *   The data definition object.
   */
  public function getDataDefinition();

  /**
   * Determines if this definition is satisfied by a context object.
   *
   * @param \Drupal\Core\Plugin\Context\ContextInterface $context
   *   The context object.
   *
   * @return bool
   *   TRUE if this definition is satisfiable by the context object, FALSE
   *   otherwise.
   */
  public function isSatisfiedBy(ContextInterface $context);

}
