<?php

declare(strict_types=1);

namespace Drupal\field_ui\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a class for a view mode edit form.
 */
final class EntityViewModeEditForm extends EntityDisplayModeEditForm {

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
    $original_path = $this->entity->getPath();
    $this->markRouteRebuild($form_state->getValue('path') !== $original_path);
    $saved = parent::save($form, $form_state);
    if ($original_path !== NULL && $this->entity->getPath() !== NULL) {
      // We already had a page display, no need to update entity_view_displays.
      return;
    }
    [, $view_mode] = \explode('.', $this->entity->id());
    $this->setPageDisplayOnViewDisplays($this->entity->getTargetType(), $view_mode, $original_path === NULL);
    return $saved;
  }

}
