<?php

declare(strict_types=1);

namespace Drupal\user;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Session\AnonymousUserSession;
use Drupal\Core\Session\SessionManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Handler for user sessions.
 */
class UserSessionHandler implements UserSessionHandlerInterface {

  /**
   * Creates a new UserSessionHandler.
   */
  public function __construct(
    protected AccountProxyInterface $accountProxy,
    protected RequestStack $requestStack,
    protected SessionManagerInterface $sessionManager,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected ModuleHandlerInterface $moduleHandler,
    protected LoggerInterface $logger,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function login(UserInterface $user): void {
    $this->accountProxy->setAccount($user);
    $this->logger->info('Session opened for %name.', ['%name' => $user->getAccountName()]);
    // Update the user table timestamp noting user has logged in.
    // This is also used to invalidate one-time login links.
    $user->setLastLoginTime(\Drupal::time()->getRequestTime());
    $this->entityTypeManager->getStorage('user')
      ->updateLastLoginTimestamp($user);

    // Regenerate the session ID to prevent against session fixation attacks.
    // This is called before hook_user_login() in case one of those functions
    // fails or incorrectly does a redirect which would leave the old session
    // in place.
    $session = $this->requestStack->getSession();
    $session->migrate();
    $session->set('uid', $user->id());
    $session->set('check_logged_in', TRUE);
    $this->moduleHandler->invokeAll('user_login', [$user]);
  }

  /**
   * {@inheritdoc}
   */
  public function logout(): void {
    $this->logger->info('Session closed for %name.', ['%name' => $this->accountProxy->getAccountName()]);

    $this->moduleHandler->invokeAll('user_logout', [$this->accountProxy]);

    // Destroy the current session, and reset $user to the anonymous user.
    // Note: In Symfony the session is intended to be destroyed with
    // Session::invalidate(). Regrettably this method is currently broken and
    // may lead to the creation of spurious session records in the database.
    // @see https://github.com/symfony/symfony/issues/12375
    $this->sessionManager->destroy();
    $this->accountProxy->setAccount(new AnonymousUserSession());
  }

}
