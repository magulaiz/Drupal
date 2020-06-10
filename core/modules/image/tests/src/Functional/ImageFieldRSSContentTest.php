<?php

namespace Drupal\Tests\image\Functional;

use Drupal\file\Entity\File;
use Drupal\Tests\TestFileCreationTrait;

/**
 * Ensure that images added to nodes appear correctly in RSS feeds.
 *
 * @group image
 */
class ImageFieldRSSContentTest extends ImageFieldTestBase {

  use TestFileCreationTrait {
    getTestFiles as drupalGetTestFiles;
  }

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['node', 'views'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests RSS enclosure formatter display for RSS feeds.
   */
  public function testFileFieldRSSContent() {
    $node_storage = $this->container->get('entity_type.manager')->getStorage('node');
    $field_name = strtolower($this->randomMachineName());
    $type_name = 'article';

    $this->createImageField($field_name, $type_name);

    // RSS display must be added manually.
    $this->drupalGet("admin/structure/types/manage/$type_name/display");
    $edit = [
      "display_modes_custom[rss]" => '1',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));

    // Change the format to 'RSS enclosure'.
    $this->drupalGet("admin/structure/types/manage/$type_name/display/rss");
    $edit = [
      "fields[$field_name][type]" => 'file_rss_enclosure',
      "fields[$field_name][region]" => 'content',
    ];
    $this->drupalPostForm(NULL, $edit, t('Save'));

    $test_files = $this->drupalGetTestFiles('image');

    // Create a new node with the uploaded image. Promote to frontpage needs to
    // be set so this node will appear in the RSS feed.
    $nid = $this->uploadNodeImage($test_files[0], $field_name, $type_name, $this->randomMachineName());
    $node_storage->resetCache([$nid]);
    $node = $node_storage->load($nid);
    $node->promote = 1;
    $node->save();

    // Get the uploaded file from the node.
    $node_file = File::load($node->{$field_name}->target_id);

    // Check that the RSS enclosure appears in the RSS feed.
    $this->drupalGet('rss.xml');
    $elements = $this->xpath(
      '//enclosure[@url=:url and @length=:length and @type=:type]',
      [
        ':url' => file_create_url($node_file->getFileUri()),
        ':length' => $node_file->getSize(),
        ':type' => $node_file->getMimeType(),
      ]
    );
    $this->assertNotEmpty($elements, 'Image field RSS enclosure is displayed when viewing the RSS feed.');
  }

}
