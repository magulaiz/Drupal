<?php

declare(strict_types=1);

namespace Drupal\Tests\system\Functional\Form;

use Drupal\Tests\BrowserTestBase;

/**
 * @covers \Drupal\Core\Recipe\RecipeInputFormTrait
 * @group system
 */
class RecipeFormInputTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['form_test'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests collecting recipe input via a form.
   */
  public function testRecipeInputViaForm(): void {
    $this->drupalGet('/form-test/recipe-input');

    $assert_session = $this->assertSession();
    // There should only be one input element on the page: the one defined
    // by the feedback_contact_form recipe.
    $assert_session->elementsCount('css', 'input[name^="input["]', 1);
    // All recipe inputs are required.
    $this->submitForm([
      'input[feedback_contact_form][recipient]' => '',
    ], 'Apply recipe');
    $assert_session->statusMessageContains('Feedback form email address field is required.', 'error');
  }

}
