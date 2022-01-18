<?php

namespace Drupal\Tests\user\Functional;

use Drupal\Core\Test\AssertMailTrait;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests user cancellation with a custom method.
 *
 * @group user
 */
class UserCancelCustomMethodTest extends BrowserTestBase {

  use AssertMailTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['user_cancel_test'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests user cancellation with a custom method.
   */
  public function testUserCancelCustomMethod() {
    $account = $this->createUser([
      'cancel account',
      'select account cancellation method',
    ]);
    $this->drupalLogin($account);
    $this->drupalGet($account->toUrl('edit-form'));
    $page = $this->getSession()->getPage();
    $page->pressButton('Cancel account');

    // Chose the custom cancellation method.
    $page->selectFieldOption('user_cancel_method', 'user_cancel_test');
    $page->pressButton('Cancel account');

    $this->clickConfirmationLinkFomMail();

    $this->assertSession()->pageTextContains('Custom user cancel method executed.');
    $this->assertSession()->pageTextNotContains("{$account->getDisplayName()} has been disabled.");
  }

  /**
   * Clicks on the confirmation link sent by email.
   */
  protected function clickConfirmationLinkFomMail() {
    $mails = $this->getMails();
    $mail = reset($mails);
    preg_match('#http.*#', $mail['body'], $found);
    $this->drupalGet($found[0]);
  }

}
