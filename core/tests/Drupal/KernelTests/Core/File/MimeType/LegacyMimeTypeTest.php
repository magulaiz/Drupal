<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\File\MimeType;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests deprecated file features.
 *
 * @group legacy
 * @group File
 */
class LegacyMimeTypeTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system', 'file_deprecated_test'];

  /**
   * Tests deprecation of hook_file_mimetype_mapping_alter.
   *
   * @group legacy
   */
  public function testHookFileMimetypeMappingAlter() {
    $this->expectDeprecation(
      'The deprecated alter hook hook_file_mimetype_mapping_alter() is implemented in these functions: file_deprecated_test_file_mimetype_mapping_alter. This hook is deprecated in drupal:11.1.0 and will be removed before drupal:12.0.0. Implement service tagged with mime_type_map_modifier instead. See https://www.drupal.org/node/2311679'
    );

    $mapper = $this->container->get('file.mime_type.map');
    $this->assertEquals(['file_test_2', 'file_test_3'],
      $mapper->getExtensionsForMimeType('made_up/file_test_2'));
  }

}
