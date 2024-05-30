<?php

declare(strict_types=1);

namespace Drupal\Tests\user\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * @group system
 */
class UserConfigValidationTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests invalid register key config.
   */
  public function testRegisterKey(): void {
    $this->expectExceptionMessage('Schema errors for user.settings with the following errors: 0 [register] The value you selected is not a valid choice.');
    $this->config('user.settings')->set('register', 'somebody')->save();
  }

  /**
   * Tests invalid cancel_method key config.
   */
  public function testCancelMethodsKey(): void {
    $this->expectExceptionMessage('Schema errors for user.settings with the following errors: 0 [cancel_method] The value you selected is not a valid choice.');
    $this->config('user.settings')->set('cancel_method', 'user_cancel_random')->save();
  }

  /**
   * Tests invalid password_reset_timeout key config.
   */
  public function testPasswordResetTimeoutKey(): void {
    $this->expectExceptionMessage('Schema errors for user.settings with the following errors: 0 [password_reset_timeout] This value should be &lt;em class=&quot;placeholder&quot;&gt;1&lt;/em&gt; or more.');
    $this->config('user.settings')->set('password_reset_timeout', 0)->save();
  }

}
