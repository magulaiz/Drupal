<?php

namespace Drupal\Core\TypedData\Options;

use Drupal\Core\TypedData\DataDefinitionInterface;

/**
 * Trait for implementing the DefinitionAwareOptionsProviderInterface.
 */
trait DefinitionAwareOptionsProviderTrait {

  /**
   * The data definition.
   *
   * @var \Drupal\Core\TypedData\DataDefinitionInterface
   */
  protected $definition;

  /**
   * @see \Drupal\Core\TypedData\Options\DefinitionAwareOptionsProviderInterface::setDataDefinition()
   */
  public function setDataDefinition(DataDefinitionInterface $definition) {
    $this->definition = $definition;
    return $this;
  }

  /**
   * Gets the data definition where the options provider is defined.
   *
   * @return \Drupal\Core\TypedData\DataDefinitionInterface
   *   The data definition.
   */
  public function getDataDefinition() {
    return $this->definition;
  }

}
