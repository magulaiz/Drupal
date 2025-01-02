<?php

declare(strict_types=1);

namespace Drupal\Tests\user\Functional;

use Drupal\Core\Cache\Cache;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests user edit page.
 *
 * @group user
 */
class UserEditTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests user edit page.
   */
  public function testUserEdit(): void {
    // Test user edit functionality.
    $user1 = $this->drupalCreateUser(['change own username']);
    $user2 = $this->drupalCreateUser([]);
    $this->drupalLogin($user1);

    // Test that error message appears when attempting to use a non-unique user name.
    $edit['name'] = $user2->getAccountName();
    $this->drupalGet("user/" . $user1->id() . "/edit");
    $this->submitForm($edit, 'Save');
    $this->assertSession()->pageTextContains("The username {$edit['name']} is already taken.");

    // Check that the default value in user name field
    // is the raw value and not a formatted one.
    \Drupal::state()->set('user_hooks_test_user_format_name_alter', TRUE);
    \Drupal::service('module_installer')->install(['user_hooks_test']);
    Cache::invalidateTags(['rendered']);
    $this->drupalGet('user/' . $user1->id() . '/edit');
    $this->assertSession()->fieldValueEquals('name', $user1->getAccountName());

    // Ensure the formatted name is displayed when expected.
    $this->drupalGet('user/' . $user1->id());
    $this->assertSession()->responseContains($user1->getDisplayName());
    $this->assertSession()->titleEquals(strip_tags($user1->getDisplayName()) . ' | Drupal');

    // Check that filling out a single password field does not validate.
    $edit = [];
    $edit['pass[pass1]'] = '';
    $edit['pass[pass2]'] = $this->randomMachineName();
    $this->drupalGet("user/" . $user1->id() . "/edit");
    $this->submitForm($edit, 'Save');
    $this->assertSession()->pageTextContains("The specified passwords do not match.");

    $edit['pass[pass1]'] = $this->randomMachineName();
    $edit['pass[pass2]'] = '';
    $this->drupalGet("user/" . $user1->id() . "/edit");
    $this->submitForm($edit, 'Save');
    $this->assertSession()->pageTextContains("The specified passwords do not match.");

    // Test that the error message appears when attempting to change the mail or
    // pass without the current password.
    $edit = [];
    $edit['mail'] = $this->randomMachineName() . '@new.example.com';
    $this->drupalGet("user/" . $user1->id() . "/edit");
    $this->submitForm($edit, 'Save');
    $this->assertSession()->pageTextContains("Your current password is missing or incorrect; it's required to change the Email.");

    $edit['current_pass'] = $user1->passRaw;
    $this->drupalGet("user/" . $user1->id() . "/edit");
    $this->submitForm($edit, 'Save');
    $this->assertSession()->pageTextContains("The changes have been saved.");

    // Test that the user must enter current password before changing passwords.
    $edit = [];
    $edit['pass[pass1]'] = $new_pass = $this->randomMachineName();
    $edit['pass[pass2]'] = $new_pass;
    $this->drupalGet("user/" . $user1->id() . "/edit");
    $this->submitForm($edit, 'Save');
    $this->assertSession()->pageTextContains("Your current password is missing or incorrect; it's required to change the Password.");

    // Try again with the current password.
    $edit['current_pass'] = $user1->passRaw;
    $this->drupalGet("user/" . $user1->id() . "/edit");
    $this->submitForm($edit, 'Save');
    $this->assertSession()->pageTextContains("The changes have been saved.");

    // Confirm there's only one session in the database as the existing session
    // has been migrated when the password is changed.
    // @see \Drupal\user\Entity\User::postSave()
    $this->assertSame(1, (int) \Drupal::database()->select('sessions', 's')->countQuery()->execute()->fetchField());

    // Make sure the changed timestamp is updated.
    $this->assertEquals(\Drupal::time()->getRequestTime(), $user1->getChangedTime(), 'Changing a user sets "changed" timestamp.');

    // Make sure the user can log in with their new password.
    $this->drupalLogout();
    $user1->passRaw = $new_pass;
    $this->drupalLogin($user1);
    $this->drupalLogout();

    // Test that the password strength indicator displays.
    $config = $this->config('user.settings');
    $this->drupalLogin($user1);

    $config->set('password_strength', TRUE)->save();
    $this->drupalGet("user/" . $user1->id() . "/edit");
    $this->submitForm($edit, 'Save');
    $this->assertSession()->responseContains("Password strength:");

    $config->set('password_strength', FALSE)->save();
    $this->drupalGet("user/" . $user1->id() . "/edit");
    $this->submitForm($edit, 'Save');
    $this->assertSession()->responseNotContains("Password strength:");

    // Check that the user status field has the correct value and that it is
    // properly displayed.
    $admin_user = $this->drupalCreateUser(['administer users']);
    $this->drupalLogin($admin_user);

    $this->drupalGet('user/' . $user1->id() . '/edit');
    $this->assertSession()->checkboxNotChecked('edit-status-0');
    $this->assertSession()->checkboxChecked('edit-status-1');

    $edit = ['status' => 0];
    $this->drupalGet('user/' . $user1->id() . '/edit');
    $this->submitForm($edit, 'Save');
    $this->assertSession()->pageTextContains('The changes have been saved.');
    $this->assertSession()->checkboxChecked('edit-status-0');
    $this->assertSession()->checkboxNotChecked('edit-status-1');

    $edit = ['status' => 1];
    $this->drupalGet('user/' . $user1->id() . '/edit');
    $this->submitForm($edit, 'Save');
    $this->assertSession()->pageTextContains('The changes have been saved.');
    $this->assertSession()->checkboxNotChecked('edit-status-0');
    $this->assertSession()->checkboxChecked('edit-status-1');
  }

}
