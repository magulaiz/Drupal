<?php

declare(strict_types=1);

namespace Drupal\Tests\link\Kernel;

use Drupal\Tests\field\Kernel\FieldKernelTestBase;
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
   * Link field values use for test.
   *
   * @return array
   *   Values to use at link field setter.
   */
  abstract protected function getTestValues(): array;

  /**
   * Provides case name, link field display settings and expected results.
   *
   * @return \Generator
   *   Test cases.
   */
  abstract protected function getTestCases(): \Generator;

}
