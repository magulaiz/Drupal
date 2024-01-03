<?php

namespace Drupal\Tests\block_content\Kernel;

use Drupal\block\Entity\Block;
use Drupal\block_content\Entity\BlockContent;
use Drupal\block_content\Entity\BlockContentType;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * Tests the block content.
 *
 * @group block_content
 */
class BlockContentTest extends KernelTestBase {

  use UserCreationTrait;
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['block', 'block_content', 'system', 'user'];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('block_content');
  }

  /**
   * Tests the editing links for BlockContentBlock.
   */
  public function testOperationLinks(): void {
    // Create a block content type.
    $block_content_type = BlockContentType::create([
      'id' => 'spiffy',
      'label' => 'Mucho spiffy',
      'description' => "Provides a block type that increases your site's spiffiness by up to 11%",
    ]);
    $block_content_type->save();
    // And a block content entity.
    $block_content = BlockContent::create([
      'info' => 'Spiffy prototype',
      'type' => 'spiffy',
    ]);
    $block_content->save();
    $block = Block::create([
      'plugin' => 'block_content' . PluginBase::DERIVATIVE_SEPARATOR . $block_content->uuid(),
      'region' => 'content',
      'id' => 'machine_name',
      'theme' => 'stark',
    ]);

    $links = $block->getOperationLinks();
    $block_link = [
      'title' => $this->t('Edit block'),
      'url' => $block_content->toUrl('edit-form')->setOptions([]),
      'weight' => 50,
    ];

    // At this point, we are logged in as anonymous, so the user doesn't
    // have the "administer block" permission.
    $this->assertEmpty($links);

    $this->setUpCurrentUser(['uid' => 1], ['edit any spiffy block content', 'administer blocks']);
    $links = $block->getOperationLinks();
    // At this point, we are logged in as an admin, so the user does
    // have the "administer block" permission.
    $this->assertEquals(['block-edit' => $block_link], $links);
  }

}
