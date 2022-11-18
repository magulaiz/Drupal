<?php

namespace Drupal\Tests\user\Functional;

use Drupal\Core\Test\AssertMailTrait;
use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;

/**
 * Ensure that login works as expected.
 *
 * @group user
 */
class UserLoginTest extends BrowserTestBase {

  use AssertMailTrait {
    getMails as drupalGetMails;
  }

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['dblog'];

  /**
   * Tests login with destination.
   */
  public function testLoginCacheTagsAndDestination() {
    $this->drupalGet('user/login');
    // The user login form says "Enter your <site name> username.", hence it
    // depends on config:system.site, and its cache tags should be present.
    $this->assertSession()->responseHeaderContains('X-Drupal-Cache-Tags', 'config:system.site');

    $user = $this->drupalCreateUser([]);
    $this->drupalGet('user/login', ['query' => ['destination' => 'foo']]);
    $edit = ['name' => $user->getAccountName(), 'pass' => $user->passRaw];
    $this->submitForm($edit, 'Log in');
    $this->assertSession()->addressEquals('foo');
  }

  /**
   * Tests the global login flood control.
   */
  public function testGlobalLoginFloodControl() {
    $this->config('user.flood')
      ->set('ip_limit', 10)
      // Set a high per-user limit out so that it is not relevant in the test.
      ->set('user_limit', 4000)
      ->save();

    $user1 = $this->drupalCreateUser([]);
    $incorrect_user1 = clone $user1;
    $incorrect_user1->passRaw .= 'incorrect';

    // Try 2 failed logins.
    for ($i = 0; $i < 2; $i++) {
      $this->assertFailedLogin($incorrect_user1);
    }

    // A successful login will not reset the IP-based flood control count.
    $this->drupalLogin($user1);
    $this->drupalLogout();

    // Try 8 more failed logins, they should not trigger the flood control
    // mechanism.
    for ($i = 0; $i < 8; $i++) {
      $this->assertFailedLogin($incorrect_user1);
    }

    // The next login trial should result in an IP-based flood error message.
    $this->assertFailedLogin($incorrect_user1, 'ip');

    // A login with the correct password should also result in a flood error
    // message.
    $this->assertFailedLogin($user1, 'ip');

    // A login attempt after resetting the password should still fail, since the
    // IP-based flood control count is not cleared after a password reset.
    $this->resetUserPassword($user1);
    $this->drupalLogout();
    $this->assertFailedLogin($user1, 'ip');
    $this->assertSession()->responseContains('Too many failed login attempts from your IP address.');
  }

  /**
   * Tests the per-user login flood control.
   */
  public function testPerUserLoginFloodControl() {
    $this->config('user.flood')
      // Set a high global limit out so that it is not relevant in the test.
      ->set('ip_limit', 4000)
      ->set('user_limit', 3)
      ->save();

    $user1 = $this->drupalCreateUser([]);
    $incorrect_user1 = clone $user1;
    $incorrect_user1->passRaw .= 'incorrect';

    $user2 = $this->drupalCreateUser([]);

    // Try 2 failed logins.
    for ($i = 0; $i < 2; $i++) {
      $this->assertFailedLogin($incorrect_user1);
    }

    // We're not going to test resetting the password which should clear the
    // flood table and allow the user to log in again.
    $this->drupalLogin($user1);
    $this->drupalLogout();

    // Try 3 failed logins for user 1, they will not trigger flood control.
    for ($i = 0; $i < 3; $i++) {
      $this->assertFailedLogin($incorrect_user1);
    }

    // Try one successful attempt for user 2, it should not trigger any
    // flood control.
    $this->drupalLogin($user2);
    $this->drupalLogout();

    // Try one more attempt for user 1, it should be rejected, even if the
    // correct password has been used.
    $this->assertFailedLogin($user1, 'user');
    $this->resetUserPassword($user1);
    $this->drupalLogout();

    // Try to log in as user 1, it should be successful.
    $this->drupalLogin($user1);
    $this->assertSession()->responseContains('Member for');
  }

  /**
   * Tests that user password is re-hashed upon login, after changing the cost.
   */
  public function testPasswordRehashOnLoginAfterChangingCost() {
    /** @var \Drupal\Core\Password\PasswordInterface $hashing_service */
    $hashing_service = $this->container->get('password');

    // Create a new user and authenticate.
    $account = $this->drupalCreateUser();
    $plain_password = $account->pass_raw;
    $old_hash = $account->getPassword();

    // Check that the stored password doesn't need rehash.
    $this->assertFalse($hashing_service->needsRehash($old_hash));

    // The current hashing cost is set to 10 in the container. Increase cost by
    // one, by enabling a module containing the necessary container changes.
    \Drupal::service('module_installer')->install(['user_custom_pass_hash_params_test']);
    $this->resetAll();
    // Reload the hashing service after container changes.
    $hashing_service = $this->container->get('password');

    // Check that the stored password needs rehash.
    $this->assertTrue($hashing_service->needsRehash($old_hash));

    // User first login after the password hashing cost was changed.
    $this->drupalLogin($account);
    $this->drupalLogout();
    // Check that after login the password has been rehashed and is valid.
    $new_hash = User::load($account->id())->getPassword();
    $this->assertNotEquals($new_hash, $old_hash);
    $this->assertTrue($hashing_service->check($plain_password, $new_hash));
    $this->assertFalse($hashing_service->needsRehash($new_hash));
  }

