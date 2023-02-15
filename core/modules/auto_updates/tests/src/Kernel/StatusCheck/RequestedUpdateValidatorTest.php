<?php

declare(strict_types = 1);

namespace Drupal\Tests\auto_updates\Kernel\StatusCheck;

use Drupal\package_manager\Exception\StageValidationException;
use Drupal\package_manager\ValidationResult;
use Drupal\Tests\auto_updates\Kernel\AutoUpdatesKernelTestBase;

/**
 * @coversDefaultClass \Drupal\auto_updates\Validator\RequestedUpdateValidator
 * @group auto_updates
 * @internal
 */
class RequestedUpdateValidatorTest extends AutoUpdatesKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'auto_updates',
    'auto_updates_test',
  ];

  /**
   * Tests error message is shown if the core version is not updated.
   */
  public function testErrorMessageOnCoreNotUpdated(): void {
    // Update `drupal/core-recommended` to a version that does not match the
    // requested version of '9.8.1'. This also does not update all packages that
    // are expected to be updated when updating Drupal core.
    // @see \Drupal\auto_updates\Updater::begin()
    // @see \Drupal\package_manager\ComposerUtility::getCorePackages()
    $this->getStageFixtureManipulator()->setVersion('drupal/core-recommended', '9.8.2');
    $this->setCoreVersion('9.8.0');
    $this->setReleaseMetadata([
      'drupal' => __DIR__ . '/../../../../package_manager/tests/fixtures/release-history/drupal.9.8.1-security.xml',
    ]);
    $this->container->get('module_installer')->install(['auto_updates']);

    /** @var \Drupal\auto_updates\Updater $updater */
    $updater = $this->container->get('auto_updates.updater');
    $updater->begin(['drupal' => '9.8.1']);
    $updater->stage();

    $expected_results = [
      ValidationResult::createError([t("The requested update to 'drupal/core-recommended' to version '9.8.1' does not match the actual staged update to '9.8.2'.")]),
      ValidationResult::createError([t("The requested update to 'drupal/core-dev' to version '9.8.1' was not performed.")]),
    ];
    try {
      $updater->apply();
      $this->fail('Expecting an exception.');
    }
    catch (StageValidationException $exception) {
      $this->assertValidationResultsEqual($expected_results, $exception->getResults());
    }
  }

  /**
   * Tests error message is shown if there are no core packages in stage.
   */
  public function testErrorMessageOnEmptyCorePackages(): void {
    $this->getStageFixtureManipulator()
      ->removePackage('drupal/core')
      ->removePackage('drupal/core-recommended')
      ->removePackage('drupal/core-dev');

    $this->setCoreVersion('9.8.0');
    $this->setReleaseMetadata([
      'drupal' => __DIR__ . '/../../../../package_manager/tests/fixtures/release-history/drupal.9.8.1-security.xml',
    ]);
    $this->container->get('module_installer')->install(['auto_updates']);

    /** @var \Drupal\auto_updates\Updater $updater */
    $updater = $this->container->get('auto_updates.updater');
    $updater->begin(['drupal' => '9.8.1']);
    $updater->stage();
    $this->expectException(StageValidationException::class);
    $this->expectExceptionMessage('No updates detected in the staging area.');
    $updater->apply();
  }

}
