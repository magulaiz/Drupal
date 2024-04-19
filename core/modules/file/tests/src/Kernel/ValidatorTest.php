<?php

namespace Drupal\Tests\file\Kernel;

use Drupal\file\Entity\File;

/**
 * Tests the functions used to validate uploaded files.
 *
 * @group file
 */
class ValidatorTest extends FileManagedUnitTestBase {

  /**
   * An image file.
   *
   * @var \Drupal\file\FileInterface
   */
  protected $image;

  /**
   * A file which is not an image.
   *
   * @var \Drupal\file\Entity\File
   */
  protected $nonImage;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->image = File::create();
    $this->image->setFileUri('core/misc/druplicon.png');
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = \Drupal::service('file_system');
    $this->image->setFilename($file_system->basename($this->image->getFileUri()));

    $this->nonImage = File::create();
    $this->nonImage->setFileUri('core/assets/vendor/jquery/jquery.min.js');
    $this->nonImage->setFilename($file_system->basename($this->nonImage->getFileUri()));
  }

  /**
   * Tests the file_validate_extensions() function.
   */
  public function testFileValidateExtensions() {
    $file = File::create(['filename' => 'asdf.txt']);
    $errors = file_validate_extensions($file, 'asdf txt pork');
    $this->assertCount(0, $errors, 'Valid extension accepted.');

    $file->setFilename('asdf.txt');
    $errors = file_validate_extensions($file, 'exe png');
    $this->assertCount(1, $errors, 'Invalid extension blocked.');
  }

  /**
   * Tests the file_validate_extensions() function.
   *
   * @param array $file_properties
   *   The properties of the file being validated.
   * @param string[] $extensions
   *   An array of the allowed file extensions.
   * @param string[] $expected_errors
   *   The expected error messages as string.
   *
   * @dataProvider providerTestFileValidateExtensionsOnUri
   */
  public function testFileValidateExtensionsOnUri(array $file_properties, array $extensions, array $expected_errors) {
    $file = File::create($file_properties);
    $actual_errors = file_validate_extensions($file, implode(' ', $extensions));
    $actual_errors_as_string = array_map(function ($error_message) {
      return (string) $error_message;
    }, $actual_errors);
    $this->assertEquals($expected_errors, $actual_errors_as_string);
  }

  /**
   * Data provider for ::testFileValidateExtensionsOnUri.
   *
   * @return array[][]
   *   The test cases.
   */
  public function providerTestFileValidateExtensionsOnUri(): array {
    $temporary_txt_file_properties = [
      'filename' => 'asdf.txt',
      'uri' => 'temporary://asdf',
      'status' => 0,
    ];
    $permanent_txt_file_properties = [
      'filename' => 'asdf.txt',
      'uri' => 'public://asdf_0.txt',
      'status' => 1,
    ];
    $permanent_png_file_properties = [
      'filename' => 'The Druplicon',
      'uri' => 'public://druplicon.png',
      'status' => 1,
    ];
    return [
      'Temporary txt validated with "asdf", "txt", "pork"' => [
        'File properties' => $temporary_txt_file_properties,
        'Allowed_extensions' => ['asdf', 'txt', 'pork'],
        'Expected errors' => [],
      ],
      'Temporary txt validated with "exe" and "png"' => [
        'File properties' => $temporary_txt_file_properties,
        'Allowed_extensions' => ['exe', 'png'],
        'Expected errors' => [
          'Only files with the following extensions are allowed: <em class="placeholder">exe png</em>.',
        ],
      ],
      'Permanent txt validated with "asdf", "txt", "pork"' => [
        'File properties' => $permanent_txt_file_properties,
        'Allowed_extensions' => ['asdf', 'txt', 'pork'],
        'Expected errors' => [],
      ],
      'Permanent txt validated with "exe" and "png"' => [
        'File properties' => $permanent_txt_file_properties,
        'Allowed_extensions' => ['exe', 'png'],
        'Expected errors' => [
          'Only files with the following extensions are allowed: <em class="placeholder">exe png</em>.',
        ],
      ],
      'Permanent png validated with "png", "gif", "jpg", "jpeg"' => [
        'File properties' => $permanent_png_file_properties,
        'Allowed_extensions' => ['png', 'gif', 'jpg', 'jpeg'],
        'Expected errors' => [],
      ],
      'Permanent png validated with "exe" and "txt"' => [
        'File properties' => $permanent_png_file_properties,
        'Allowed_extensions' => ['exe', 'txt'],
        'Expected errors' => [
          'Only files with the following extensions are allowed: <em class="placeholder">exe txt</em>.',
        ],
      ],
    ];
  }

  /**
   * Provides data for testFileValidateIsImage.
   *
   * @return array[]
   *   An associative array of simple arrays, with key the test scenario and
   *   value an array having the following elements:
   *   - the file path of the image file to be tested
   *   - the extensions restriction
   *   - the expected error message or NULL if no error expected.
   */
  public function providerFileValidateIsImage(): array {
    return [
      'Valid image, no extensions restriction' => [
        'core/misc/druplicon.png',
        '',
        NULL,
      ],
      'Invalid image, no extensions restriction' => [
        'core/tests/fixtures/files/invalid-img-test.png',
        '',
        'The image file is invalid or the image type is not allowed. Allowed types: png jpeg jpg jpe gif webp',
      ],
      'Not an image, no extensions restriction' => [
        'core/assets/vendor/jquery/jquery.min.js',
        '',
        'The image file is invalid or the image type is not allowed. Allowed types: png jpeg jpg jpe gif webp',
      ],
      // If extension restrictions are specified, we expect that the
      // validator returns an empty array if the test file extension is not in
      // the list of the restricted extensions, as the extension validator will
      // cope with that case.
      'Valid image, extension included in restriction' => [
        'core/misc/druplicon.png',
        'jpeg png',
        NULL,
      ],
      'Invalid image, extension included in restriction' => [
        'core/tests/fixtures/files/invalid-img-test.png',
        'jpeg png',
        'The image file is invalid or the image type is not allowed. Allowed types: png jpeg',
      ],
      'Valid png image, extensions restricted to jpeg' => [
        'core/misc/druplicon.png',
        'jpeg',
        NULL,
      ],
      'Invalid png image, extensions restricted to png' => [
        'core/tests/fixtures/files/invalid-img-test.png',
        'png',
        'The image file is invalid or the image type is not allowed. Allowed types: png',
      ],
      'Not an image, extensions restricted to jpeg' => [
        'core/assets/vendor/jquery/jquery.min.js',
        'jpeg',
        NULL,
      ],
      // Even if we include a non-image file extension in the allowed
      // extensions, still the toolkit will cut it.
      'Not an image, but extension allowed in restriction' => [
        'core/assets/vendor/jquery/jquery.min.js',
        'jpeg png js',
        'The image file is invalid or the image type is not allowed. Allowed types: png jpeg',
      ],
    ];
  }

  /**
   * This ensures a specific file is actually an image.
   *
   * @param string $image_path
   *   The file path of the image file to be tested.
   * @param string $extensions
   *   The allowed extensions restriction.
   * @param string|null $expected_error
   *   The expected error message or NULL if no error expected.
   *
   * @dataProvider providerFileValidateIsImage
   */
  public function testFileValidateIsImage(string $image_path, string $extensions, ?string $expected_error): void {
    $image = File::create();
    $image->setFileUri($image_path);
    $image->setFilename(\Drupal::service('file_system')->basename($image_path));
    $this->assertFileExists($image->getFileUri());
    $errors = file_validate_is_image($image, $extensions);
    if ($expected_error !== NULL) {
      $this->assertEquals($expected_error, strip_tags($errors[0]));
    }
    else {
      $this->assertEmpty($errors);
    }
  }

  /**
   * This ensures the resolution of a specific file is within bounds.
   *
   * The image will be resized if it's too large.
   */
  public function testFileValidateImageResolution() {
    // Non-images.
    $errors = file_validate_image_resolution($this->nonImage);
    $this->assertCount(0, $errors, 'Should not get any errors for a non-image file.');
    $errors = file_validate_image_resolution($this->nonImage, '50x50', '100x100');
    $this->assertCount(0, $errors, 'Do not check the resolution on non files.');

    // Minimum size.
    $errors = file_validate_image_resolution($this->image);
    $this->assertCount(0, $errors, 'No errors for an image when there is no minimum or maximum resolution.');
    $errors = file_validate_image_resolution($this->image, 0, '200x1');
    $this->assertCount(1, $errors, 'Got an error for an image that was not wide enough.');
    $errors = file_validate_image_resolution($this->image, 0, '1x200');
    $this->assertCount(1, $errors, 'Got an error for an image that was not tall enough.');
    $errors = file_validate_image_resolution($this->image, 0, '200x200');
    $this->assertCount(1, $errors, 'Small images report an error.');

    // Maximum size.
    if ($this->container->get('image.factory')->getToolkitId()) {
      // Copy the image so that the original doesn't get resized.
      copy('core/misc/druplicon.png', 'temporary://druplicon.png');
      $this->image->setFileUri('temporary://druplicon.png');

      $errors = file_validate_image_resolution($this->image, '10x5');
      $this->assertCount(0, $errors, 'No errors should be reported when an oversized image can be scaled down.');

      $image = $this->container->get('image.factory')->get($this->image->getFileUri());
      // Verify that the image was scaled to the correct width and height.
      $this->assertLessThanOrEqual(10, $image->getWidth());
      $this->assertLessThanOrEqual(5, $image->getHeight());

      // Once again, now with negative width and height to force an error.
      copy('core/misc/druplicon.png', 'temporary://druplicon.png');
      $this->image->setFileUri('temporary://druplicon.png');
      $errors = file_validate_image_resolution($this->image, '-10x-5');
      $this->assertCount(1, $errors, 'An error reported for an oversized image that can not be scaled down.');

      \Drupal::service('file_system')->unlink('temporary://druplicon.png');
    }
    else {
      // TODO: should check that the error is returned if no toolkit is available.
      $errors = file_validate_image_resolution($this->image, '5x10');
      $this->assertCount(1, $errors, 'Oversize images that cannot be scaled get an error.');
    }
  }

  /**
   * This will ensure the filename length is valid.
   */
  public function testFileValidateNameLength() {
    // Create a new file entity.
    $file = File::create();

    // Add a filename with an allowed length and test it.
    $file->setFilename(str_repeat('x', 240));
    $this->assertEquals(240, strlen($file->getFilename()));
    $errors = file_validate_name_length($file);
    $this->assertCount(0, $errors, 'No errors reported for 240 length filename.');

    // Add a filename with a length too long and test it.
    $file->setFilename(str_repeat('x', 241));
    $errors = file_validate_name_length($file);
    $this->assertCount(1, $errors, 'An error reported for 241 length filename.');

    // Add a filename with an empty string and test it.
    $file->setFilename('');
    $errors = file_validate_name_length($file);
    $this->assertCount(1, $errors, 'An error reported for 0 length filename.');
  }

  /**
   * Tests file_validate_size().
   */
  public function testFileValidateSize() {
    // Create a file with a size of 1000 bytes, and quotas of only 1 byte.
    $file = File::create(['filesize' => 1000]);
    $errors = file_validate_size($file, 0, 0);
    $this->assertCount(0, $errors, 'No limits means no errors.');
    $errors = file_validate_size($file, 1, 0);
    $this->assertCount(1, $errors, 'Error for the file being over the limit.');
    $errors = file_validate_size($file, 0, 1);
    $this->assertCount(1, $errors, 'Error for the user being over their limit.');
    $errors = file_validate_size($file, 1, 1);
    $this->assertCount(2, $errors, 'Errors for both the file and their limit.');
  }

}
