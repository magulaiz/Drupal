<?php

declare(strict_types=1);

namespace Drupal\field_ui\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a class for a view mode add form.
 */
final class EntityViewModeAddForm extends EntityDisplayModeAddForm {

  use EntityViewModeFormTrait;

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $this->addPathField($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $saved = parent::save($form, $form_state);
    $this->markRouteRebuild($form_state->getValue('path') !== '');
    if ($form_state->getValue('path') !== '') {
      [, $view_mode] = \explode('.', $this->entity->id());
      $this->setPageDisplayOnViewDisplays($this->entity->getTargetType(), $view_mode, TRUE);
    }
    return $saved;
  }

}
