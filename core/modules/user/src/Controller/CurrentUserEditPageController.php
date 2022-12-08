<?php

namespace Drupal\user\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirects users to their profile edit page.
 */
class CurrentUserEditPageController extends ControllerBase {

  /**
   * Constructs a CurrentUserEditPageController object.
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
   * Redirects users to their profile edit page.
   *
   * This controller assumes that it is only invoked for authenticated users.
   * This is typically enforced with the '_user_is_logged_in' requirement.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Returns a redirect to the profile edit form of the currently logged in
   *   user.
   */
  public function __invoke(): Response|array {
    return $this->redirect('entity.user.edit_form', ['user' => $this->currentUser->id()], status: 301);
  }

}
