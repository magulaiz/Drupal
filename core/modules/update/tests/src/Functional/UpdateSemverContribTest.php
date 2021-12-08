<?php

namespace Drupal\Tests\update\Functional;

/**
 * Tests the Update Manager module with a contrib module with semver versions.
 *
 * @group update
 */
class UpdateSemverContribTest extends UpdateSemverTestBase {

  /**
   * {@inheritdoc}
   */
  protected $updateTableLocator = 'table.update:nth-of-type(2)';

  /**
   * {@inheritdoc}
   */
  protected $updateProject = 'semver_test';

  /**
   * {@inheritdoc}
   */
  protected $projectTitle = 'Semver Test';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['semver_test'];

  /**
   * {@inheritdoc}
   */
  protected function setProjectInstalledVersion($version) {
    $system_info = [
      $this->updateProject => [
        'project' => $this->updateProject,
        'version' => $version,
        'hidden' => FALSE,
      ],
      // Ensure Drupal core on the same version for all test runs.
      'drupal' => [
        'project' => 'drupal',
        'version' => '8.0.0',
        'hidden' => FALSE,
      ],
    ];
    $this->config('update_test.settings')->set('system_info', $system_info)->save();
  }

  /**
   * {@inheritdoc}
   */
  protected function standardTests(): void {
    parent::standardTests();
    // Ensure we are always testing with the same version of Drupal Core.
    $core_update_table_selector = 'table.update:nth-of-type(1)';
    $this->assertSession()->elementContains('css', $core_update_table_selector, 'Up to date');
    $this->assertSession()->elementContains('css', $core_update_table_selector, '8.0.0');
  }

}
