<?php

declare(strict_types=1);

namespace Drupal\field_ui\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a class for a view mode add form.
 *
 * @method \Drupal\Core\Entity\EntityViewModeInterface getEntity()
 */
final class EntityViewModeAddForm extends EntityDisplayModeAddForm {

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
    $saved = parent::save($form, $form_state);

    if ($this->getEntity()->getPath() !== NULL) {
      $this->rebuildRoute();
      $this->setPageDisplayOnViewDisplays($this->getEntity()->getTargetType(), $this->getEntity()->getMode(), TRUE);
    }

    return $saved;
  }

}
