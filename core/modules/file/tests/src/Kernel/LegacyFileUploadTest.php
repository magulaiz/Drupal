<?php

namespace Drupal\Tests\file\Kernel;

use Drupal\Core\Form\FormState;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Tests deprecated file upload functions.
 *
 * @group file
 * @group legacy
 */
class LegacyFileUploadTest extends FileManagedUnitTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    // Set up our files.
    $request_stack = new RequestStack();
    $request = new Request();
    $files = [
      'test_file' => $this->createUploadedFile(UPLOAD_ERR_OK),
    ];
    $request->files->set('files', $files);
    $request_stack->push($request);
    $this->container->set('request_stack', $request_stack);
  }

  /**
   * Helper method to prepare the UploadedFile depending on core version.
   *
   * Drupal core uses different Symfony versions where we have a different
   * UploadedFile constructor signature.
   */
  protected function createUploadedFile(
    int $error_status,
    int $size = 0,
    string $source_filename = 'upload_test.txt',
    string $dest_filename = 'upload_test.txt'
  ): UploadedFile {
    $source_filepath = $this->createSourceTestFilePath($source_filename);
    return new UploadedFile($source_filepath, $dest_filename, 'text/plain', $error_status, TRUE);
  }

  /**
   * Gets the file path of the source file.
   *
   * @param string $filename
   *   Filename of the source file to be get the file path for.
   *
   * @return string
   *   File path of the source file.
   */
  protected function createSourceTestFilePath(string $filename): string {
    $file_system = $this->container->get('file_system');
    // Create dummy file, since symfony will test if it exists.
    $filepath = $file_system->getTempDirectory() . '/' . $filename;
    $this->assertNotFalse(file_put_contents($filepath, 'foo bar'));
    return $filepath;
  }

  /**
   * Tests the file_save_upload() deprecation.
   */
  public function testFileSaveUploadDeprecation(): void {
    $this->expectDeprecation('file_save_upload() is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. Use \Drupal\file\Upload\FormFileUploadHandler::saveFileUploads() instead. See https://www.drupal.org/node/3382414');
    $validators = ['FileExtension' => ['extensions' => 'txt']];
    // We cannot use move_uploaded_files() so this will fail.
    $files = file_save_upload('test_file', $validators, 'public://');
    $this->assertFalse($files[0]);
  }

  /**
   * Tests file_managed_file_save_upload() deprecation.
   */
  public function testFileManagedSaveFileUpload(): void {
    $element = [
      '#field_name' => 'test',
      '#parents' => ['test', 'file'],
      '#multiple' => FALSE,
      '#upload_location' => 'public://',
      '#upload_validators' => [
        'FileExtension' => ['extensions' => 'txt'],
      ],
    ];
    $formState = new FormState();

    $this->expectDeprecation('file_managed_file_save_upload() is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. Use \Drupal\file\Upload\FileElementHelper::saveFileUploads() instead. See https://www.drupal.org/node/3382414');
    // We cannot use move_uploaded_files() so this will fail.
    $files = file_managed_file_save_upload($element, $formState);
    $this->assertCount(1, $formState->getErrors());
  }

}
