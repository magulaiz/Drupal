<?php

namespace Drupal\Tests\file\Kernel;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\Tests\field\Kernel\FieldKernelTestBase;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\user\Entity\Role;

/**
 * Tests using entity fields of the media_track field type.
 *
 * @group file
 */
class FileMediaTrackItemTest extends FieldKernelTestBase {

  use TestFileCreationTrait {
    getTestFiles as drupalGetTestFiles;
  }

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['file'];

  /**
   * Created file entity.
   *
   * @var \Drupal\file\Entity\File
   */
  protected $trackFile;

  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installConfig(['user']);
    // Give anonymous users permission to access content, so that we can view
    // and download public files.
    $anonymous_role = Role::load(Role::ANONYMOUS_ID);
    $anonymous_role->grantPermission('access content');
    $anonymous_role->save();

    $this->installEntitySchema('file');
    $this->installSchema('file', ['file_usage']);

    FieldStorageConfig::create([
      'entity_type' => 'entity_test',
      'field_name' => 'media_track_test',
      'type' => 'media_track',
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
    ])->save();
    FieldConfig::create([
      'entity_type' => 'entity_test',
      'field_name' => 'media_track_test',
      'bundle' => 'entity_test',
    ])->save();

    // Get a file to upload.
    $file = current($this->drupalGetTestFiles('webvtt'));

    // Add a filesize property to files as would be read by
    // \Drupal\file\Entity\File::load().
    $file->filesize = filesize($file->uri);
    $this->trackFile = File::create((array) $file);
    $this->trackFile->save();
  }

  /**
   * Tests using entity fields of the media_track field type.
   */
  public function testMediaTrackItem() {
    // Create a test entity with the media_track field set.
    $entity = EntityTest::create();
    $entity->media_track_test->target_id = $this->trackFile->id();
    $entity->media_track_test->label = $label = $this->randomMachineName();
    $entity->media_track_test->kind = $kind = 'subtitles';
    $entity->media_track_test->srclang = $srclang = 'en';
    $entity->media_track_test->default = $default = 0;
    $entity->name->value = $this->randomMachineName();
    $entity->save();

    $entity = EntityTest::load($entity->id());

    $this->assertInstanceOf(FieldItemListInterface::class, $entity->media_track_test);
    $this->assertInstanceOf(FieldItemInterface::class, $entity->media_track_test[0]);
    $this->assertEquals($this->trackFile->id(), $entity->media_track_test->target_id);
    $this->assertEquals($kind, $entity->media_track_test->kind);
    $this->assertEquals($srclang, $entity->media_track_test->srclang);
    $this->assertEquals($label, $entity->media_track_test->label);
    $this->assertEquals($default, $entity->media_track_test->default);

    // Test media_track item properties.
    $expected = ['target_id', 'entity', 'label', 'kind', 'srclang', 'default'];
    $properties = $entity->getFieldDefinition('media_track_test')->getFieldStorageDefinition()->getPropertyDefinitions();
    $this->assertEquals($expected, array_keys($properties));

    // Test the generateSampleValue() method.
    $entity = EntityTest::create();
    $entity->media_track_test->generateSampleItems();
    $this->entityValidateAndSave($entity);
    $this->assertEquals('text/vtt', $entity->media_track_test->entity->get('filemime')->value);
  }

}
