<?php

namespace Drupal\KernelTests\Core\File;

/**
 * Tests filename mimetype detection.
 *
 * @group File
 */
class MimeTypeTest extends FileTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['file_test'];

  /**
   * Tests mapping of mimetypes from filenames.
   */
  public function testFileMimeTypeDetection() {
    $prefixes = ['public://', 'private://', 'temporary://', 'dummy-remote://'];

    $test_case = [
      'test.jar' => 'application/java-archive',
      'test.jpeg' => 'image/jpeg',
      'test.JPEG' => 'image/jpeg',
      'test.jpg' => 'image/jpeg',
      'test.jar.jpg' => 'image/jpeg',
      'test.jpg.jar' => 'application/java-archive',
      'test.pcf.Z' => 'application/x-font',
      'pcf.z' => 'application/octet-stream',
      'jar' => 'application/octet-stream',
      'some.junk' => 'application/octet-stream',
      'foo.file_test_1' => 'madeup/file_test_1',
      'foo.file_test_2' => 'madeup/file_test_2',
      'foo.doc' => 'madeup/doc',
      'test.ogg' => 'audio/ogg',
    ];

    $guesser = $this->container->get('file.mime_type.guesser');
    // Test using default mappings.
    foreach ($test_case as $input => $expected) {
      // Test stream [URI].
      foreach ($prefixes as $prefix) {
        $output = $guesser->guessMimeType($prefix . $input);
        $this->assertSame($expected, $output);
      }

      // Test normal path equivalent
      $output = $guesser->guessMimeType($input);
      $this->assertSame($expected, $output);
    }

    // Now test the extension guesser by passing in a custom mapping.
    $mapping = [
      'mimetypes' => [
        0 => 'application/java-archive',
        1 => 'image/jpeg',
      ],
      'extensions' => [
        'jar' => 0,
        'jpg' => 1,
      ],
    ];

    $test_case = [
      'test.jar' => 'application/java-archive',
      'test.jpeg' => 'application/octet-stream',
      'test.jpg' => 'image/jpeg',
      'test.jar.jpg' => 'image/jpeg',
      'test.jpg.jar' => 'application/java-archive',
      'test.pcf.z' => 'application/octet-stream',
      'pcf.z' => 'application/octet-stream',
      'jar' => 'application/octet-stream',
      'some.junk' => 'application/octet-stream',
      'foo.file_test_1' => 'application/octet-stream',
      'foo.file_test_2' => 'application/octet-stream',
      'foo.doc' => 'application/octet-stream',
      'test.ogg' => 'application/octet-stream',
    ];
    $mime_type_mapper = $this->container->get('file.mime_type.mapper');
    $mime_type_mapper->setMapping($mapping);
    $extension_guesser = $this->container->get('file.mime_type.guesser.extension');
    $extension_guesser->setMapping($mapping);

    foreach ($test_case as $input => $expected) {
      $output = $extension_guesser->guessMimeType($input);
      $this->assertSame($expected, $output);
    }
  }

  /**
   * Test deprecation of ::setMapping.
   *
   * @group legacy
   */
  public function testSetMapping() {

    $this->expectDeprecation('Drupal\Core\File\MimeType\ExtensionMimeTypeGuesser::setMapping() is deprecated in drupal:10.1.0, and will be removed in drupal:11.0.0. Use \Drupal\Core\File\MimeType\MimeTypeMapper::setMapping() instead. See https://www.drupal.org/project/drupal/issues/2311679.');

    $extension_guesser = $this->container->get('file.mime_type.guesser.extension');
    $extension_guesser->setMapping([
      'mimetypes' => [
        0 => 'application/java-archive',
        1 => 'image/jpeg',
      ],
      'extensions' => [
        'jar' => 0,
        'jpg' => 1,
      ],
    ]);
  }

}
