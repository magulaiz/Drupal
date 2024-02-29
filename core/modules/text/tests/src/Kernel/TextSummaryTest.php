<?php

namespace Drupal\Tests\text\Kernel;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Form\FormState;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\filter\Render\FilteredMarkup;
use Drupal\KernelTests\KernelTestBase;
use Drupal\filter\Entity\FilterFormat;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * Tests text_summary() with different strings and lengths.
 *
 * @group text
 */
class TextSummaryTest extends KernelTestBase {

  use UserCreationTrait;

  protected static $modules = [
    'system',
    'user',
    'filter',
    'text',
    'field',
    'field_ui',
    'entity_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig(['text']);
  }

  /**
   * Tests text summaries for a question followed by a sentence.
   */
  public function testFirstSentenceQuestion() {
    $text = 'A question? A sentence. Another sentence.';
    $expected = 'A question? A sentence.';
    $this->assertTextSummary($text, $expected, NULL, 30);
  }

  /**
   * Tests summary with long example.
   */
  public function testLongSentence() {
    // 125.
    // cSpell:disable
    $text =
      'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. ' .
      // 108.
      'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. ' .
      // 103.
      'Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. ' .
      // 110.
      'Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';
    $expected = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. ' .
                'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. ' .
                'Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.';
    // cSpell:enable
    // First three sentences add up to: 336, so add one for space and then 3 to get half-way into next word.
    $this->assertTextSummary($text, $expected, NULL, 340);
  }

  /**
   * Tests various summary length edge cases.
   */
  public function testLength() {
    FilterFormat::create([
      'format' => 'autop',
      'name' => 'Autop',
      'filters' => [
        'filter_autop' => [
          'status' => 1,
        ],
      ],
    ])->save();
    FilterFormat::create([
      'format' => 'autop_correct',
      'name' => 'Autop correct',
      'filters' => [
        'filter_autop' => [
          'status' => 1,
        ],
        'filter_htmlcorrector' => [
          'status' => 1,
        ],
      ],
    ])->save();

    // This string tests a number of edge cases.
    $text = "<p>\nHi\n</p>\n<p>\nfolks\n<br />\n!\n</p>";

    // The summaries we expect text_summary() to return when $size is the index
    // of each array item.
    // Using no text format:
    $format = NULL;
    $i = 0;
    $this->assertTextSummary($text, "<p>\nHi\n</p>\n<p>\nfolks\n<br />\n!\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<", $format, $i++);
    $this->assertTextSummary($text, "<p", $format, $i++);
    $this->assertTextSummary($text, "<p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\n", $format, $i++);
    $this->assertTextSummary($text, "<p>\nH", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n<", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>\n<p>\nfolks\n<br />\n!\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>\n<p>\nfolks\n<br />\n!\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>\n<p>\nfolks\n<br />\n!\n</p>", $format, $i++);

    // Using a text format with filter_autop enabled.
    $format = 'autop';
    $i = 0;
    $this->assertTextSummary($text, "<p>\nHi\n</p>\n<p>\nfolks\n<br />\n!\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<", $format, $i++);
    $this->assertTextSummary($text, "<p", $format, $i++);
    $this->assertTextSummary($text, "<p>", $format, $i++);
    $this->assertTextSummary($text, "<p>", $format, $i++);
    $this->assertTextSummary($text, "<p>", $format, $i++);
    $this->assertTextSummary($text, "<p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>\n<p>\nfolks\n<br />\n!\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>\n<p>\nfolks\n<br />\n!\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>\n<p>\nfolks\n<br />\n!\n</p>", $format, $i++);

    // Using a text format with filter_autop and filter_htmlcorrector enabled.
    $format = 'autop_correct';
    $i = 0;
    $this->assertTextSummary($text, "<p>\nHi\n</p>\n<p>\nfolks\n<br />\n!\n</p>", $format, $i++);
    $this->assertTextSummary($text, "", $format, $i++);
    $this->assertTextSummary($text, "<p></p>", $format, $i++);
    $this->assertTextSummary($text, "<p></p>", $format, $i++);
    $this->assertTextSummary($text, "<p></p>", $format, $i++);
    $this->assertTextSummary($text, "<p></p>", $format, $i++);
    $this->assertTextSummary($text, "<p></p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>\n<p>\nfolks\n<br />\n!\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>\n<p>\nfolks\n<br />\n!\n</p>", $format, $i++);
    $this->assertTextSummary($text, "<p>\nHi\n</p>\n<p>\nfolks\n<br />\n!\n</p>", $format, $i++);
  }

  /**
   * Tests text summaries with an invalid filter format.
   *
   * @see text_summary()
   */
  public function testInvalidFilterFormat() {

    $this->assertTextSummary($this->randomString(100), '', 'non_existent_format');
  }

