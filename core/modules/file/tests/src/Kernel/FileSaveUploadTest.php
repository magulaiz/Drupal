<?php

namespace Drupal\Tests\file\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Tests file_save_upload().
 *
 * @group file
 * @group legacy
 */
class FileSaveUploadTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'file',
    'file_test',
    'file_validator_test',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    \file_put_contents('test.txt', 'test');

    parent::setUp();
    $request = new Request();
    $request->files->set('files', [
      'file' => new UploadedFile(
        path: 'test.txt',
        originalName: 'test.txt',
        mimeType: 'text/plain',
        error: \UPLOAD_ERR_OK,
        test: TRUE
      ),
    ]);

    $requestStack = new RequestStack();
    $requestStack->push($request);

    $this->container->set('request_stack', $requestStack);
  }

  /**
   * Tests file_save_upload() with empty extensions.
   */
  public function testFileSaveUploadEmptyExtensions(): void {
    $validators = ['file_validate_extensions' => ''];
    $file = file_save_upload('file', $validators, 'public://');
    $this->assertCount(1, $file);
  }

}
