<?php

namespace Drupal\navigation\Form;

use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Url;
use Drupal\navigation\NavigationBlockRepositoryInterface;

/**
 * Provides a deletion confirmation form for navigation blocks.
 */
class NavigationBlockDeleteForm extends EntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('navigation_block.admin_display');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Remove');
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $entity = $this->getEntity();
    $regions = [
      NavigationBlockRepositoryInterface::REGION_CONTENT => $this->t('Content'),
      NavigationBlockRepositoryInterface::REGION_FOOTER => $this->t('Footer'),
    ];
    return $this->t('Are you sure you want to remove the @entity-type %label from the %region region?', [
      '@entity-type' => $entity->getEntityType()->getSingularLabel(),
      '%label' => $entity->label(),
      '%region' => $regions[$entity->getRegion()],
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('This will remove the navigation block placement. You will need to <a href=":url">place it again</a> in order to undo this action.', [
      ':url' => Url::fromRoute('navigation_block.admin_display')->toString(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDeletionMessage() {
    $entity = $this->getEntity();
    $regions = [
      NavigationBlockRepositoryInterface::REGION_CONTENT => $this->t('Content'),
      NavigationBlockRepositoryInterface::REGION_FOOTER => $this->t('Footer'),
    ];
    return $this->t('The @entity-type %label has been removed from the %region region.', [
      '@entity-type' => $entity->getEntityType()->getSingularLabel(),
      '%label' => $entity->label(),
      '%region' => $regions[$entity->getRegion()],
    ]);
  }

}