  /**
   * Calls text_summary() and asserts that the expected teaser is returned.
   *
   * @internal
   */
  public function assertTextSummary(string $text, string $expected, ?string $format = NULL, int $size = NULL): void {
    $summary = text_summary($text, $format, $size);
    $this->assertSame($expected, $summary, '<pre style="white-space: pre-wrap">' . $summary . '</pre> is identical to <pre style="white-space: pre-wrap">' . $expected . '</pre>');
  }

  /**
   * Tests required summary.
   *
   * @param bool $display_summary
   *   TRUE if a checkbox "Summary input" is checked.
   *   This allows authors to input an explicit summary, to be displayed
   *   instead of the automatically trimmed text.
   *   (for example, it can be set on a page
   *    admin/structure/types/manage/article/fields/node.article.body)
   * @param bool $require_summary
   *   TRUE if a checkbox "Require summary" is checked.
   *   This make a summary field required when a text is edited by an author.
   *   (for example, it can be set on a page
   *    admin/structure/types/manage/article/fields/node.article.body)
   * @param array $expected_entity_form
   *   An expected state of an entity form when certain
   *   display_summary and require_summary values are provided.
   *   The summary is used here as a regular widget
   *   (for example on a page node/add/article)
   *   An associative array with the following keys:
   *    - display_type: (string) A summary field's type
   *      (equals to 'textarea' or 'value').
   *    - require: (bool) TRUE if a summary field is required.
   *    - violations: (int) Number of violations after validation.
   *    - violation_path: (string) A property path from the root element
   *      to the violation or the form element which raised an error.
   *    - violation_message: (string) A message associated with the violation.
   * @param array $expected_field_config_form
   *   An expected state of a field config form when certain
   *   display_summary and require_summary are provided.
   *   The summary is used here as a default value widget.
   *   (for example on a page
   *    admin/structure/types/manage/article/fields/node.article.body)
   *   An associative array with the following keys:
   *    - display_type: (string) A summary field's type
   *      (equals to 'textarea' or 'value').
   *    - require: (bool) TRUE if a summary field is required.
   *    - violations: (int) Number of violations after validation.
   *    - violation_path: (string) A property path from the root element
   *      to the violation or the form element which raised an error.
   *    - violation_message: (string) A message associated with the violation.
   *
   * @dataProvider providerTestRequiredSummary
   */
  public function testRequiredSummary(bool $display_summary, bool $require_summary, array $expected_entity_form, array $expected_field_config_form) {
    $this->installEntitySchema('entity_test');
    $this->setUpCurrentUser();
    $field_definition = FieldStorageConfig::create([
      'field_name' => 'test_textwithsummary',
      'type' => 'text_with_summary',
      'entity_type' => 'entity_test',
      'cardinality' => 1,
      'settings' => [
        'max_length' => 200,
      ],
    ]);
    $field_definition->save();

    $instance = FieldConfig::create([
      'field_name' => 'test_textwithsummary',
      'label' => 'A text field',
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
      'settings' => [
        'text_processing' => TRUE,
        'display_summary' => $display_summary,
        'required_summary' => $require_summary,
      ],
    ]);
    $instance->save();

    EntityFormDisplay::create([
      'targetEntityType' => 'entity_test',
      'bundle' => 'entity_test',
      'mode' => 'default',
      'status' => TRUE,
    ])->setComponent('test_textwithsummary', [
      'type' => 'text_textarea_with_summary',
      'settings' => [
        'summary_rows' => 2,
        'show_summary' => TRUE,
      ],
    ])
      ->save();

    // Check the required summary.
    // Create a test entity with a 'text with summary' field.
    // Fill in a text field and leave its summary empty.
    $entity = EntityTest::create([
      'name' => $this->randomMachineName(),
      'type' => 'entity_test',
      'test_textwithsummary' => ['value' => $this->randomMachineName()],
    ]);

    // Check the state of a summary field when an entity form is displayed.
    // If an author edits a text its summary can be visible as a textarea
    // or hidden as a value.
    $form = \Drupal::service('entity.form_builder')->getForm($entity);
    $this->assertEquals($expected_entity_form['display_type'], $form['test_textwithsummary']['widget'][0]['summary']['#type']);
    $this->assertEquals($expected_entity_form['require'], !empty($form['test_textwithsummary']['widget'][0]['summary']['#required']));

    // Test entity form validation.
    /** @var \Symfony\Component\Validator\ConstraintViolation[] $violations */
    $violations = $entity->validate();
    $violation_path = !count($violations) ? '' : $violations[0]->getPropertyPath();
    $violation_message = !count($violations) ? '' : $violations[0]->getMessage();
    $this->assertCount($expected_entity_form['violations'], $violations);
    $this->assertEquals($expected_entity_form['violation_path'], $violation_path);
    $this->assertEquals($expected_entity_form['violation_message'], $violation_message);

    // Check the default summary field is not required in a field config form.
    $form_object = \Drupal::entityTypeManager()->getFormObject($instance->getEntityTypeId(), 'edit');
    $form_object->setEntity($instance);
    $form = \Drupal::formBuilder()->getForm($form_object);
    $this->assertEquals($expected_field_config_form['display_type'], $form['default_value']['widget'][0]['summary']['#type']);
    $this->assertEquals($expected_field_config_form['require'], !empty($form['default_value']['widget'][0]['summary']['#required']));

    // Test a field config form validation.
    $form_state = (new FormState())->setFormState([]);
    \Drupal::formBuilder()->submitForm($form_object, $form_state);
    $errors = $form_state->getErrors();
    $error_key = !count($errors) ? '' : key($errors);
    $error_message = !count($errors) ? '' : $errors[$error_key];
    $this->assertCount($expected_field_config_form['violations'], $errors);
    $this->assertEquals($expected_field_config_form['violation_path'], $error_key);
    $this->assertEquals($expected_field_config_form['violation_message'], $error_message);
  }

