<?php

declare(strict_types=1);

namespace Drupal\Tests\system\Functional\Recipe;

use Drupal\contact\Entity\ContactForm;
use Drupal\FunctionalTests\Core\Recipe\RecipeTestTrait;
use Drupal\Tests\BrowserTestBase;

/**
 * Runs a series of generic tests for one recipe.
 */
abstract class GenericRecipeTestBase extends BrowserTestBase {

  use RecipeTestTrait;

  /**
   * {@inheritdoc}
   */
  protected $profile = 'minimal';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Returns the path of the recipe under test.
   *
   * @return string
   *   The absolute path of the recipe that contains this test.
   */
  protected function getRecipePath(): string {
    // Assume this test in located in RECIPE_DIR/tests/src/Functional.
    return dirname((new \ReflectionObject($this))->getFileName(), 4);
  }

  /**
   * Applies the recipe under test.
   */
  protected function doApply(): void {
    $this->applyRecipe($this->getRecipePath());
  }

  /**
   * Tests that this recipe can be applied multiple times.
   */
  public function testRecipeCanBeApplied(): void {
    $this->setUpCurrentUser(admin: TRUE);
    $this->doApply();
    // Apply the recipe again to prove that it is idempotent. Because this
    // recipe takes dynamic input, we have to reset the active version of the
    // form to what the recipe ships with, at least until recipes can do more
    // nuanced comparisons between active config and shipped config.
    ContactForm::load('feedback')?->setRecipients(['admin@example.com'])
      ->save();
    $this->doApply();
  }

}