  /**
   * Tests rehashing of Drupal 6 (md5) passwords migrated to Drupal 8+.
   */
  public function testDrupal6MigratedPasswordRehashing() {
    /** @var \Drupal\Core\Password\PasswordInterface $main_hashing_service */
    $main_hashing_service = $this->container->get('password');

    $account = $this->drupalCreateUser();
    $plain_password = $account->pass_raw;
    $md5_pass = md5($plain_password);

    /** @var \Drupal\Core\Password\PasswordInterface[] $migration_cases */
    $migration_cases = [
      // Drupal 6 (md5) passwords migrated to Drupal < 10.1.0 used the legacy
      // password hashing engine, inherited from Drupal 7.
      $this->container->get('legacy_password'),
      // Drupal 6 (md5) passwords migrated to Drupal >= 10.1.0 are using the
      // current hashing password engine, based PHP >= 5.5.0 password hashing.
      $main_hashing_service,
    ];

    foreach ($migration_cases as $hashing_service) {
      // The user has been migrate from Drupal 6. The Drupal 6 md5 hashed
      // password was rehashed with 'password' or 'legacy_password' password
      // hashing service and prefixed with 'U'.
      $old_hash = 'U' . $hashing_service->hash($md5_pass);
      // Store the hashed password, but prevent rehashing.
      $account->setPassword($old_hash);
      $account->get('pass')->pre_hashed = TRUE;
      $account->save();

      // Check that the migrated password needs rehash.
      $this->assertTrue($hashing_service->needsRehash($old_hash));

      // User first login after migration.
      $this->drupalLogin($account);
      $this->drupalLogout();

      // Check that after login the password has been rehashed and is valid.
      $new_hash = User::load($account->id())->getPassword();
      $this->assertNotEquals($new_hash, $old_hash);
      $this->assertTrue($main_hashing_service->check($plain_password, $new_hash));
      $this->assertFalse($main_hashing_service->needsRehash($new_hash));
    }
  }

  /**
   * Tests rehashing of Drupal 7 and < 10.1.0 passwords.
   */
  public function testPasswordRehashing() {
    /** @var \Drupal\Core\Password\PasswordInterface $hashing_service */
    $hashing_service = $this->container->get('password');
    /** @var \Drupal\Core\Password\PasswordInterface $legacy_hashing_service */
    $legacy_hashing_service = $this->container->get('legacy_password');

    $account = $this->drupalCreateUser();
    $plain = $account->pass_raw;

    // User has Drupal < 10.1.0 hashed password or migrated from Drupal 7.
    $old_hash = $legacy_hashing_service->hash($plain);
    // Store the password but prevent rehashing.
    $account->setPassword($old_hash);
    $account->get('pass')->pre_hashed = TRUE;
    $account->save();

    // Check that the migrated password needs rehashing with the new service.
    $this->assertTrue($hashing_service->needsRehash($old_hash));

    // User first login after changing the hashing service.
    $this->drupalLogin($account);
    $this->drupalLogout();

    // Check that after login the password has been rehashed and is valid.
    $new_hash = User::load($account->id())->getPassword();
    $this->assertNotEquals($new_hash, $old_hash);
    $this->assertTrue($hashing_service->check($plain, $new_hash));
    $this->assertFalse($hashing_service->needsRehash($new_hash));
  }

  /**
   * Tests log in with a maximum length and a too long password.
   */
  public function testPasswordLengthLogin() {
    // Create a new user and authenticate.
    $account = $this->drupalCreateUser([]);
    $current_password = $account->passRaw;
    $this->drupalLogin($account);

    // Use the length specified in
    // \Drupal\Core\Render\Element\Password::getInfo().
    $length = 128;

    $current_password = $this->doPasswordLengthLogin($account, $current_password, $length);
    $this->assertSession()->pageTextNotContains('Password cannot be longer than');
    $this->assertSession()->pageTextContains('Member for');

    $this->doPasswordLengthLogin($account, $current_password, $length + 1);
    $this->assertSession()->pageTextContains('Password cannot be longer than ' . $length . ' characters but is currently ' . ($length + 1) . ' characters long.');
    $this->assertSession()->pageTextNotContains('Member for');
  }

