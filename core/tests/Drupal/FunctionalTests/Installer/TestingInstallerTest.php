<?php

namespace Drupal\FunctionalTests\Installer;

/**
 * Tests the interactive installer installing the testing profile.
 *
 * @group Installer
 */
class TestingInstallerTest extends ConfigAfterInstallerTestBase {

  /**
   * {@inheritdoc}
   */
  protected $profile = 'testing';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Ensures that the action called successfully after installation.
   */
  public function testSiteInstallFinished() {
    $skipped_config = [];
    // FunctionalTestSetupTrait::installParameters() uses Drupal as site name
    // and simpletest@example.com as mail address.
    $skipped_config['system.site'][] = 'name: Drupal';
    $skipped_config['system.site'][] = 'mail: simpletest@example.com';
    $skipped_config['contact.form.feedback'][] = '- simpletest@example.com';
    // \Drupal\filter\Entity\FilterFormat::toArray() drops the roles of filter
    // formats.
    $skipped_config['filter.format.basic_html'][] = 'roles:';
    $skipped_config['filter.format.basic_html'][] = '- authenticated';
    $skipped_config['filter.format.full_html'][] = 'roles:';
    $skipped_config['filter.format.full_html'][] = '- administrator';
    $skipped_config['filter.format.restricted_html'][] = 'roles:';
    $skipped_config['filter.format.restricted_html'][] = '- anonymous';
    // The site UUID is set dynamically for each installation.
    $skipped_config['system.site'][] = 'uuid: ' . $this->config('system.site')->get('uuid');

    $this->assertInstalledConfig($skipped_config);

    // Check that drupal installed successfully.
    $this->assertText('Congratulations, you installed Drupal!');
    // Check that after installation finished, hook(hook_site_install_finished)
    // called successfully.
    $this->assertText('Perform action after the installation.');
  }

}
