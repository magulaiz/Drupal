<?php

namespace Drupal\BuildTests\Core\Command;

use Drupal\BuildTests\QuickStart\QuickStartTestBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Test\TestDatabase;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Tests install command using configured database driver.
 *
 * @group Command
 */
class InstallCommandTest extends QuickStartTestBase {

  /**
   * The PHP executable path.
   *
   * @var string
   */
  protected $php;

  /**
   * A test database object.
   *
   * @var \Drupal\Core\Test\TestDatabase
   */
  protected $testDb;

  /**
   * The Drupal root directory.
   *
   * @var string
   */
  protected $root;

  /**
   * Gets the database URL.
   *
   * @return string
   *   The database URL used for the install command.
   */
  protected function getDbUrl() {
    return getenv('SIMPLETEST_DB');
  }

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $php_executable_finder = new PhpExecutableFinder();
    $this->php = $php_executable_finder->find();
    $this->root = dirname(substr(__DIR__, 0, -strlen(__NAMESPACE__)), 2);

    // Get a lock and a valid site path.
    $this->testDb = new TestDatabase();

    $this->copyCodebase();
    $this->executeCommand('COMPOSER_DISCARD_CHANGES=true composer install --no-dev --no-interaction');
    $this->assertErrorOutputContains('Generating autoload files');
  }

  /**
   * Tests the install command.
   */
  public function testInstallCommand() {
    if (version_compare(phpversion(), \Drupal::MINIMUM_SUPPORTED_PHP) < 0) {
      $this->markTestSkipped();
    }

    $connection_info = Database::convertDbUrlToConnectionInfo($this->getDbUrl(), $this->root);

    // Installs a site using the standard profile.
    $install_command = [
      $this->php,
      'core/scripts/drupal',
      'install',
      'standard',
      "--site-name='Test site {$this->testDb->getDatabasePrefix()}'",
      '--database-driver=' . $connection_info['driver'],
      '--database-name=' . $connection_info['database'],
      '--database-host=' . $connection_info['host'],
      '--database-prefix=' . $this->testDb->getDatabasePrefix(),
    ];

    if ($connection_info['driver'] !== 'sqlite') {
      $install_command[] = '--database-username=' . $connection_info['username'];
      $install_command[] = '--database-password=' . $connection_info['password'];
    }

    $this->executeCommand(implode(' ', $install_command));

    // The progress bar uses STDERR to write messages.
    $this->assertErrorOutputContains('Congratulations, you installed Drupal!');

    $this->visit('/');
    $content = $this->getMink()->getSession()->getPage()->getContent();
    $this->assertStringContainsString('Test site ' . $this->testDb->getDatabasePrefix(), $content);
  }

  /**
   * Tests the install command with a provided language.
   */
  public function testInstallWithLangcode() {
    if (version_compare(phpversion(), \Drupal::MINIMUM_SUPPORTED_PHP) < 0) {
      $this->markTestSkipped();
    }

    $connection_info = Database::convertDbUrlToConnectionInfo($this->getDbUrl(), $this->root);
    $base_url = getenv('SIMPLETEST_BASE_URL');

    // Installs a site using the standard profile with the provided locale.
    $install_command = [
      $this->php,
      'core/scripts/drupal',
      'install',
      'standard',
      '--langcode=en',
      "--site-name='Test site {$this->testDb->getDatabasePrefix()}'",
      '--database-driver=' . $connection_info['driver'],
      '--database-name=' . $connection_info['database'],
      '--database-host=' . $connection_info['host'],
      '--database-prefix=' . $this->testDb->getDatabasePrefix(),
    ];

    if ($connection_info['driver'] !== 'sqlite') {
      $install_command[] = '--database-username=' . $connection_info['username'];
      $install_command[] = '--database-password=' . $connection_info['password'];
    }

    $this->executeCommand(implode(' ', $install_command));

    // The progress bar uses STDERR to write messages.
    $this->assertErrorOutputContains('Congratulations, you installed Drupal!');

    $this->visit('/');
    $content = $this->getMink()->getSession()->getPage()->getContent();
    $this->assertStringContainsString('Test site ' . $this->testDb->getDatabasePrefix(), $content);
  }

  /**
   * Tests that an error is returned if Drupal is already installed.
   */
  public function testAlreadyInstalledError() {
    if (version_compare(phpversion(), \Drupal::MINIMUM_SUPPORTED_PHP) < 0) {
      $this->markTestSkipped();
    }

    $connection_info = Database::convertDbUrlToConnectionInfo($this->getDbUrl(), $this->root);

    // Installs a site using the standard profile.
    $install_command = [
      $this->php,
      'core/scripts/drupal',
      'install',
      'standard',
      "--site-name='Test site {$this->testDb->getDatabasePrefix()}'",
      '--database-driver=' . $connection_info['driver'],
      '--database-name=' . $connection_info['database'],
      '--database-host=' . $connection_info['host'],
      '--database-prefix=' . $this->testDb->getDatabasePrefix(),
    ];

    if ($connection_info['driver'] !== 'sqlite') {
      $install_command[] = '--database-username=' .
        $connection_info['username'];
      $install_command[] = '--database-password=' .
        $connection_info['password'];
    }

    $this->executeCommand(implode(' ', $install_command));

    // The progress bar uses STDERR to write messages.
    $this->assertErrorOutputContains('Congratulations, you installed Drupal!');

    // Tries to re-install over the top of an existing site using a separate
    // process so that the original command process is not mutated.
    $second_install = [
      $this->php,
      'core/scripts/drupal',
      'install',
      'testing',
      "--site-name='Test another site {$this->testDb->getDatabasePrefix()}'",
    ];
    $install_process = new Process($second_install, $this->root);
    $install_process->setTimeout(500);
    $result = $install_process->run();
    $this->assertStringContainsString('Drupal is already installed.', $install_process->getOutput());
    $this->assertSame(0, $result);
  }

}
