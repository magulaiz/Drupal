<?php

declare(strict_types=1);

namespace Drupal\Core\Recipe;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;

/**
 * Defines helper methods for forms which collect input on behalf of recipes.
 */
trait RecipeInputFormTrait {

  protected function getRecipeInputForm(Recipe $recipe): array {
    $collector = new class () implements InputCollectorInterface {

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
