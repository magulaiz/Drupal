<?php

declare(strict_types=1);

namespace Drupal\Tests\Composer\Plugin\Scaffold\Functional;

use Composer\Util\Filesystem;
use Drupal\BuildTests\Framework\BuildTestBase;
use Drupal\Tests\Composer\Plugin\Scaffold\AssertUtilsTrait;
use Drupal\Tests\Composer\Plugin\Scaffold\ExecTrait;
use Drupal\Tests\Composer\Plugin\Scaffold\Fixtures;

/**
 * Tests Composer Hooks that run scaffold operations.
 *
 * The purpose of this test file is to exercise all of the different Composer
 * commands that invoke scaffold operations, and ensure that files are
 * scaffolded when they should be.
 *
 * Note that this test file uses `exec` to run Composer for a pure functional
 * test. Other functional test files invoke Composer commands directly via the
 * Composer Application object, in order to get more accurate test coverage
 * information.
 *
 * @group Scaffold
 */
class ComposerHookTest extends BuildTestBase {

  use ExecTrait;
  use AssertUtilsTrait;

  /**
   * Directory to perform the tests in.
   *
   * @var string
   */
  protected $fixturesDir;

  /**
   * The Symfony FileSystem component.
   *
   * @var \Symfony\Component\Filesystem\Filesystem
   */
  protected $fileSystem;

  /**
   * The Fixtures object.
   *
   * @var \Drupal\Tests\Composer\Plugin\Scaffold\Fixtures
   */
  protected $fixtures;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->fileSystem = new Filesystem();
    $this->fixtures = new Fixtures();
    $this->fixtures->createIsolatedComposerCacheDir();
    $this->fixturesDir = $this->fixtures->tmpDir($this->name());
    $replacements = ['SYMLINK' => 'false', 'PROJECT_ROOT' => $this->fixtures->projectRoot()];
    $this->fixtures->cloneFixtureProjects($this->fixturesDir, $replacements);
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown(): void {
    // Remove any temporary directories et. al. that were created.
    $this->fixtures->tearDown();

    parent::tearDown();
  }

  /**
   * Tests to see if scaffold operation runs after 'composer require'.
   */
  public function testRequireHooks() {
    $topLevelProjectDir = 'composer-hooks-fixture';
    $sut = $this->fixturesDir . '/' . $topLevelProjectDir;

    // First test: run composer install. This is the same as composer update
    // since there is no lock file. Ensure that scaffold operation ran.
    $this->mustExec("composer install --no-ansi", $sut);
    $this->assertScaffoldedFile($sut . '/sites/default/default.settings.php', FALSE, 'Test version of default.settings.php from drupal/core');

    // Run composer required to add in the scaffold-override-fixture. This
    // project is "allowed" in our main fixture project, but not required.
    // We expect that requiring this library should re-scaffold, resulting
    // in a changed default.settings.php file.
    $stdout = $this->mustExec("composer require --no-ansi --no-interaction fixtures/drupal-assets-fixture:dev-main fixtures/scaffold-override-fixture:dev-main", $sut);
    $this->assertScaffoldedFile($sut . '/sites/default/default.settings.php', FALSE, 'scaffolded from the scaffold-override-fixture');

    // Make sure that the appropriate notice informing us that scaffolding
    // is allowed was printed.
    $this->assertStringContainsString('Package fixtures/scaffold-override-fixture has scaffold operations, and is already allowed in the root-level composer.json file.', $stdout);
  }

