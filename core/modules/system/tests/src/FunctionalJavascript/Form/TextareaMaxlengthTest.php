<?php

namespace Drupal\Tests\system\FunctionalJavascript\Form;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests validation of textarea maxlength properties.
 *
 * @group system
 */
class TextareaMaxlengthTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['form_test'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests that textarea maxlength validation is the same for JS and PHP.
   */
  public function testTextareaMaxlengthValidation(): void {
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();
    $textarea_id = 'edit-textarea';
    $test_text = "abcdefghij\r\nklmnopqrs";

    $this->drupalGet('/form-test/textarea-maxlength');
    $page->fillField($textarea_id, $test_text);
    $page->pressButton('Submit');
    $this->assertSession()->pageTextNotContains('Textarea with maxlength cannot be longer than 20 characters but is currently 21 characters long.');
  }

}
