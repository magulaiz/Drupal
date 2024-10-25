<?php

declare(strict_types=1);

namespace Drupal\form_test\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Recipe\Recipe;
use Drupal\Core\Recipe\RecipeInputFormTrait;
use Drupal\Core\Recipe\RecipeRunner;

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
    $recipe = Recipe::createFromDirectory('core/recipes/feedback_contact_form');
    $form += $this->getRecipeInputForm($recipe);

    $form['apply'] = [
      '#type' => 'submit',
      '#value' => $this->t('Apply recipe'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $recipe = Recipe::createFromDirectory('core/recipes/feedback_contact_form');
    $this->validateRecipeInput($recipe, $form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $recipe = Recipe::createFromDirectory('core/recipes/feedback_contact_form');
    $this->setRecipeInput($recipe, $form_state);
    RecipeRunner::processRecipe($recipe);
  }

}
