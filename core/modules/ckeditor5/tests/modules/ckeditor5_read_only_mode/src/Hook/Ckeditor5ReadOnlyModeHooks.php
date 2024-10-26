<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_read_only_mode\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;

class Ckeditor5ReadOnlyModeHooks {

  /**
   * Implements hook_form_alter().
   */
  #[Hook('form_alter', module: 'ckeditor5_read_only_mode_form_node_page')]
  public function formNodePageFormAlter(array &$form, FormStateInterface $form_state, string $form_id) : void {
    $form['body']['#disabled'] = \Drupal::state()->get('ckeditor5_read_only_mode_body_enabled', \FALSE);
    $form['field_second_ckeditor5_field']['#disabled'] = \Drupal::state()->get('ckeditor5_read_only_mode_second_ckeditor5_field_enabled', \FALSE);
  }

}
