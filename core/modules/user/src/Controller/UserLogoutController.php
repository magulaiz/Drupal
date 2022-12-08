<?php

namespace Drupal\user\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logs the current user out.
 */
class UserLogoutController extends ControllerBase {

  /**
   * Constructs a UserLogoutController object.
   *
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The current user.
   */
  public function __construct(AccountInterface $currentUser) {
    $this->currentUser = $currentUser;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
    );
  }

  /**
   * Logs the current user out.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirection to home page.
   */
  public function __invoke(): Response|array {
    if ($this->currentUser->isAuthenticated()) {
      user_logout();
    }
    return $this->redirect('<front>');
  }

}
