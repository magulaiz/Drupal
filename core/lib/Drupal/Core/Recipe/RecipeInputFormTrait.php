<?php

declare(strict_types=1);

namespace Drupal\Core\Recipe;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * Defines helper methods for forms which collect input on behalf of recipes.
 */
trait RecipeInputFormTrait {

  protected function getRecipeInputForm(Recipe $recipe): array {
    $collector = new class () implements InputCollectorInterface {

      // phpcs:ignore DrupalPractice.CodeAnalysis.VariableAnalysis.UnusedVariable
      public array $form = [];

      /**
       * {@inheritdoc}
       */
      public function collectValue(string $name, DataDefinitionInterface $definition, mixed $default_value): mixed {
        $element = $definition->getSetting('form');
        if ($element) {
          $element += [
            '#description' => $definition->getDescription(),
            '#default_value' => $default_value,
          ];
          // Recipe inputs are always required.
          $element['#required'] = TRUE;
          NestedArray::setValue($this->form, explode('.', $name, 2), $element);

          // Always return the input elements as a tree.
          $this->form['#tree'] = TRUE;
        }
        return $default_value;
      }

    };
    $recipe->input->collectAll($collector);
    return $collector->form;
  }

  protected function validateRecipeInput(Recipe $recipe, array &$form, FormStateInterface $form_state): void {
    try {
      $this->setRecipeInput($recipe, $form_state);
    }
    catch (ValidationFailedException $e) {
      $data = $e->getValue();
      assert($data instanceof TypedDataInterface);

      $element = NestedArray::getValue($form, explode('.', $data->getName(), 2));
      $form_state->setError($element, $e->getMessage());
    }
  }

  protected function setRecipeInput(Recipe $recipe, FormStateInterface $form_state): void {
    $recipe->input->collectAll(new class ($form_state) implements InputCollectorInterface {

      public function __construct(private readonly FormStateInterface $formState) {
      }

      /**
       * {@inheritdoc}
       */
      public function collectValue(string $name, DataDefinitionInterface $definition, mixed $default_value): mixed {
        return $this->formState->getValue(explode('.', $name, 2), $default_value);
      }

    });
  }

}
