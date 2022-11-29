<?php

namespace Drupal\Tests\editor\Kernel;

use Drupal\editor\Entity\Editor;
use Drupal\filter\Entity\FilterFormat;
use Drupal\KernelTests\Core\Config\ConfigEntityValidationTestBase;

/**
 * Tests validation of editor entities.
 *
 * @group editor
 */
class EditorValidationTest extends ConfigEntityValidationTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['editor', 'editor_test', 'filter'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $format = FilterFormat::create([
      'format' => 'test',
      'name' => 'Test',
    ]);
    $format->save();

    $this->entity = Editor::create([
      'format' => $format->id(),
      'editor' => 'unicorn',
    ]);
    $this->entity->save();
  }

}
