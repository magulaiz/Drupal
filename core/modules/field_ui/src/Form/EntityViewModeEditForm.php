<?php

declare(strict_types=1);

namespace Drupal\field_ui\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a class for a view mode edit form.
 *
 * @method \Drupal\Core\Entity\EntityViewModeInterface getEntity()
 */
final class EntityViewModeEditForm extends EntityDisplayModeEditForm {

  use EntityViewModeFormTrait;

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state): array {
    $form = parent::form($form, $form_state);
    $this->addPathField($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): int {
    /** @var \Drupal\Core\Entity\EntityViewModeInterface $original */
    $original = $this->entityTypeManager->getStorage('entity_view_mode')->loadUnchanged($this->getEntity()->id());
    $newPath = $this->getEntity()->getPath();
    $pathChanged = $newPath !== $original->getPath();
    if ($pathChanged) {
      $this->rebuildRoute();
    }

    $saved = parent::save($form, $form_state);
    $this->setPageDisplayOnViewDisplays($this->entity->getTargetType(), $this->getEntity()->getMode(), $newPath !== NULL);

    return $saved;
  }

}
