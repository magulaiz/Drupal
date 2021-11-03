<?php

namespace Drupal\Tests\user\Functional\Views;

/**
 * Checks changing entity and field access.
 *
 * @group user
 */
class UserFieldsAccessChangeTest extends UserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['user_access_test'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'classy';

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = ['test_user_fields_access'];

  /**
   * Tests if another module can change field access.
   */
  public function testUserFieldAccess() {
    $this->drupalGet('test_user_fields_access');

    // User has access to name and created date by default.
    $this->assertSession()->pageTextContains('Name');
    $this->assertSession()->pageTextContains('Created');

    // User does not by default have access to init, mail and status.
    $this->assertSession()->pageTextNotContains('Init');
    $this->assertSession()->pageTextNotContains('Email');
    $this->assertSession()->pageTextNotContains('Status');

    // Assign sub-admin role to grant extra access.
    $user = $this->drupalCreateUser(['sub-admin']);
    $this->drupalLogin($user);
    $this->drupalGet('test_user_fields_access');

    // Access for init, mail and status is added in hook_entity_field_access().
    $this->assertSession()->pageTextContains('Init');
    $this->assertSession()->pageTextContains('Email');
    $this->assertSession()->pageTextContains('Status');
  }

  /**
   * Tests the user name formatter shows a link to the user when there is
   * access but not otherwise.
   */
  public function testUserNameLink() {
    $user_to_check = $this->drupalCreateUser();

    // No access, no username.
    $test_user = $this->drupalCreateUser();
    $this->drupalLogin($test_user);
    $this->drupalGet('test_user_fields_access');
    $this->assertSession()->pageTextNotContains($user_to_check->getAccountName());

    $xpath = "//td/a[.='" . $user_to_check->getAccountName() . "'][@class='username']/@href[.='" . $user_to_check->toUrl()->toString() . "']";

    // Has view usernames permission but no view user profiles.
    $test_user = $this->drupalCreateUser(['view usernames']);
    $this->drupalLogin($test_user);
    $this->drupalGet('test_user_fields_access');
    $this->assertSession()->pageTextContains($user_to_check->getAccountName());
    $result = $this->xpath($xpath);
    $this->assertCount(0, $result, "{$user_to_check->getAccountName()} user is not a link");

    // Assign sub-admin role to grant extra access.
    $test_user = $this->drupalCreateUser(['sub-admin', 'view usernames']);
    $this->drupalLogin($test_user);
    $this->drupalGet('test_user_fields_access');
    $result = $this->xpath($xpath);
    $this->assertCount(1, $result, "{$user_to_check->getAccountName()} user is a link");
  }

}
