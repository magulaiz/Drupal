<?php

namespace Drupal\comment;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a comment type entity.
 */
interface CommentTypeInterface extends ConfigEntityInterface {

  /**
   * Returns the comment type description.
   *
   * @return string
   *   The comment-type description.
   */
  public function getDescription();

  /**
   * Sets the description of the comment type.
   *
   * @param string $description
   *   The new description.
   *
   * @return $this
   */
  public function setDescription($description);

  /**
   * Gets the target entity type id for this comment type.
   *
   * @return string
   *   The target entity type id.
   */
  public function getTargetEntityTypeId();

  /**
   * Gets the comment submit button label for the comment type.
   *
   * @return string
   *   The label for the comment submit button.
   */
  public function getCommentSubmitButtonLabel();

  /**
   * Sets the comment submit button label for the comment type.
   *
   * @param string $button_label
   *   The label for the comment submit button.
   */
  public function setCommentSubmitButtonLabel($label);

  /**
   * Gets the reply submit button label for the comment type.
   *
   * @return string
   *   The label for the reply submit button.
   */
  public function getReplySubmitButtonLabel();

  /**
   * Sets the reply submit button label for the comment type.
   *
   * @param string $button_label
   *   The label for the reply submit button.
   */
  public function setReplySubmitButtonLabel($button_label);

}
