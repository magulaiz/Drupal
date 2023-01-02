<?php

namespace Drupal\KernelTests\Core\File;

use Drupal\Core\File\MimeType\ExtensionMimeTypeGuesser;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests that deprecation messages are raised for deprecations.
 *
 * @covers \Drupal\Core\File\MimeType\ExtensionMimeTypeGuesser
 * @group file
 * @group legacy
 *
 * @todo Remove this class once deprecations are removed.
 */
class ExtensionMimeTypeGuesserDeprecationTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system', 'file_deprecated_test'];

  /**
   * Tests that deprecations are raised for missing constructor arguments.
   *
   * @covers \Drupal\Core\File\MimeType\ExtensionMimeTypeGuesser::__construct
   * @group legacy
   */
  public function testConstructorDeprecation(): void {

    $this->expectDeprecation('Calling ' . ExtensionMimeTypeGuesser::class . '::__construct() without the $mapper argument is deprecated in drupal:10.1.0 and will be required before drupal:11.0.0. See https://www.drupal.org/node/2311679.');

    new ExtensionMimeTypeGuesser(
      $this->container->get('module_handler'),
      NULL
    );
  }

}
