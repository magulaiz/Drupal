<?php

namespace Drupal\Tests\shortcut\Kernel;

use Drupal\KernelTests\Core\Config\ConfigEntityValidationTestBase;
use Drupal\shortcut\Entity\ShortcutSet;

/**
 * Tests validation of shortcut_set entities.
 *
 * @group shortcut
 */
class ShortcutSetValidationTest extends ConfigEntityValidationTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['link', 'shortcut'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig('shortcut');
    $this->installEntitySchema('shortcut');

    $this->entity = ShortcutSet::create([
      'id' => 'test-shortcut-set',
      'label' => 'Test',
    ]);
    $this->entity->save();
  }

  /**
   * {@inheritdoc}
   */
  public function providerInvalidMachineNameCharacters(): array {
    $data = parent::providerInvalidMachineNameCharacters();
    // Shortcut set machine names allow dashes, but not underscores.
    unset($data['dash separated']);
    $data['underscore separated'] = ['invalid_name'];
    return $data;
  }

}
