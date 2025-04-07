<?php

declare(strict_types=1);

namespace Drupal\FunctionalTests\Hook;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests services in .module files.
 *
 * @group Hook
 */
class HookCollectorPassTest extends BrowserTestBase {

  /**
   * The admin user used in this test.
   *
   * @var \Drupal\user\Entity\User|false
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser([
      'administer modules',
      'administer themes',
      'administer site configuration',
    ]);

    // Ensure the global variable being asserted by this test does not exist;
    // a previous test executed in this request/process might have set it.
    unset($GLOBALS['hook_config_test']);
  }

  /**
   * Tests installing a module with a Drupal container call outside functions.
   *
   * If this is removed then it needs to be moved to a test that installs modules through
   * admin/modules.
   */
  public function testPreExistingConfigInstall(): void {
    $this->drupalLogin($this->adminUser);

    $this->drupalGet('admin/modules');
    $this->submitForm([
      'modules[config_test][enable]' => TRUE,
    ], 'Install');

    $this->assertSession()->responseContains('Module <em class="placeholder">Configuration test</em> has been installed.');
  }

}