  /**
   * Helper to test log in with a maximum length password.
   *
   * @param \Drupal\user\UserInterface $account
   *   An object containing the user account.
   * @param string $current_password
   *   The current password associated with the user.
   * @param int $length
   *   The length of the password.
   *
   * @return string
   *   The new password associated with the user.
   */
  public function doPasswordLengthLogin(UserInterface $account, string $current_password, int $length) {
    $new_password = \Drupal::service('password_generator')->generate($length);
    $uid = $account->id();
    $edit = [
      'current_pass' => $current_password,
      'mail' => $account->getEmail(),
      'pass[pass1]' => $new_password,
      'pass[pass2]' => $new_password,
    ];

    // Change the password.
    $this->drupalGet("user/$uid/edit");
    $this->submitForm($edit, 'Save');
    $this->assertSession()->pageTextContains('The changes have been saved.');
    $this->drupalLogout();

    // Login with new password.
    $this->drupalGet('user/login');
    $edit = [
      'name' => $account->getAccountName(),
      'pass' => $new_password,
    ];
    $this->submitForm($edit, 'Log in');
    return $new_password;
  }

  /**
   * Tests with a browser that denies cookies.
   */
  public function testCookiesNotAccepted() {
    $this->drupalGet('user/login');
    $form_build_id = $this->getSession()->getPage()->findField('form_build_id');

    $account = $this->drupalCreateUser([]);
    $post = [
      'form_id' => 'user_login_form',
      'form_build_id' => $form_build_id,
      'name' => $account->getAccountName(),
      'pass' => $account->passRaw,
      'op' => 'Log in',
    ];
    $url = $this->buildUrl(Url::fromRoute('user.login'));

    /** @var \Psr\Http\Message\ResponseInterface $response */
    $response = $this->getHttpClient()->post($url, [
      'form_params' => $post,
      'http_errors' => FALSE,
      'cookies' => FALSE,
      'allow_redirects' => FALSE,
    ]);

    // Follow the location header.
    $this->drupalGet($response->getHeader('location')[0]);
    $this->assertSession()->statusCodeEquals(403);
    $this->assertSession()->pageTextContains('To log in to this site, your browser must accept cookies from the domain');
  }

  /**
   * Make an unsuccessful login attempt.
   *
   * @param \Drupal\user\Entity\User $account
   *   A user object with name and passRaw attributes for the login attempt.
   * @param string $flood_trigger
   *   (optional) Whether or not to expect that the flood control mechanism
   *    will be triggered. Defaults to NULL.
   *   - Set to 'user' to expect a 'too many failed logins error.
   *   - Set to any value to expect an error for too many failed logins per IP.
   *   - Set to NULL to expect a failed login.
   *
   * @internal
   */
  public function assertFailedLogin(User $account, string $flood_trigger = NULL): void {
    $database = \Drupal::database();
    $edit = [
      'name' => $account->getAccountName(),
      'pass' => $account->passRaw,
    ];
    $this->drupalGet('user/login');
    $this->submitForm($edit, 'Log in');
    if (isset($flood_trigger)) {
      $this->assertSession()->statusCodeEquals(403);
      $this->assertSession()->fieldNotExists('pass');
      $last_log = $database->select('watchdog', 'w')
        ->fields('w', ['message'])
        ->condition('type', 'user')
        ->orderBy('wid', 'DESC')
        ->range(0, 1)
        ->execute()
        ->fetchField();
      if ($flood_trigger == 'user') {
        $this->assertSession()->pageTextMatches("/There (has|have) been more than \w+ failed login attempt.* for this account. It is temporarily blocked. Try again later or request a new password./");
        $this->assertSession()->elementExists('css', 'body.maintenance-page');
        $this->assertSession()->linkExists("request a new password");
        $this->assertSession()->linkByHrefExists(Url::fromRoute('user.pass')->toString());
        $this->assertEquals('Flood control blocked login attempt for uid %uid from %ip', $last_log, 'A watchdog message was logged for the login attempt blocked by flood control per user.');
      }
      else {
        // No uid, so the limit is IP-based.
        $this->assertSession()->pageTextContains("Too many failed login attempts from your IP address. This IP address is temporarily blocked. Try again later or request a new password.");
        $this->assertSession()->elementExists('css', 'body.maintenance-page');
        $this->assertSession()->linkExists("request a new password");
        $this->assertSession()->linkByHrefExists(Url::fromRoute('user.pass')->toString());
        $this->assertEquals('Flood control blocked login attempt from %ip', $last_log, 'A watchdog message was logged for the login attempt blocked by flood control per IP.');
      }
    }
    else {
      $this->assertSession()->statusCodeEquals(200);
      $this->assertSession()->fieldValueEquals('pass', '');
      $this->assertSession()->pageTextContains('Unrecognized username or password. Forgot your password?');
    }
  }

  /**
   * Reset user password.
   *
   * @param object $user
   *   A user object.
   */
  public function resetUserPassword($user) {
    $this->drupalGet('user/password');
    $edit['name'] = $user->getDisplayName();
    $this->submitForm($edit, 'Submit');
    $_emails = $this->drupalGetMails();
    $email = end($_emails);
    $urls = [];
    preg_match('#.+user/reset/.+#', $email['body'], $urls);
    $resetURL = $urls[0];
    $this->drupalGet($resetURL);
    $this->submitForm([], 'Log in');
  }

}
