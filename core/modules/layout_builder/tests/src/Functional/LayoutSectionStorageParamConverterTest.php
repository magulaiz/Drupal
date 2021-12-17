<?php

namespace Drupal\Tests\layout_builder\Functional;

use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests that section storage parameter conversion works as expected.
 *
 * @group layout_builder
 */
class LayoutSectionStorageParamConverterTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'layout_builder',
    'node',
    'user',
    'layout_builder_tempstore_access_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'classy';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $page = $this->getSession()->getPage();

    $this->drupalLogin($this->drupalCreateUser([
      'configure any layout',
      'administer node display',
    ]));

    $bundles = [
      'storage_empty_tempstore_empty',
      'storage_empty_tempstore_full',
      'storage_full_tempstore_full',
    ];

    foreach ($bundles as $bundle) {
      $this->drupalGet("admin/structure/types/manage/{$bundle}/display");
      $this->submitForm(['layout[enabled]' => TRUE], 'Save');

      if ($bundle !== 'storage_empty_tempstore_empty') {
        $page->clickLink('Manage layout');
        $page->clickLink('Add section');
        $page->clickLink('One column');
        $page->pressButton('Add section');
      }

      if ($bundle === 'storage_full_tempstore_full') {
        $page->pressButton('Save');
      }
    }
  }

  /**
   * Tests that section storage parameter conversion works as expected.
   *
   * @param string $expected_section_storage
   *   The expected access result instance for the section storage.
   * @param string $expected_tempstore
   *   The expected access result instance for the tempstore.
   * @param array $route_parameters
   *   An array of route parameters describing the section storage to retrieve.
   *
   * @dataProvider providerStorageParamConverter
   */
  public function testStorageParamConverter(string $expected_section_storage, string $expected_tempstore, array $route_parameters) {
    $access_section_storage = Url::fromRoute('layout_builder_tempstore_access_test.section_count', $route_parameters)->access(NULL, TRUE);
    $access_tempstore = Url::fromRoute('layout_builder_tempstore_access_test.section_count_tempstore', $route_parameters)->access(NULL, TRUE);

    $this->assertInstanceOf($expected_section_storage, $access_section_storage);
    $this->assertInstanceOf($expected_tempstore, $access_tempstore);
  }

  /**
   * Data provider for ::testStorageParamConverter().
   */
  public function providerStorageParamConverter() {
    return [
      'Storage empty, tempstore empty' => [
        AccessResultForbidden::class,
        AccessResultForbidden::class,
        [
          'section_storage' => 'node.storage_empty_tempstore_empty.default',
          'section_storage_type' => 'defaults',
        ],
      ],
      'Storage empty, tempstore full' => [
        AccessResultForbidden::class,
        AccessResultAllowed::class,
        [
          'section_storage' => 'node.storage_empty_tempstore_full.default',
          'section_storage_type' => 'defaults',
        ],
      ],
      'Storage full, tempstore full' => [
        AccessResultAllowed::class,
        AccessResultAllowed::class,
        [
          'section_storage' => 'node.storage_full_tempstore_full.default',
          'section_storage_type' => 'defaults',
        ],
      ],
    ];
  }

}
