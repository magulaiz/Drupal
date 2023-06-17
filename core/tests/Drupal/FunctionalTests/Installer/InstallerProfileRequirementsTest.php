<?php

namespace Drupal\FunctionalTests\Installer;

/**
 * Tests that an install profile can implement hook_requirements().
 *
 * @group Installer
 */
class InstallerProfileRequirementsTest extends InstallerTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected $profile = 'testing_requirements';

  /**
   * {@inheritdoc}
   */
  protected function setUpSettings() {
    // This form will never be reached.
  }

  /**
   * {@inheritdoc}
   */
  protected function setUpRequirementsProblem() {
    // The parent method asserts that there are no requirements errors, but
    // this test expects a requirements error in the test method below.
    // Therefore, we override this method to suppress the parent's assertions.
  }

  /**
   * {@inheritdoc}
   */
  protected function setUpSite() {
    // This form will never be reached.
  }

  /**
   * Assert that the profile failed hook_requirements().
   */
  public function testHookRequirementsFailure() {
    $this->assertSession()->pageTextContains('Testing requirements failed requirements.');
    // Profile PHP requirements.
    $this->assertSession()->pageTextContains('Warnings found');
    $this->assertSession()->pageTextContains('Consider increasing your PHP memory limit to 123456789M to help prevent errors in the installation process.');
    $this->assertSession()->pageTextContains('Your PHP installation is too old. Drupal requires at least PHP 123456789.');
  }

}
