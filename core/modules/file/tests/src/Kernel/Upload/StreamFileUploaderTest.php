<?php

declare(strict_types=1);

namespace Drupal\Tests\file\Kernel\Upload;

use PHPUnit\Framework\Attributes\CoversClass;
use Drupal\KernelTests\KernelTestBase;
use org\bovigo\vfs\vfsStream;

/**
 * Tests the stream file uploader.
 *
 * @group file
 */
#[CoversClass(\Drupal\file\Upload\InputStreamFileWriter::class)]
class StreamFileUploaderTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['file'];

  public function testWriteStreamToFileSuccess(): void {
    vfsStream::newFile('foo.txt')
      ->at($this->vfsRoot)
      ->withContent('bar');

    $fileWriter = $this->container->get('file.input_stream_file_writer');

    $filename = $fileWriter->writeStreamToFile(vfsStream::url('root/foo.txt'));

    $this->assertStringStartsWith('temporary://', $filename);
    $this->assertStringEqualsFile($filename, 'bar');
  }

  public function testWriteStreamToFileWithSmallerBytes(): void {
    $content = $this->randomString(2048);
    vfsStream::newFile('foo.txt')
      ->at($this->vfsRoot)
      ->withContent($content);

    $fileWriter = $this->container->get('file.input_stream_file_writer');

    $filename = $fileWriter->writeStreamToFile(
      stream: vfsStream::url('root/foo.txt'),
      bytesToRead: 1024,
    );

    $this->assertStringStartsWith('temporary://', $filename);
    $this->assertStringEqualsFile($filename, $content);
  }

}
