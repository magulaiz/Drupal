<?php

declare(strict_types=1);

namespace Drupal\Core\Form;

/**
 * Helper trait to mark forms as workspace-safe.
 */
trait WorkspaceSafeFormTrait {

  /**
   * Determines whether the current form is safe to be submitted in a workspace.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return bool
   *   TRUE if the form is workspace-safe, FALSE otherwise.
   */
  public function isWorkspaceSafeForm(array $form, FormStateInterface $form_state): bool {
    return TRUE;
  }

}
