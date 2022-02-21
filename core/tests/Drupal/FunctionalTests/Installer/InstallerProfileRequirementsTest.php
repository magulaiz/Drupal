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
    // Override, since we expect an error here, and the parent asserts there is
    // no error.
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
  }

}
