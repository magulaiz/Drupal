<?php

namespace Drupal\comment\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\comment\CommentTypeInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the comment type entity.
 *
 * @ConfigEntityType(
 *   id = "comment_type",
 *   label = @Translation("Comment type"),
 *   label_singular = @Translation("comment type"),
 *   label_plural = @Translation("comment types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count comment type",
 *     plural = "@count comment types",
 *   ),
 *   handlers = {
 *     "form" = {
 *       "default" = "Drupal\comment\CommentTypeForm",
 *       "add" = "Drupal\comment\CommentTypeForm",
 *       "edit" = "Drupal\comment\CommentTypeForm",
 *       "delete" = "Drupal\comment\Form\CommentTypeDeleteForm"
 *     },
 *     "list_builder" = "Drupal\comment\CommentTypeListBuilder"
 *   },
 *   admin_permission = "administer comment types",
 *   config_prefix = "type",
 *   bundle_of = "comment",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "delete-form" = "/admin/structure/comment/manage/{comment_type}/delete",
 *     "edit-form" = "/admin/structure/comment/manage/{comment_type}",
 *     "add-form" = "/admin/structure/comment/types/add",
 *     "collection" = "/admin/structure/comment",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "target_entity_type_id",
 *     "description",
 *     "button_labels",
 *   }
 * )
 */
class CommentType extends ConfigEntityBundleBase implements CommentTypeInterface {

  /**
   * The comment type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The comment type label.
   *
   * @var string
   */
  protected $label;

  /**
   * The description of the comment type.
   *
   * @var string
   */
  protected $description;

  /**
   * The target entity type.
   *
   * @var string
   */
  protected $target_entity_type_id;

  /**
   * An array of submit button labels.
   *
   * The keys are 'submit_comment' and 'submit_reply' and the values are the
   * labels of the submit button for a top-level comment and a comment reply,
   * respectively.
   *
   * @var string[]
   */
  protected $button_labels = [
    'submit_comment' => '',
    'submit_reply' => '',
  ];

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->description = $description;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTargetEntityTypeId() {
    return $this->target_entity_type_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getCommentSubmitButtonLabel() {
    return $this->button_labels['submit_comment'] ?: new TranslatableMarkup('Save');
  }

  /**
   * {@inheritdoc}
   */
  public function setCommentSubmitButtonLabel($button_label) {
    $this->button_labels['submit_comment'] = $button_label;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getReplySubmitButtonLabel() {
    return $this->button_labels['submit_reply'] ?: new TranslatableMarkup('Save');
  }

  /**
   * {@inheritdoc}
   */
  public function setReplySubmitButtonLabel($button_label) {
    $this->button_labels['submit_reply'] = $button_label;
    return $this;
  }

}
