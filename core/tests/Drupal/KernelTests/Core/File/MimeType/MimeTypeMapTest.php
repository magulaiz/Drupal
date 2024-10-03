<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\File\MimeType;

use Drupal\Core\File\MimeType\MimeTypeMapInterface;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the MIME type mapper to extension.
 *
 * @coversDefaultClass \
 *
 * @group File
 */
class MimeTypeMapTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system'];

  /**
   * The MIME type mapper service.
   */
  protected MimeTypeMapInterface $map;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->map = $this->container->get('file.mime_type.map');
  }

  /**
   * Sets up a very basic mapping array for testing.
   */
  protected function setBasicMapping() {
    $this->map->reset();
    $this->map->addMapping('application/java-archive', 'jar');
    $this->map->addMapping('image/jpeg', 'jpg');
  }

  /**
   * @covers ::addMapping
   */
  public function testAddMapping() {
    $this->setBasicMapping();

    $this->map->addMapping('image/gif', 'gif');
    $this->assertEquals(
      'image/gif',
      $this->map->getMimeTypeForExtension('gif')
    );

    $this->map->addMapping('image/jpeg', 'jpeg');
    $this->assertEquals(
      'image/jpeg',
      $this->map->getMimeTypeForExtension('jpeg')
    );
  }

  /**
   * @covers ::removeMapping
   */
  public function testRemoveMapping() {
    $this->setBasicMapping();

    $this->assertTrue($this->map->removeMapping('jpg'));
    $this->assertNull($this->map->getMimeTypeForExtension('jpg'));
    $this->assertFalse($this->map->removeMapping('foo'));
  }

  /**
   * @covers ::removeMimeType
   */
  public function testRemoveMimeType() {
    $this->setBasicMapping();

    $this->assertTrue($this->map->removeMimeType('image/jpeg'));
    $this->assertNull($this->map->getMimeTypeForExtension('jpg'));
    $this->assertFalse($this->map->removeMimeType('foo/bar'));
  }

  /**
   * @covers ::getMimeTypes
   */
  public function testGetMimeTypes() {
    $this->setBasicMapping();
    $this->assertEquals(['application/java-archive', 'image/jpeg'],
      $this->map->getMimeTypes());
  }

  /**
   * @covers ::getMimeTypeForExtension
   */
  public function testGetMimeTypeForExtension() {
    // Using default mapping.
    $this->assertSame('image/jpeg', $this->map->getMimeTypeForExtension('jpe'));
  }

  /**
   * @covers ::getExtensionsForMimeType
   */
  public function testGetExtensionsForMimeType() {
    // Using default mapping.
    $this->assertEquals(['jpe', 'jpeg', 'jpg'],
      $this->map->getExtensionsForMimeType('image/jpeg'));
  }

}
