<?php

declare(strict_types=1);

namespace Drupal\Tests\user\Kernel;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Session\AnonymousUserSession;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Tests the user session handler.
 *
 * @group user
 * @coversDefaultClass \Drupal\user\UserSessionHandler
 */
class UserSessionHandlerTest extends KernelTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system', 'user', 'user_hooks_test'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');

    // Create a mock time service.
    $time = $this->createMock(TimeInterface::class);
    $time->expects($this->any())
      ->method('getRequestTime')
      ->willReturn(1234567890);
    $this->container->set('datetime.time', $time);

    // Set up our session.
    $request_stack = new RequestStack();
    $request = new Request();
    $session = new Session($this->container->get('session_manager'));
    $request->setSession($session);
    $request_stack->push($request);
    $this->container->set('request_stack', $request_stack);
  }

  /**
   * @covers ::login
   */
  public function testLogin(): void {
    // Create a user.
    $user = $this->createUser();

    // Handle the login.
    $handler = $this->container->get('user.session_handler');
    $handler->login($user);

    // Get the session.
    $request = $this->container->get('request_stack')->getCurrentRequest();
    $session = $request->getSession();

    // Assert our user is logged in.
    $this->assertEquals($user->id(), $session->get('uid'));
    $this->assertTrue($session->get('check_logged_in'));

    // Assert last login time is set.
    $user = User::load($user->id());
    $this->assertEquals(1234567890, $user->getLastLoginTime());

    // Assert our user login hook was called.
    $this->assertEquals($user->id(), \Drupal::state()->get('user_hooks_test_user_login'));
  }

  /**
   * @covers ::logout
   */
  public function testLogout(): void {
    // Create a user.
    $user = $this->createUser();

    // Create a mock session manager.
    $sessionManager = $this->createMock(SessionManagerInterface::class);
    $sessionManager->expects($this->once())
      ->method('destroy');
    $this->container->set('session_manager', $sessionManager);

    // Handle the login.
    $handler = $this->container->get('user.session_handler');
    $handler->login($user);

    // We assert the user is logged already in the testLogin method.
    $handler->logout();

    // Assert our user logout hook was called.
    $this->assertEquals($user->id(), \Drupal::state()->get('user_hooks_test_user_logout'));

    // Get the session.
    /** @var \Drupal\Core\Session\AccountProxyInterface $accountProxy */
    $accountProxy = $this->container->get('current_user');

    $this->assertInstanceOf(AnonymousUserSession::class, $accountProxy->getAccount());
  }

}