  /**
   * Provides test data for testRequiredSummary().
   */
  public function providerTestRequiredSummary(): array {
    return [
      [
        'display_summary' => TRUE,
        'require_summary' => TRUE,
        'expected_entity_form' => [
          'display_type' => 'textarea',
          'require' => TRUE,
          'violations' => 1,
          'violation_path' => 'test_textwithsummary.0.summary',
          'violation_message' => 'The summary field is required for A text field',
        ],
        'expected_field_config_form' => [
          'display_type' => 'textarea',
          'require' => FALSE,
          'violations' => 0,
          'violation_path' => '',
          'violation_message' => '',
        ],
      ],
      [
        'display_summary' => TRUE,
        'require_summary' => FALSE,
        'expected_entity_form' => [
          'display_type' => 'textarea',
          'require' => FALSE,
          'violations' => 0,
          'violation_path' => '',
          'violation_message' => '',
        ],
        'expected_field_config_form' => [
          'display_type' => 'textarea',
          'require' => FALSE,
          'violations' => 0,
          'violation_path' => '',
          'violation_message' => '',
        ],
      ],
      [
        'display_summary' => FALSE,
        'require_summary' => TRUE,
        'expected_entity_form' => [
          'display_type' => 'value',
          'require' => FALSE,
          'violations' => 0,
          'violation_path' => '',
          'violation_message' => '',
        ],
        'expected_field_config_form' => [
          'display_type' => 'value',
          'require' => FALSE,
          'violations' => 1,
          'violation_path' => 'settings][required_summary',
          'violation_message' => 'If "Require summary" is checked "Summary input" has to be checked as well.',
        ],
      ],
      [
        'display_summary' => FALSE,
        'require_summary' => FALSE,
        'expected_entity_form' => [
          'display_type' => 'value',
          'require' => FALSE,
          'violations' => 0,
          'violation_path' => '',
          'violation_message' => '',
        ],
        'expected_field_config_form' => [
          'display_type' => 'value',
          'require' => FALSE,
          'violations' => 0,
          'violation_path' => '',
          'violation_message' => '',
        ],
      ],
    ];
  }

  /**
   * Test text normalization when filter_html or filter_htmlcorrector enabled.
   */
  public function testNormalization() {
    FilterFormat::create([
      'format' => 'filter_html_enabled',
      'name' => 'Filter HTML enabled',
      'filters' => [
        'filter_html' => [
          'status' => 1,
          'settings' => [
            'allowed_html' => '<strong>',
          ],
        ],
      ],
    ])->save();
    FilterFormat::create([
      'format' => 'filter_htmlcorrector_enabled',
      'name' => 'Filter HTML corrector enabled',
      'filters' => [
        'filter_htmlcorrector' => [
          'status' => 1,
        ],
      ],
    ])->save();
    FilterFormat::create([
      'format' => 'neither_filter_enabled',
      'name' => 'Neither filter enabled',
      'filters' => [],
    ])->save();

    $filtered_markup = FilteredMarkup::create('<div><strong><span>Hello World</span></strong></div>');
    // With either HTML filter enabled, text_summary() will normalize the text
    // using HTML::normalize().
    $summary = text_summary($filtered_markup, 'filter_html_enabled', 30);
    $this->assertStringContainsString('<div><strong><span>', $summary);
    $this->assertStringContainsString('</span></strong></div>', $summary);
    $summary = text_summary($filtered_markup, 'filter_htmlcorrector_enabled', 30);
    $this->assertStringContainsString('<div><strong><span>', $summary);
    $this->assertStringContainsString('</span></strong></div>', $summary);
    // If neither filter is enabled, the text will not be normalized.
    $summary = text_summary($filtered_markup, 'neither_filter_enabled', 30);
    $this->assertStringContainsString('<div><strong><span>', $summary);
    $this->assertStringNotContainsString('</span></strong></div>', $summary);
  }

}
