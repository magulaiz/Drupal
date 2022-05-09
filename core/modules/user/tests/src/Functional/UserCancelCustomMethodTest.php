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
  public function testUserCancelCustomMethod(): void {
    $permissions = ['cancel account', 'select account cancellation method'];
    $account1 = $this->createUser($permissions);
    $account2 = $this->createUser($permissions);

    $this->drupalLogin($account1);
    $this->drupalGet($account1->toUrl('edit-form'));
    $page = $this->getSession()->getPage();
    $page->clickLink('Cancel account');

    // Chose the custom cancellation method.
    $page->selectFieldOption('user_cancel_method', 'user_cancel_test');
    $page->pressButton('Confirm');

    $this->clickConfirmationLinkFomMail();

    // Check that the custom user cancellation has been executed. We let also
    // Drupal core cancellation code to run, so the user will be blocked.
    // @see \Drupal\user\EventSubscriber\AccountCancelSubscriber::onUserAccountCancel()
    // @see \Drupal\user_cancel_test\UserCancelTestAccountCancelSubscriber::onUserAccountCancel()
    $this->assertSession()->pageTextContains('Custom user cancel method executed.');
    $this->assertSession()->pageTextContains("{$account1->getDisplayName()} has been disabled.");

    // Repeat but suppress Drupal core cancellation.
    \Drupal::state()->set('user_cancel_test.bypass_core_cancellation', TRUE);

    $this->drupalLogin($account2);
    $this->drupalGet($account2->toUrl('edit-form'));
    $page = $this->getSession()->getPage();
    $page->clickLink('Cancel account');

    // Chose the custom cancellation method.
    $page->selectFieldOption('user_cancel_method', 'user_cancel_test');
    $page->pressButton('Confirm');

    $this->clickConfirmationLinkFomMail();

    // Check that the custom user cancellation has been executed but not the
    // Drupal core cancellation.
    // @see \Drupal\user\EventSubscriber\AccountCancelSubscriber::onUserAccountCancel()
    // @see \Drupal\user_cancel_test\UserCancelTestAccountCancelSubscriber::onUserAccountCancel()
    $this->assertSession()->pageTextContains('Custom user cancel method executed.');
    $this->assertSession()->pageTextNotContains("{$account2->getDisplayName()} has been disabled.");
  }

  /**
   * Clicks on the confirmation link sent by email.
   */
  protected function clickConfirmationLinkFomMail(): void {
    $mails = $this->getMails();
    $mail = reset($mails);
    preg_match('#http.*#', $mail['body'], $found);
    $this->drupalGet($found[0]);
    // Prepare for next operation.
    \Drupal::state()->set('system.test_mail_collector', []);
  }

}
