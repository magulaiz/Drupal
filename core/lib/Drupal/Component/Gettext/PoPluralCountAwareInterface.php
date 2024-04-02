<?php

namespace Drupal\Component\Gettext;

/**
 * Interface for all Gettext PO elements that need to know about plurals.
 */
interface PoPluralCountAwareInterface {

  /**
   * Gets the plural count.
   *
   * @return int
   *   The plural count.
   */
  public function getPluralCount(): int;

  /**
   * Sets the plural count.
   *
   * @param int $pluralCount
   *   The plural count.
   *
   * @return self
   */
  public function setPluralCount(int $pluralCount);

}
