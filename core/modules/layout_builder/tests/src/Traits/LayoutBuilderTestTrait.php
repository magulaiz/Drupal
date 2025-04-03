<?php

declare(strict_types=1);

namespace Drupal\Tests\layout_builder\Traits;

use Drupal\block_content\Entity\BlockContent;
use Drupal\block_content\Entity\BlockContentType;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\layout_builder\Field\LayoutSectionItemList;
use Drupal\layout_builder\Plugin\SectionStorage\OverridesSectionStorage;
use Drupal\layout_builder\Section;
use Drupal\layout_builder\SectionComponent;

/**
 * Test trait for Layout Builder functionality.
 */
trait LayoutBuilderTestTrait {

  /**
   * Set up the test content.
   */
  protected function setupTestContent(): void {
    $this->drupalPlaceBlock('local_tasks_block');

    $this->createContentType(['type' => 'bundle_with_section_field', 'new_revision' => TRUE]);
    $this->createNode([
      'type' => 'bundle_with_section_field',
      'title' => 'The node title',
      'body' => [
        [
          'value' => 'The node body',
        ],
      ],
    ]);
    $this->createNode([
      'type' => 'bundle_with_section_field',
      'title' => 'The node2 title',
      'body' => [
        [
          'value' => 'The node2 body',
        ],
      ],
    ]);
    $bundle = BlockContentType::create([
      'id' => 'basic',
      'label' => 'Basic block',
      'revision' => 1,
    ]);
    $bundle->save();
    block_content_add_body_field($bundle->id());
  }

  /**
   * Add a block to a layout.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to attach the block to.
   * @param string $blockType
   *   The block type id.
   * @param string $title
   *   The title of the block.
   * @param string $body
   *   The body of the block.
   */
  protected function addInlineBlockToLayout(ContentEntityInterface $entity, string $blockType, string $title, string $body): SectionComponent {
    $layout = $entity->get(OverridesSectionStorage::FIELD_NAME);
    $uuid = \Drupal::getContainer()->get(UuidInterface::class)->generate();
    \assert($layout instanceof LayoutSectionItemList);
    $newSection = new Section('layout_onecol');
    $block = BlockContent::create([
      'type' => $blockType,
      'info' => $title,
      'body' => $body,
    ]);
    $block->save();
    $component = new SectionComponent($uuid, 'content', [
      'id' => \sprintf('inline_block:%s', $blockType),
      'block_revision_id' => $block->getRevisionId(),
    ]);
    $newSection->appendComponent($component);
    $entity->get(OverridesSectionStorage::FIELD_NAME)->appendSection($newSection);
    $entity->setNewRevision();
    $entity->save();
    return $component;
  }

  /**
   * Add a block to a layout via the UI.
   *
   * @param string $entityType
   *   The entity type.
   * @param string $id
   *   The entity id.
   * @param string $region
   *   The region to add the block to.
   * @param int $delta
   *   The delta of the section to add the block to.
   * @param string $blockType
   *   The block type id.
   * @param string $title
   *   The title of the block.
   * @param string $body
   *   The body of the block.
   * @param bool $save
   *   Whether to save the layout.
   */
  protected function addInlineBlockViaUi(string $entityType, string $id, string $region, int $delta, string $blockType, string $title, string $body, bool $save = TRUE): void {
    $this->drupalGet(\sprintf('/layout_builder/add/block/overrides/%s.%s/%d/%s/inline_block:%s', $entityType, $id, $delta, $region, $blockType));
    $this->submitForm([
      'settings[label]' => $title,
      'settings[block_form][body][0][value]' => $body,
    ], 'Add block');
    if ($save) {
      $this->submitForm([], 'Save layout');
    }
  }

  /**
   * Remove an inline block from a layout.
   *
   * @param string $placeholderLabel
   *   The placeholder label.
   * @param string $storageType
   *   The storage type.
   * @param string $storageIdentifier
   *   The storage identifier.
   * @param string $region
   *   The region of the block.
   * @param int $delta
   *   The section delta the block is contained in.
   */
  protected function removeInlineBlockViaUi(string $placeholderLabel, string $storageType, string $storageIdentifier, string $region, int $delta): void {
    $uuid = $this->getComponentUuidFromPlaceholderLabel($placeholderLabel);
    $this->drupalGet(\sprintf('layout_builder/remove/block/%s/%s/%d/%s/%s', $storageType, $storageIdentifier, $delta, $region, $uuid));
    $this->submitForm([], 'Remove');
    $this->submitForm([], 'Save layout');
  }

  /**
   * Get the UUID of a component from a placeholder label.
   *
   * @param string $placeholderLabel
   *   The placeholder label.
   */
  protected function getComponentUuidFromPlaceholderLabel(string $placeholderLabel): string {
    $element = $this->assertSession()->elementExists('css', \sprintf('[data-layout-content-preview-placeholder-label*="%s"]', $placeholderLabel));
    return $element->getAttribute('data-layout-block-uuid');
  }

}
