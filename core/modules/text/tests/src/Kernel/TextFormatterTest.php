<?php

namespace Drupal\Tests\text\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\filter\Entity\FilterFormat;
use Drupal\filter\FilterFormatInterface;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * Tests the text formatters functionality.
 *
 * @group text
 */
class TextFormatterTest extends EntityKernelTestBase {

  /**
   * The entity type used in this test.
   *
   * @var string
   */
  protected $entityType = 'entity_test';

  /**
   * The bundle used in this test.
   *
   * @var string
   */
  protected $bundle = 'entity_test';

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['text'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    FieldStorageConfig::create([
      'field_name' => 'formatted_text',
      'entity_type' => $this->entityType,
      // A field type supported by all formatters under test.
      'type' => 'text_with_summary',
      'settings' => [],
    ])->save();
    FieldConfig::create([
      'entity_type' => $this->entityType,
      'bundle' => $this->bundle,
      'field_name' => 'formatted_text',
      'label' => 'Filtered text',
    ])->save();
  }

  /**
   * Tests the default text field formatter functionality.
   */
  public function testDefaultFormatter() {
    $autop_filter = FilterFormat::create([
      'format' => 'my_text_format',
      'name' => 'My text format',
      'filters' => [
        'filter_autop' => [
          'module' => 'filter',
          'status' => TRUE,
        ],
      ],
    ]);
    $autop_filter->save();

    $this->assertFormattedMarkup(
      'Hello, world!',
       "<p>Hello, world!</p>\n",
      $autop_filter,
      ['type' => 'text_default'],
    );
  }

  /**
   * Tests the functionality of field formatters that allow trimming.
   */
  public function testTrimmedFormatters() {
    $formatters = [
      'text_trimmed',
      'text_summary_or_trimmed',
    ];

    $text = 'This should <iframe>only be</iframe> allowed for administrators.';

    $null_filter = FilterFormat::create([
      'format' => 'null_filter',
      'name' => 'NULL filter',
      'filters' => [],
    ]);
    $null_filter->save();

    foreach ($formatters as $formatter) {
      $this->assertFormattedMarkup(
        $text,
        $text,
        $null_filter,
        ['type' => $formatter]
      );
      // Ensure HTML markup doesn't get removed when trimmed.
      $this->assertFormattedMarkup(
        $text,
        'This should <iframe>only be</iframe> allowed for a',
        $null_filter,
        ['type' => $formatter, 'settings' => ['trim_length' => 50]],
      );
    }
  }

  /**
   * Asserts the markup of a formatted text field.
   *
   * @param string $text
   *   The field text.
   * @param string $expected
   *   The expected markup.
   * @param \Drupal\filter\FilterFormatInterface $filter_format
   *   A configured filter format.
   * @param array $display_options
   *   The display options of the field.
   */
  protected function assertFormattedMarkup(
    string $text,
    string $expected,
    FilterFormatInterface $filter_format,
    array $display_options
  ): void {
    // Create the entity to be referenced.
    $entity = $this->entityTypeManager
      ->getStorage($this->entityType)
      ->create(['name' => $this->randomMachineName()]);
    $entity->formatted_text = [
      'value' => $text,
      'format' => $filter_format_id = $filter_format->id(),
    ];
    $entity->save();

    if (!isset($display_options['type'])) {
      throw new \InvalidArgumentException('The formatter is not set.');
    }
    $formatter = $display_options['type'];

    // Verify the text field formatter's render array.
    $build = $entity->get('formatted_text')->view($display_options);
    \Drupal::service('renderer')->renderRoot($build[0]);
    $this->assertSame($expected, (string) $build[0]['#markup']);
    $this->assertEquals(
      FilterFormat::load($filter_format_id)->getCacheTags(),
      $build[0]['#cache']['tags'],
      "The $formatter formatter has not the expected cache tags when formatting a formatted text field."
    );
  }

}
