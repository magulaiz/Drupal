<?php

namespace Drupal\Core\Entity;

/**
 * Provides an interface defining an entity view mode entity type.
 */
interface EntityViewModeInterface extends EntityDisplayModeInterface {

  /**
   * Gets the path for this view mode.
   *
   * For example a summary view mode might appear at {canonical_url}/summary. In
   * that case, this method would return 'summary'
   *
   * @return string|null
   *   Path or NULL if this view mode does not have a page display.
   */
  public function getPath(): ?string;

  /**
   * Sets value of Path.
   *
   * @param string|null $path
   *   Value for Path.
   *
   * @return $this
   */
  public function setPath(?string $path): static;

}
