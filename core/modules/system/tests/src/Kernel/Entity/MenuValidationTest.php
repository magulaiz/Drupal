<?php

namespace Drupal\Tests\system\Kernel\Entity;

use Drupal\KernelTests\Core\Config\ConfigEntityValidationTestBase;
use Drupal\system\Entity\Menu;

/**
 * Tests validation of menu entities.
 *
 * @group system
 */
class MenuValidationTest extends ConfigEntityValidationTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entity = Menu::create([
      'id' => 'test-menu',
      'label' => 'Test',
    ]);
    $this->entity->save();
  }

  /**
   * {@inheritdoc}
   */
  public function providerInvalidMachineNameCharacters(): array {
    $data = parent::providerInvalidMachineNameCharacters();
    // Menu machine names allow dashes, but not underscores.
    unset($data['dash separated']);
    $data['underscore separated'] = ['invalid_name'];
    return $data;
  }

}
