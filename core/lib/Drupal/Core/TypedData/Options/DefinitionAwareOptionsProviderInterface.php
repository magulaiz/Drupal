<?php

namespace Drupal\Core\TypedData\Options;

use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\OptionsProviderInterface;

/**
 * Interface for option providers that are aware of their definition objects.
 *
 * Being aware of the data definition allows option providers to return
 * different options based on definition settings or data type.
 */
interface DefinitionAwareOptionsProviderInterface extends OptionsProviderInterface {

  /**
   * Sets the data definition.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $definition
   *   The data definition where the options provider is defined.
   *
   * @return $this
   */
  public function setDataDefinition(DataDefinitionInterface $definition);

}
