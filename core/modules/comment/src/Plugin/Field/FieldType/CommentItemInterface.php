<?php

namespace Drupal\comment\Plugin\Field\FieldType;

/**
 * Interface definition for Comment items.
 */
interface CommentItemInterface {

  /**
   * Comments for this entity are hidden.
   */
  const HIDDEN = 0;

  /**
   * Comments for this entity are closed.
   */
  const CLOSED = 1;

  /**
   * Comments for this entity are open.
   */
  const OPEN = 2;

  /**
   * Comment form should be displayed on a separate page.
   */
  const FORM_SEPARATE_PAGE = 0;

  /**
   * Comment form should be shown below post or list of comments.
   */
  const FORM_BELOW = 1;

  /**
   * Comment preview is disabled.
   */
  const PREVIEW_DISABLED = 0;

  /**
   * Comment preview is optional.
   */
  const PREVIEW_OPTIONAL = 1;

  /**
   * Comment preview is required.
   */
  const PREVIEW_REQUIRED = 2;

}
