<?php

declare(strict_types=1);

namespace Drupal\form_test\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Recipe\Recipe;
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
    $recipe = Recipe::createFromDirectory('core/recipes/standard');
    $form['input'] = $this->getRecipeInputForm($recipe);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
  }

}
