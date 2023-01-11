<?php

namespace Drupal\Tests\system\Kernel\Entity;

use Drupal\KernelTests\Core\Config\ConfigEntityValidationTestBase;
use Drupal\system\Entity\Action;

/**
 * Tests validation of action entities.
 *
 * @group system
 */
class ActionValidationTest extends ConfigEntityValidationTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entity = Action::create([
      'id' => 'test',
      'label' => 'Test',
      'type' => 'test',
      'plugin' => 'action_goto_action',
    ]);
    $this->entity->save();
  }

  /**
   * Data provider for ::testMachineName().
   *
   * @return array[]
   *   The test cases.
   */
  public function providerMachineName(): array {
    return [
      ['invalid name'],
      ['invalid-name'],
      ['Invalid_Name'],
    ];
  }

  /**
   * Tests that the action entity's ID is validated as a machine name.
   *
   * @param string $invalid_id
   *   An invalid machine name that should raise a validation error.
   *
   * @dataProvider providerMachineName
   */
  public function testMachineName(string $invalid_id): void {
    // The entity should be valid to begin with.
    $this->assertValidationErrors([]);

    $this->entity->set('id', $invalid_id);
    $this->assertValidationErrors(['This value is not valid.']);

    $this->entity->set('id', mb_strtolower($this->randomMachineName(68)));
    $this->assertValidationErrors(['This value is too long. It should have <em class="placeholder">64</em> characters or less.']);
  }

}
