<?php

declare(strict_types=1);

namespace Drupal\file_test\MimeType;

use Drupal\Core\File\MimeType\MimeTypeMapInterface;
use Drupal\Core\File\MimeType\MimeTypeMapModifierInterface;

/**
 * Modifies the MIME type map by adding dummy mappings.
 */
class DummyMimeTypeMapModifier implements MimeTypeMapModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function modifyMimeTypeMap(MimeTypeMapInterface $map): void {
    // Add new mappings.
    $map->addMapping('made_up/file_test_1', 'file_test_1');
    $map->addMapping('made_up/file_test_2', 'file_test_2');
    $map->addMapping('made_up/file_test_2', 'file_test_3');
    // Override existing mapping.
    $map->addMapping('made_up/doc', 'doc');
  }

}
