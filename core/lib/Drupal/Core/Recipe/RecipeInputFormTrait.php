<?php

declare(strict_types=1);

namespace Drupal\Core\Recipe;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\TypedData\DataDefinitionInterface;

/**
 * Defines helper methods for forms which collect input on behalf of recipes.
 */
trait RecipeInputFormTrait {

  protected function getRecipeInputForm(Recipe $recipe): array {
    $collector = new class implements InputCollectorInterface {

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
          NestedArray::setValue($this->form, explode('.', $name), $element);
        }
        return $default_value;
      }

    };
    $recipe->input->collectAll($collector);
    if ($collector->form) {
      $collector->form['#tree'] = TRUE;
    }
    return $collector->form;
  }

}
