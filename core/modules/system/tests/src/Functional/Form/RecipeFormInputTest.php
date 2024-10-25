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
    $this->assertSession()->statusCodeEquals(200);
  }

}
