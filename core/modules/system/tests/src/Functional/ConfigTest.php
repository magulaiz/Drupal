<?php

declare(strict_types = 1);

namespace Drupal\Tests\system\Functional;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;
use Drupal\user\UserInterface;

/**
 * Config test.
 *
 * @group system
 */
class ConfigTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
  ];

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * The admin user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected UserInterface $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->configFactory = $this->container->get('config.factory');
    $this->adminUser = $this->drupalCreateUser($this->getAdminUserPermissions());
  }

  /**
   * The list of admin user permissions.
   *
   * @return array
   *   The list of admin user permissions.
   */
  protected function getAdminUserPermissions(): array {
    return [
      'administer site configuration',
    ];
  }

  /**
   * Test config value.
   */
  public function testWorking(): void {
    $this->drupalLogin($this->adminUser);
    $this->drupalGet(Url::fromRoute('system.site_information_settings'));
    $this->submitForm([
      'site_name' => 'my name',
      'site_frontpage' => '/user',
    ], $this->t('Save configuration'));

    $config = $this->configFactory->get('system.site');
    $this->assertEquals('my name', $config->get('name'));
  }

  /**
   * Test config value.
   */
  public function testNotWorking(): void {
    $config = $this->configFactory->get('system.site');
    $this->assertEquals('Drupal', $config->get('name'));

    $this->drupalLogin($this->adminUser);
    $this->drupalGet(Url::fromRoute('system.site_information_settings'));
    $this->submitForm([
      'site_name' => 'my name',
      'site_frontpage' => '/user',
    ], $this->t('Save configuration'));

    $config = $this->configFactory->get('system.site');
    $this->assertEquals('my name', $config->get('name'));
  }

}
