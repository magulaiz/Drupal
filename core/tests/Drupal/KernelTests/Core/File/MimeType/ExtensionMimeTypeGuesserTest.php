<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\File\MimeType;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests filename mimetype detection.
 *
 * @group File
 * @coversDefaultClass \Drupal\Core\File\MimeType\ExtensionMimeTypeGuesser
 */
class ExtensionMimeTypeGuesserTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['file_test'];

  /**
   * Tests mapping of mimetypes from filenames.
   *
   * @covers ::guessMimeType
   */
  public function testGuessMimeType(): void {
    $prefixes = ['public://', 'private://', 'temporary://', 'dummy-remote://'];

    $test_case = [
      'test.jar' => 'application/java-archive',
      'test.jpeg' => 'image/jpeg',
      'test.JPEG' => 'image/jpeg',
      'test.jpg' => 'image/jpeg',
      'test.jar.jpg' => 'image/jpeg',
      'test.jpg.jar' => 'application/java-archive',
      'test.pcf.Z' => 'application/x-font',
      'pcf.z' => NULL,
      'jar' => NULL,
      'some.junk' => NULL,
      // Mime type added by file_test_mimetype_alter()
      'foo.file_test_1' => 'made_up/file_test_1',
      'foo.file_test_2' => 'made_up/file_test_2',
      'foo.doc' => 'made_up/doc',
      'test.ogg' => 'audio/ogg',
    ];

    /** @var \Drupal\Core\File\MimeType\ExtensionMimeTypeGuesser $guesser */
    $guesser = $this->container->get('file.mime_type.guesser.extension');
    // Test using default mappings.
    foreach ($test_case as $input => $expected) {
      // Test stream [URI].
      foreach ($prefixes as $prefix) {
        $output = $guesser->guessMimeType($prefix . $input);
        $this->assertSame($expected, $output);
      }

      // Test normal path equivalent.
      $output = $guesser->guessMimeType($input);
      $this->assertSame($expected, $output);
    }
  }

  /**
   * Tests mapping of mimetypes from filenames.
   *
   * @group legacy
   * @covers ::guessMimeType
   * @covers ::setMapping
   */
  public function testFileMimeTypeDetectionCustomMapping(): void {
    /** @var \Drupal\Core\File\MimeType\ExtensionMimeTypeGuesser $extension_guesser */
    $extension_guesser = $this->container->get(
      'file.mime_type.guesser.extension'
    );

    // Pass in a custom mapping.
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

    $this->expectDeprecation(
      'Drupal\Core\File\MimeType\ExtensionMimeTypeGuesser::setMapping() is deprecated in drupal:11.1.0 and is removed from drupal:12.0.0. Use \Drupal\Core\File\MimeType\MimeTypeMapper::setMapping() instead. See https://www.drupal.org/project/drupal/issues/2311679'
    );
    $extension_guesser->setMapping($mapping);

    $test_case = [
      'test.jar' => 'application/java-archive',
      'test.jpeg' => NULL,
      'test.jpg' => 'image/jpeg',
      'test.jar.jpg' => 'image/jpeg',
      'test.jpg.jar' => 'application/java-archive',
      'test.pcf.z' => NULL,
      'pcf.z' => NULL,
      'jar' => NULL,
      'some.junk' => NULL,
      'foo.file_test_1' => NULL,
      'foo.file_test_2' => NULL,
      'foo.doc' => NULL,
      'test.ogg' => NULL,
    ];

    foreach ($test_case as $input => $expected) {
      $output = $extension_guesser->guessMimeType($input);
      $this->assertSame($expected, $output);
    }
  }

}
