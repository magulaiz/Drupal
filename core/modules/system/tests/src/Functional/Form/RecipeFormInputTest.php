<?php

declare(strict_types=1);

namespace Drupal\Tests\system\Functional\Form;

use Drupal\contact\Entity\ContactForm;
use Drupal\Tests\BrowserTestBase;
use Drupal\user\Entity\User;

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
    User::load(1)->addRole($this->createAdminRole());

    $this->drupalGet('/form-test/recipe-input');

    $assert_session = $this->assertSession();
    // There should only be one nested input element on the page: the one
    // defined by the feedback_contact_form recipe.
    $assert_session->elementsCount('css', 'input[name*="["]', 1);
    // All recipe inputs are required.
    $this->submitForm([
      'feedback_contact_form[recipient]' => '',
    ], 'Apply recipe');
    $assert_session->statusMessageContains('Feedback form email address field is required.', 'error');
    // Submit the form with a valid value and apply the recipe, to prove that
    // it was passed through correctly.
    $this->submitForm([
      'feedback_contact_form[recipient]' => 'it.works@drupal.test',
    ], 'Apply recipe');

    $this->resetAll();
    $this->assertContains('it.works@drupal.test', ContactForm::load('feedback')->getRecipients());
  }

}