  /**
   * Tests to see if deleted / modified files are handled correctly.
   */
  public function testDeletedAndModifiedScaffoldFiles() {
    $topLevelProjectDir = 'composer-hooks-fixture';
    $sut = $this->fixturesDir . '/' . $topLevelProjectDir;

    // Run 'composer install' again to set up for our next set of tests.
    $this->mustExec("composer install --no-ansi", $sut);

    // Delete one scaffold file, just for test purposes, then run
    // 'composer update' and see if the scaffold file is replaced.
    @unlink($sut . '/sites/default/default.settings.php');
    $this->assertFileDoesNotExist($sut . '/sites/default/default.settings.php');
    $this->mustExec("composer update --no-ansi", $sut);
    $this->assertScaffoldedFile($sut . '/sites/default/default.settings.php', FALSE, 'Test version of default.settings.php from drupal/core');

    // Delete the same test scaffold file again, then run
    // 'composer drupal:scaffold' and see if the scaffold file is
    // re-scaffolded.
    @unlink($sut . '/sites/default/default.settings.php');
    $this->assertFileDoesNotExist($sut . '/sites/default/default.settings.php');
    $this->mustExec("composer install --no-ansi", $sut);
    $this->assertScaffoldedFile($sut . '/sites/default/default.settings.php', FALSE, 'Test version of default.settings.php from drupal/core');

    // Delete the same test scaffold file yet again, then run
    // 'composer install' and see if the scaffold file is re-scaffolded.
    @unlink($sut . '/sites/default/default.settings.php');
    $this->assertFileDoesNotExist($sut . '/sites/default/default.settings.php');
    $this->mustExec("composer drupal:scaffold --no-ansi", $sut);
    $this->assertScaffoldedFile($sut . '/sites/default/default.settings.php', FALSE, 'Test version of default.settings.php from drupal/core');

    // Modify the same test scaffold file, then run 'composer drupal:scaffold'
    // and confirm the modification is not overwritten.
    $contents = file_get_contents($sut . '/sites/default/default.settings.php');
    file_put_contents($sut . '/sites/default/default.settings.php', $contents . "\n// Simulated user modification\n");
    $stdout = $this->mustExec("composer drupal:scaffold --no-ansi --no-interaction 2>&1", $sut);
    $this->assertEquals("Scaffolding files for fixtures/drupal-assets-fixture:\n  - Skip [web-root]/.htaccess: overridden in fixtures/drupal-drupal\nScaffolding files for fixtures/drupal-drupal:\n  - Skip [web-root]/.htaccess: disabled\n", $stdout);
    $post_scaffold_contents = file_get_contents($sut . '/sites/default/default.settings.php');
    $this->assertContains('Simulated user modification', $post_scaffold_contents);

    // Finally, run 'composer drupal:scaffold' yet a third time in a row, this
    // time with an environment variable set that tells drupal:scaffold to act
    // as if the user had requested that modified files be overwritten.
    $stdout = $this->mustExec("composer drupal:scaffold --no-ansi --no-interaction 2>&1", $sut, ['DRUPAL_SCAFFOLD_DISCARD_MODIFIED' => 1]);
    $this->assertContains("The following managed scaffold files have been modified:\n  - [web-root]/sites/default/default.settings.php", $stdout);
    $post_scaffold_contents_three = file_get_contents($sut . '/sites/default/default.settings.php');
    $this->assertNotContains('Simulated user modification', $post_scaffold_contents_three);

    // And if we run it yet again, then the file should not be modified.
    $stdout = $this->mustExec("composer drupal:scaffold --no-ansi --no-interaction 2>&1", $sut);
    $this->assertNotContains("The following managed scaffold files have been modified:\n  - [web-root]/sites/default/default.settings.php", $stdout);
    $post_scaffold_contents_three = file_get_contents($sut . '/sites/default/default.settings.php');
    $this->assertNotContains('Simulated user modification', $post_scaffold_contents_three);

    // Modify the test scaffold file even again, then run
    // 'composer drupal:scaffold' with DRUPAL_SCAFFOLD_KEEP_MODIFIED. Confirm
    // that the file is now excluded in the composer.json file's file-mapping.
    $contents = file_get_contents($sut . '/sites/default/default.settings.php');
    file_put_contents($sut . '/sites/default/default.settings.php', $contents . "\n// Simulated user modification\n");
    $stdout = $this->mustExec("composer drupal:scaffold --no-ansi --no-interaction 2>&1", $sut, ['DRUPAL_SCAFFOLD_KEEP_MODIFIED' => 1]);
    $this->assertContains("The following managed scaffold files have been modified:\n  - [web-root]/sites/default/default.settings.php", $stdout);
    $post_scaffold_contents = file_get_contents($sut . '/sites/default/default.settings.php');
    $this->assertContains('Simulated user modification', $post_scaffold_contents);
    $post_scaffold_composer_json = file_get_contents($sut . '/composer.json');
    $this->assertContains('"[web-root]/sites/default/default.settings.php": false', $post_scaffold_composer_json);

    // Scaffold again. We should not modify the files we kept
    // (default.settings.php) above.
    $stdout = $this->mustExec("composer drupal:scaffold --no-ansi --no-interaction 2>&1", $sut, ['DRUPAL_SCAFFOLD_DISCARD_MODIFIED' => 1]);
    $this->assertNotContains("The following managed scaffold files have been modified:\n  - [web-root]/sites/default/default.settings.php", $stdout);
  }

