<?php

namespace Drupal\Tests\system\Functional\Update;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;
use Drupal\user\Entity\User;

/**
 * Tests that users created with Drupal prior to version 10.1.x can still login.
 *
 * @group Update
 */
class PasswordCompatibilityUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../tests/fixtures/update/drupal-9.4.0.phpass.standard.php.gz',
    ];
  }

  /**
   * Tests that the password compatibility is working properly.
   */
  public function testPasswordCompatibility() {
    // Ensure phpass extension is not yet enabled.
    $this->assertArrayNotHasKey('phpass', $this->config('core.extension')->get('module'));

    // Log in as user test1 with password "drupal".
    // This account still uses the original phpass hash.
    $account1 = User::load(2);
    $account1->passRaw = 'drupal';
    $this->drupalLogin($account1);
    $this->drupalLogout();

    $this->runUpdates();

    // Ensure phpass extension is enabled.
    $this->assertArrayHasKey('phpass', $this->config('core.extension')->get('module'));

    // Log in as user test1 with password "drupal".
    // This account now uses the native php password hash.
    $account1 = User::load(2);
    $account1->passRaw = 'drupal';
    $this->drupalLogin($account1);
    $this->drupalLogout();

    // Log in as user test2 with password "drupal".
    // This account still uses the original phpass hash.
    $account2 = User::load(3);
    $account2->passRaw = 'drupal';
    $this->drupalLogin($account2);
    $this->drupalLogout();
  }

}
