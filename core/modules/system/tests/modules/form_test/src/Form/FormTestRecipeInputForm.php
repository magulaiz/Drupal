<?php

declare(strict_types=1);

namespace Drupal\form_test\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Recipe\RecipeInputFormTrait;

class FormTestRecipeInputForm extends FormBase {

  use RecipeInputFormTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'form_test_recipe_input';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
  }

}
