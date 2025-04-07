<?php

declare(strict_types=1);

namespace Drupal\Tests\node\Traits;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;
use PHPUnit\Framework\TestCase;

/**
 * Provides methods to create content type from given values.
 *
 * This trait is meant to be used only by test classes.
 */
trait ContentTypeCreationTrait {

  /**
   * Creates a custom content type based on default settings.
   *
   * @param array $values
   *   An array of settings to change from the defaults.
   *   Example: 'type' => 'foo'.
   *
   * @return \Drupal\node\Entity\NodeType
   *   Created content type.
   */
  protected function createContentType(array $values = []) {
    // Find a non-existent random type name.
    if (!isset($values['type'])) {
      do {
        $id = $this->randomMachineName(8);
      } while (NodeType::load($id));
    }
    else {
      $id = $values['type'];
    }
    $values += [
      'type' => $id,
      'name' => $id,
    ];
    $type = NodeType::create($values);
    $status = $type->save();

    // Add body field manually.
    $field_storage = FieldStorageConfig::loadByName('node', 'body');
    $field = FieldConfig::loadByName('node', $type->id(), 'body');

    if (!$field) {
      $field = FieldConfig::create([
        'field_storage' => $field_storage,
        'bundle' => $type->id(),
        'label' => 'Body',
        'settings' => [
          'display_summary' => TRUE,
          'allowed_formats' => [],
        ],
      ]);
      $field->save();

      /** @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface $display_repository */
      $display_repository = \Drupal::service('entity_display.repository');

      // Assign widget settings for the default form mode.
      $display_repository->getFormDisplay('node', $type->id())
        ->setComponent('body', [
          'type' => 'text_textarea_with_summary',
        ])
        ->save();

      // Assign display settings for the 'default' and 'teaser' view modes.
      $display_repository->getViewDisplay('node', $type->id())
        ->setComponent('body', [
          'label' => 'hidden',
          'type' => 'text_default',
        ])
        ->save();

      // The teaser view mode is created by the Standard profile and might not exist.
      $view_modes = $display_repository->getViewModes('node');
      if (isset($view_modes['teaser'])) {
        $display_repository->getViewDisplay('node', $type->id(), 'teaser')
          ->setComponent('body', [
            'label' => 'hidden',
            'type' => 'text_summary_or_trimmed',
          ])
          ->save();
      }
    }

    if ($this instanceof TestCase) {
      $this->assertSame($status, SAVED_NEW, (new FormattableMarkup('Created content type %type.', ['%type' => $type->id()]))->__toString());
    }
    else {
      $this->assertEquals(SAVED_NEW, $status, (new FormattableMarkup('Created content type %type.', ['%type' => $type->id()]))->__toString());
    }

    return $type;
  }

}
