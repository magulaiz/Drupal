<?php

declare(strict_types=1);

namespace Drupal\block_content_theme_suggestions_test\Hook;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\block_content\Entity\BlockContent;

/**
 * Hook implementations for block_content_theme_suggestions_test.
 */
class BlockContentThemeSuggestionsTestHooks {

  /**
   * Implements hook_entity_extra_field_info().
   */
  #[Hook('entity_extra_field_info')]
  public function entityExtraFieldInfo(): array {
    // Add an extra field to the test bundle.
    $extra['node']['bundle_with_extra_field']['display']['block_content_extra_field_test'] = [
      'label' => t('Extra field'),
      'description' => t('Extra field description'),
      'weight' => 0,
    ];
    return $extra;
  }

  /**
   * Implements hook_entity_node_view().
   */
  #[Hook('entity_node_view')]
  public function entityNodeView(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, string $view_mode): void {
    // Provide content for the extra field in the form of a content block.
    if ($display->getComponent('block_content_extra_field_test')) {
      $block_content = BlockContent::create([
        'info' => 'test',
        'type' => 'basic',
        'langcode' => 'en',
      ]);
      $block_content->save();
      $build['block_content_extra_field_test'] = \Drupal::entityTypeManager()->getViewBuilder('block_content')->view($block_content);
    }
  }

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme(): array {
    // It is necessary to explicitly register the template via hook_theme()
    // because it is added via a module, not a theme.
    return [
      'block__block_content__view_type__basic__full' => [
        'base hook' => 'block',
      ],
    ];
  }

}
