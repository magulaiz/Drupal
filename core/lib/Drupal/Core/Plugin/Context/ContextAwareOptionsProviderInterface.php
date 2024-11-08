<?php

namespace Drupal\Core\Plugin\Context;

use Drupal\Core\TypedData\OptionsProviderInterface;

/**
 * Interface for option providers depending on the available contexts.
 *
 * Option providers that want to differentiate based on available contexts may
 * implement this interface. However, note that the available contexts are
 * provided optionally and the provider must be able to provide options if no
 * data value has been set.
 */
interface ContextAwareOptionsProviderInterface extends OptionsProviderInterface {

  /**
   * Sets the available contexts for which to provide options.
   *
   * @param \Drupal\Core\Plugin\Context\ContextInterface[] $contexts
   *   (optional) The array of available contexts.
   *
   * @return $this
   */
  public function setContexts(?array $contexts = NULL);

}
