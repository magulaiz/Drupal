<?php

declare(strict_types=1);

namespace Drupal\Tests\system\Functional\Module;

use Drupal\entity_test\Entity\EntityTest;

/**
 * Tests PrepareModulesEntityUninstallForm.
 *
 * @group Module
 */
class PrepareModulesEntityUninstallFormTest extends ModuleTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests PrepareModulesEntityUninstallForm.
   */
  public function testModuleEntityUninstall(): void {
    // Test the page title of entity uninstall form.
    $this->drupalGet('admin/modules/uninstall/entity/user');
    $this->assertSession()->pageTextContains('Are you sure you want to delete all users?');

    $this->drupalGet('admin/modules/uninstall/entity/' . $this->randomMachineName());
    $this->assertSession()->statusCodeEquals(403);

    $this->container->get('module_installer')->install(['entity_test']);
    EntityTest::create(['name' => $this->randomMachineName()])->save();
    $this->drupalGet('admin/modules/uninstall/entity/entity_test');
    $this->assertSession()->pageTextContains('Are you sure you want to delete all test entity entities?');
    $this->submitForm([], 'Delete all test entity entities');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('All test entity entities have been deleted.');
  }

}
