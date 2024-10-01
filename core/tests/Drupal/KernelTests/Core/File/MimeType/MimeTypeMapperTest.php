<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\File\MimeType;

use Drupal\Core\File\MimeType\MimeTypeMapperInterface;
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
  protected static $modules = ['system'];

  /**
   * The MIME type mapper service.
   */
  protected MimeTypeMapperInterface $mapper;

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
    $this->assertEquals(
      'image/gif',
      $this->mapper->getMimeTypeForExtension('gif')
    );

    $this->mapper->addMapping('image/jpeg', 'jpeg');
    $this->assertEquals(
      'image/jpeg',
      $this->mapper->getMimeTypeForExtension('jpeg')
    );
  }

  /**
   * @covers ::removeMapping
   */
  public function testRemoveMapping() {
    $this->setBasicMapping();

    $this->assertTrue($this->mapper->removeMapping('jpg'));
    $this->assertNull($this->mapper->getMimeTypeForExtension('jpg'));
    $this->assertFalse($this->mapper->removeMapping('foo'));
  }

  /**
   * @covers ::removeMimeType
   */
  public function testRemoveMimeType() {
    $this->setBasicMapping();

    $this->assertTrue($this->mapper->removeMimeType('image/jpeg'));
    $this->assertNull($this->mapper->getMimeTypeForExtension('jpg'));
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
    // Using default mapping.
    $this->assertSame('image/jpeg', $this->mapper->getMimeTypeForExtension('jpe'));
  }

  /**
   * @covers ::getExtensionsForMimeType
   */
  public function testGetExtensionsForMimeType() {
    // Using default mapping.
    $this->assertEquals(['jpe', 'jpeg', 'jpg'], $this->mapper->getExtensionsForMimeType('image/jpeg'));
  }

}
