<?php

namespace Drupal\KernelTests\Core\File;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the MIME type mapper to extension.
 *
 * @coversDefaultClass \Drupal\Core\File\MimeType\MimeTypeMapper
 *
 * @group File
 */
class MimeTypeMapperTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system', 'file_test'];

  /**
   * The MIME type mapper service.
   *
   * @var \Drupal\Core\File\MimeType\MimeTypeMapperInterface
   */
  protected $mapper;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->mapper = $this->container->get('file.mime_type.mapper');
  }

  /**
   * Sets up a very basic mapping array for testing.
   */
  protected function setBasicMapping() {
    $this->mapper->setMapping([
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

  /**
   * @covers ::addMapping
   */
  public function testAddMapping() {
    $this->setBasicMapping();

    $this->mapper->addMapping('image/gif', 'gif');
    $this->assertEquals([
      'mimetypes' => [
        0 => 'application/java-archive',
        1 => 'image/jpeg',
        2 => 'image/gif',
      ],
      'extensions' => [
        'jar' => 0,
        'jpg' => 1,
        'gif' => 2,
      ],
    ], $this->mapper->getMapping());

    $this->mapper->addMapping('image/jpeg', 'jpeg');
    $this->assertEquals([
      'mimetypes' => [
        0 => 'application/java-archive',
        1 => 'image/jpeg',
        2 => 'image/gif',
      ],
      'extensions' => [
        'jar' => 0,
        'jpg' => 1,
        'gif' => 2,
        'jpeg' => 1,
      ],
    ], $this->mapper->getMapping());
  }

  /**
   * @covers ::removeMapping
   */
  public function testRemoveMapping() {
    $this->setBasicMapping();

    $this->assertTrue($this->mapper->removeMapping('jpg'));
    $this->assertEquals([
      'mimetypes' => [
        0 => 'application/java-archive',
        1 => 'image/jpeg',
      ],
      'extensions' => [
        'jar' => 0,
      ],
    ], $this->mapper->getMapping());

    $this->assertFalse($this->mapper->removeMapping('foo'));
  }

  /**
   * @covers ::removeMimeType
   */
  public function testRemoveMimeType() {
    $this->setBasicMapping();

    $this->assertTrue($this->mapper->removeMimeType('image/jpeg'));
    $this->assertEquals([
      'mimetypes' => [
        0 => 'application/java-archive',
      ],
      'extensions' => [
        'jar' => 0,
      ],
    ], $this->mapper->getMapping());

    $this->assertFalse($this->mapper->removeMimeType('foo/bar'));
  }

  /**
   * @covers ::getMimeTypes
   */
  public function testGetMimeTypes() {
    $this->setBasicMapping();
    $this->assertEquals(['application/java-archive', 'image/jpeg'], $this->mapper->getMimeTypes());
  }

  /**
   * @covers ::getMimeTypeForExtension
   */
  public function testGetMimeTypeForExtension() {
    $this->assertSame('image/jpeg', $this->mapper->getMimeTypeForExtension('jpe'));
  }

  /**
   * @covers ::getExtensionsForMimeType
   */
  public function testGetExtensionsForMimeType() {
    $this->assertEquals(['jpe', 'jpeg', 'jpg'], $this->mapper->getExtensionsForMimeType('image/jpeg'));
  }

}
