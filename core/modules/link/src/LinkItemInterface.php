<?php

namespace Drupal\link;

use Drupal\Core\Field\FieldItemInterface;

/**
 * Defines an interface for the link field item.
 */
interface LinkItemInterface extends FieldItemInterface {

  /**
   * Specifies whether the field supports only internal URLs.
   */
  const LINK_INTERNAL = 0x01;

  /**
   * Specifies whether the field supports only external URLs.
   */
  const LINK_EXTERNAL = 0x10;

  /**
   * Specifies whether the field supports both internal and external URLs.
   */
  const LINK_GENERIC = 0x11;

  /**
   * Specifies whether the title field is disabled.
   */
  const TITLE_DISABLED = 0;

  /**
   * Specifies whether the title field is optional.
   */
  const TITLE_OPTIONAL = 1;

  /**
   * Specifies whether the title field is required.
   */
  const TITLE_REQUIRED = 2;

  /**
   * Determines if a link is external.
   *
   * @return bool
   *   TRUE if the link is external, FALSE otherwise.
   */
  public function isExternal();

  /**
   * Gets the URL object.
   *
   * @return \Drupal\Core\Url
   *   Returns a Url object.
   *
   * @throws \InvalidArgumentException
   *   Thrown when there is a problem with field data.
   */
  public function getUrl();

  /**
   * Gets the link title.
   *
   * @return string|null
   *   Returns the link title.
   */
  public function getTitle(): ?string;

}
