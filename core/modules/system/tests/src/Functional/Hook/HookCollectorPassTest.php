<?php

declare(strict_types=1);

namespace Drupal\Tests\system\Functional\Hook;

use Drupal\Tests\BrowserTestBase;
use Drupal\language\Entity\ConfigurableLanguage;

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
  }

  /**
   * Tests installing a module with a Drupal container call outside functions.
   *
   * If this is removed then it needs to be moved to a test that installs modules through
   * admin/modules.
   */
  public function testContainerOutsideFunction(): void {
    $this->drupalLogin($this->adminUser);

    // Install the config test module so that the configuration does actually
    // exist.
    $this->drupalGet('admin/modules');
    $this->submitForm([
      'modules[config_test][enable]' => TRUE,
    ], 'Install');
    $this->assertSession()->responseContains('Module <em class="placeholder">Configuration test</em> has been installed.');

    // Test that collection configuration clashes during a module install are
    // reported correctly.
    \Drupal::service('module_installer')->install(['language']);
    $this->rebuildContainer();
    ConfigurableLanguage::createFromLangcode('fr')->save();
    \Drupal::languageManager()
      ->getLanguageConfigOverride('fr', 'config_test.dynamic.dotted.default')
      ->set('label', 'Je suis Charlie')
      ->save();

    $this->drupalGet('admin/modules');
    $this->submitForm(['modules[config_install_fail_test][enable]' => TRUE], 'Install');
    $this->assertSession()->responseContains('Unable to install Configuration install fail test, <em class="placeholder">config_test.dynamic.dotted.default, language/fr/config_test.dynamic.dotted.default</em> already exist in active configuration.');
  }

}
