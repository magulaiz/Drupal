<?php

namespace Drupal\Core\TypedData\Options;

use Drupal\Core\TypedData\OptionsProviderInterface;
use Drupal\Core\TypedData\TypedDataInterface;

/**
 * Interface for option providers that vary depending on current data values.
 *
 * Option providers that want to differentiate based on current data values may
 * implement this interface in order to receive optionally provided data
 * objects. However, note that current data value is provided optionally and
 * implementing classes must be able to provide options if no data value has
 * been set.
 */
interface DependentOptionsProviderInterface extends OptionsProviderInterface {

  /**
   * Sets the data object to provide options for.
   *
   * @param \Drupal\Core\TypedData\TypedDataInterface|null $data
   *   (optional) The data object to set, or NULL if no data value is available.
   *
   * @return $this
   */
  public function setData(?TypedDataInterface $data = NULL);

}