  /**
   * Tests deleting and modifying two different scaffold files at the same time.
   */
  public function testDeletingAndModifyingAtSameTime() {
    $topLevelProjectDir = 'composer-hooks-fixture';
    $sut = $this->fixturesDir . '/' . $topLevelProjectDir;

    // Run 'composer install' again to set up for our next set of tests.
    $this->mustExec("composer install --no-ansi", $sut);

    // Delete 'default.settings.php' and modify 'robots.txt', then run
    // the scaffold operation and see if the default settings file comes back,
    // and the robots.txt file modification is preserved.
    @unlink($sut . '/sites/default/default.settings.php');
    $this->assertFileNotExists($sut . '/sites/default/default.settings.php');
    $contents = file_get_contents($sut . '/robots.txt');
    file_put_contents($sut . '/robots.txt', $contents . "\n# Simulated user modification\n");
    $stdout = $this->mustExec("composer drupal:scaffold --no-ansi --no-interaction 2>&1", $sut);
    $this->assertScaffoldedFile($sut . '/sites/default/default.settings.php', FALSE, 'Test version of default.settings.php from drupal/core');
    $post_scaffold_contents = file_get_contents($sut . '/robots.txt');
    $this->assertContains('Simulated user modification', $post_scaffold_contents);

    // Run the same test again to ensure that the scaffold tool still
    // understands that the deleted file is managed.
    @unlink($sut . '/sites/default/default.settings.php');
    $this->assertFileNotExists($sut . '/sites/default/default.settings.php');
    $stdout = $this->mustExec("composer drupal:scaffold --no-ansi --no-interaction 2>&1", $sut);
    $this->assertScaffoldedFile($sut . '/sites/default/default.settings.php', FALSE, 'Test version of default.settings.php from drupal/core');
    $post_scaffold_contents = file_get_contents($sut . '/robots.txt');
    $this->assertContains('Simulated user modification', $post_scaffold_contents);

    // Make the same modifications again, but this time we will run the scaffold
    // command with 'DRUPAL_SCAFFOLD_DISCARD_MODIFIED' environment variable,
    // simulating the user selecting 'y' from the "discard modified" prompt.
    // Make sure that both files go back the way they were.
    @unlink($sut . '/sites/default/default.settings.php');
    $this->assertFileNotExists($sut . '/sites/default/default.settings.php');
    $contents = file_get_contents($sut . '/robots.txt');
    file_put_contents($sut . '/robots.txt', $contents . "\n# Simulated user modification\n");
    $stdout = $this->mustExec("composer drupal:scaffold --no-ansi --no-interaction 2>&1", $sut, ['DRUPAL_SCAFFOLD_DISCARD_MODIFIED' => 1]);
    $this->assertScaffoldedFile($sut . '/sites/default/default.settings.php', FALSE, 'Test version of default.settings.php from drupal/core');
    $post_scaffold_contents = file_get_contents($sut . '/robots.txt');
    $this->assertNotContains('Simulated user modification', $post_scaffold_contents);

    // Now we'll do it again with 'DRUPAL_SCAFFOLD_KEEP_MODIFIED', to simulate
    // answering 'keep' to the "discard modified" prompt. Confirm that the
    // composer.json file is modified.
    @unlink($sut . '/sites/default/default.settings.php');
    $this->assertFileNotExists($sut . '/sites/default/default.settings.php');
    $contents = file_get_contents($sut . '/robots.txt');
    file_put_contents($sut . '/robots.txt', $contents . "\n# Simulated user modification\n");
    $stdout = $this->mustExec("composer drupal:scaffold --no-ansi --no-interaction 2>&1", $sut, ['DRUPAL_SCAFFOLD_KEEP_MODIFIED' => 1]);
    $this->assertScaffoldedFile($sut . '/sites/default/default.settings.php', FALSE, 'Test version of default.settings.php from drupal/core');
    $post_scaffold_contents = file_get_contents($sut . '/robots.txt');
    $this->assertContains('Simulated user modification', $post_scaffold_contents);
    $post_scaffold_composer_json = file_get_contents($sut . '/composer.json');
    $this->assertContains('"[web-root]/robots.txt": false', $post_scaffold_composer_json);
  }

  /**
   * Tests to see if create-project scaffolds correctly.
   */
  public function testCreateProject() {
    // Run 'composer create-project' to create a new test project called
    // 'create-project-test', which is a copy of 'fixtures/drupal-drupal'.
    $sut = $this->fixturesDir . '/create-project-test';
    $filesystem = new Filesystem();
    $filesystem->remove($sut);
    $stdout = $this->mustExec("composer create-project --repository=packages.json fixtures/drupal-drupal {$sut}", $this->fixturesDir, ['COMPOSER_MIRROR_PATH_REPOS' => 1]);
    $this->assertDirectoryExists($sut);
    $this->assertStringContainsString('Scaffolding files for fixtures/drupal-drupal', $stdout);
    $this->assertScaffoldedFile($sut . '/index.php', FALSE, 'Test version of index.php from drupal/core');
  }

  /**
   * Tests to see if scaffold messages are omitted when running scaffold twice.
   */
  public function testScaffoldMessagesDoNotPrintTwice() {
    $topLevelProjectDir = 'drupal-drupal';
    $sut = $this->fixturesDir . '/' . $topLevelProjectDir;

    // First test: run composer install. This is the same as composer update
    // since there is no lock file. Ensure that scaffold operation ran.
    $stdout = $this->mustExec("composer install --no-ansi", $sut);

    $this->assertStringContainsString('- Copy [web-root]/index.php from assets/index.php', $stdout);
    $this->assertStringContainsString('- Copy [web-root]/update.php from assets/update.php', $stdout);

    // Run scaffold operation again. It should not print anything.
    $stdout = $this->mustExec("composer scaffold --no-ansi", $sut);

    $this->assertEquals('', $stdout);

    // Delete a file and run it again. It should re-scaffold the removed file.
    unlink("$sut/index.php");
    $stdout = $this->mustExec("composer scaffold --no-ansi", $sut);
    $this->assertStringContainsString('- Copy [web-root]/index.php from assets/index.php', $stdout);
    $this->assertStringNotContainsString('- Copy [web-root]/update.php from assets/update.php', $stdout);
  }

}
