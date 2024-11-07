<?php

namespace Drupal\Tests\language\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Core\Form\FormStateInterface;

/**
 * Tests language form alterations.
 *
 * @group language
 */
class LanguageFormAlterTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'field',
    'language',
    'language_entity_field_access_test',
  ];

  /**
   * The theme to use for the tests.
   *
   * @var string
   */
  protected $defaultTheme = 'stark';

  /**
   * Test language_form_alter when #access is undefined.
   */
  public function testLanguageFormAlterUndefinedAccess() {
    // Create a new node entity.
    $node = Node::create([
      'type' => 'article',
      'title' => 'Test Node',
    ]);
    $node->save();

    // Create a form with no #access key for langcode.
    $form = [
      'langcode' => [
        '#type' => 'select',
        '#title' => 'Language',
        '#options' => ['en' => 'English', 'fr' => 'French'],
      ],
    ];

    // Create a mock form state object.
    $form_state = $this->createMock(FormStateInterface::class);
    
    // Mock the getFormObject method to return a mock content entity form object.
    $form_object = $this->createMock('Drupal\Core\Entity\ContentEntityFormInterface');
    $form_object->method('getEntity')->willReturn($node);
    
    // Set the mock form object to the form state.
    $form_state->method('getFormObject')->willReturn($form_object);

    // Call the form alter function directly from the module handler.
    \Drupal::moduleHandler()->alter('form', $form, $form_state);

    // Assert that the #access key is not set in the form array.
    $this->assertArrayNotHasKey('#access', $form['langcode'], 'The #access key should not be set in the form array.');
  }
}
