<?php

declare(strict_types=1);

namespace Drupal\Tests\link\Kernel;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Tests\field\Kernel\FieldKernelTestBase;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\link\LinkItemInterface;

/**
 * Tests the default 'link' field formatter.
 *
 * The formatter is tested with several forms of complex query parameters. And
 * each form is tested with different display settings.
 *
 * @group link
 */
abstract class LinkFormatterDisplayTestBase extends FieldKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['link'];

  /**
   * Tests case name.
   *
   * @var string
   */
  protected string $caseName;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    FieldStorageConfig::create([
      'entity_type' => 'entity_test',
      'field_name' => 'field_test',
      'type' => 'link',
    ])->save();

    FieldConfig::create([
      'entity_type' => 'entity_test',
      'field_name' => 'field_test',
      'bundle' => 'entity_test',
      'settings' => [
        'title' => DRUPAL_OPTIONAL,
        'link_type' => LinkItemInterface::LINK_GENERIC,
      ],
    ])->save();
  }

  /**
   * Test rendered entity field with complex internal URL.
   *
   * @param array $expected_results
   *   Render result using these display settings.
   * @param string $output
   *   Rendered field.
   */
  protected function checkLinksRender(array $expected_results, string $output): void {
    // Check results.
    foreach ($expected_results as $expected_result) {
      $this->assertStringContainsString($expected_result, $output, 'Test case failed: ' . $this->caseName);

      // With url_plain should be no links.
      if (!empty($display_settings['url_only']) && !empty($display_settings['url_plain'])) {
        $this->assertStringNotContainsString('<a href="', $output, 'Test case failed: ' . $this->caseName);
      }
    }

  }

  /**
   * Field values, and expected results.
   *
   * Contains complex internal link, absolute external links,
   *
   * @return array
   *   Values to use at link field setter.
   */
  abstract protected function getTestValues(): array;

  /**
   * Provides an array of link field display settings and expected results.
   *
   * @return array
   *   Test cases.
   */
  abstract protected function getTestCases(): array;

}
