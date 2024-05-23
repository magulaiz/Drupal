<?php

namespace  Drupal\Tests\block_content\Traits;

use Drupal\Component\Utility\Unicode;
use Drupal\comment\Plugin\Field\FieldType\CommentItemInterface;
use Drupal\Core\Field\FieldConfigInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Provides common functionality for the Block Content test classes.
 */
trait BlockContentTestTrait {

  /**
   * Helper function add a body field to a block_content entity type.
   *
   * @param string $block_type_id
   *   ID of the block type.
   * @param string $label
   *   (optional) The label for the body instance. Defaults to 'Body'
 *
   * @return \Drupal\Core\Field\FieldConfigInterface
   *   A Body field object.
   */
  public function addBodyField(string $block_type_id, string $label = 'Body'): FieldConfigInterface {
    // Add or remove the body field, as needed.
    $field = FieldConfig::loadByName('block_content', $block_type_id, 'field_body');
    if (empty($field)) {
      // Create a telephone field.
      $field_storage = FieldStorageConfig::create([
        'field_name' => 'field_body',
        'entity_type' => 'block_content',
        'type' => 'text_long',
      ]);
      $field_storage->save();

      $field = FieldConfig::create([
        'field_storage' => $field_storage,
        'bundle' => $block_type_id,
        'label' => $label,
        'settings' => [
          'allowed_formats' => [],
        ],
      ]);
      $field->save();

      /** @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface $display_repository */
      $display_repository = \Drupal::service('entity_display.repository');

      // Assign widget settings for the default form mode.
      $display_repository->getFormDisplay('block_content', $block_type_id)
        ->setComponent('body', [
          'type' => 'text_textarea',
        ])
        ->save();

      // Assign display settings for default view mode.
      $display_repository->getViewDisplay('block_content', $block_type_id)
        ->setComponent('body', [
          'label' => 'hidden',
          'type' => 'text_default',
        ])
        ->save();
    }

    return $field;
  }

}
