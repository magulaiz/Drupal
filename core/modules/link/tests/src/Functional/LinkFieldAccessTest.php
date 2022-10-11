<?php

namespace Drupal\Tests\link\Functional;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\link\LinkItemInterface;
use Drupal\node\NodeInterface;
use Drupal\Tests\block_content\Functional\BlockContentTestBase;

/**
 * Tests link field entity access.
 *
 * @group link
 */
class LinkFieldAccessTest extends BlockContentTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['node', 'block', 'link'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'classy';

  /**
   * The current node.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $unpublishedNode;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    if ($this->profile !== 'standard') {
      // Create the Basic page node type.
      $this->drupalCreateContentType([
        'type' => 'page',
        'name' => 'Basic page',
        'display_submitted' => FALSE,
      ]);
    }

    $bundle = $this->createBlockContentType('block_link_test', FALSE);
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_block_link',
      'entity_type' => 'block_content',
      'type' => 'link',
      'cardinality' => -1,
    ]);
    $field_storage->save();
    FieldConfig::create([
      'field_name' => 'field_block_link',
      'field_storage' => $field_storage,
      'label' => 'Links in the Block',
      'entity_type' => 'block_content',
      'bundle' => $bundle->id(),
      'settings' => [
        'title' => 1,
        'link_type' => LinkItemInterface::LINK_GENERIC,
      ],
    ])->save();

    $form_display = EntityFormDisplay::create([
      'status' => TRUE,
      'targetEntityType' => 'block_content',
      'bundle' => $bundle->id(),
      'mode' => 'default',
      'content' => [
        'field_block_link' => [
          'region' => 'content',
          'weight' => 1,
          'type' => 'link_default',
          'settings' => [],
          'third_party_settings' => [],
        ],
      ],
    ]);
    $form_display->save();

    $view_display = EntityViewDisplay::create([
      'status' => TRUE,
      'targetEntityType' => 'block_content',
      'bundle' => $bundle->id(),
      'mode' => 'default',
      'content' => [
        'field_block_link' => [
          'label' => 'hidden',
          'region' => 'content',
          'weight' => 1,
          'type' => 'link',
          'settings' => [],
          'third_party_settings' => [],
        ],
      ],
    ]);
    $view_display->save();

    // Create an unpublished node.
    $this->unpublishedNode = $this->drupalCreateNode([
      'status' => NodeInterface::NOT_PUBLISHED,
      'type' => 'page',
    ]);
  }

  /**
   * Test that unpublished node links are only shown to appropriate users.
   */
  public function testLinkFieldOnBlockContent() {
    $block_content = $this->createBlockContent('test title', 'block_link_test');

    $this->drupalLogin($this->drupalCreateUser([
      'administer blocks',
      'bypass node access',
    ]));
    $this->drupalGet('block/' . $block_content->id());
    $this->submitForm([
      'field_block_link[0][uri]' => $this->unpublishedNode->toUrl()->toString(),
      'field_block_link[0][title]' => 'Unpublished node',
    ], 'Save');

    $this->placeBlock('block_content:' . $block_content->uuid());

    // The admin user should be capable of viewing unpublished nodes.
    $this->drupalGet('<front>');
    $this->assertSession()->linkExists('Unpublished node');
    // Ensure the node is accessible.
    $this->drupalGet('node/' . $this->unpublishedNode->id());
    $this->assertSession()->statusCodeEquals(200);

    $this->drupalLogout();

    // A normal user should not be capable of viewing unpublished node links.
    $this->drupalLogin($this->drupalCreateUser([]));
    $this->drupalGet('<front>');
    $this->assertSession()->linkNotExists('Unpublished node');

    // Ensure the node is inaccessible.
    $this->drupalGet('node/' . $this->unpublishedNode->id());
    $this->assertSession()->statusCodeEquals(403);
  }

}
