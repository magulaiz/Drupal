<?php

namespace Drupal\Tests\views\Kernel\Entity;

use Drupal\KernelTests\Core\Config\ConfigEntityValidationTestBase;
use Drupal\views\Entity\View;

/**
 * Tests validation of view entities.
 *
 * @group views
 */
class ViewValidationTest extends ConfigEntityValidationTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['views'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entity = View::create([
      'id' => 'test',
      'label' => 'Test',
    ]);
    $this->entity->save();
  }

  /**
   * Tests that a view's display plugin ID is validated.
   */
  public function testInvalidDisplayPluginId(): void {
    $display = &$this->entity->getDisplay('default');
    $display['display_plugin'] = 'non_existent';
    $this->assertValidationErrors([
      'display.default.display_plugin' => "The 'non_existent' plugin does not exist.",
    ]);
  }

  /**
   * Tests that a view's display plugin ID is validated.
   */
  public function testInvalidPagerPluginId(): void {
    $display = &$this->entity->getDisplay('default');
    $display['display_options']['pager']['type'] = 'non_existent';
    $this->assertValidationErrors([
      'display.default.display_options.pager.type' => "The 'non_existent' plugin does not exist.",
    ]);
  }

}
