<?php

namespace Drupal\user\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\user\UserStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirects to the user password reset form.
 */
class UserResetPassController extends ControllerBase {

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * Constructs a UserResetPassController object.
   *
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The current user.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\user\UserStorageInterface $userStorage
   *   The user storage.
   */
  public function __construct(AccountInterface $currentUser, MessengerInterface $messenger, UserStorageInterface $userStorage) {
    $this->currentUser = $currentUser;
    $this->messenger = $messenger;
    $this->userStorage = $userStorage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('messenger'),
      $container->get('entity_type.manager')->getStorage('user'),
    );
  }

  /**
   * Redirects to the user password reset form.
   *
   * In order to never disclose a reset link via a referrer header this
   * controller must always return a redirect response.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param int $uid
   *   User ID of the user requesting reset.
   * @param int $timestamp
   *   The current timestamp.
   * @param string $hash
   *   Login link hash.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   The redirect response.
   */
  public function __invoke(Request $request, $uid, $timestamp, $hash): Response|array {
    // When processing the one-time login link, we have to make sure that a user
    // isn't already logged in.
    if ($this->currentUser->isAuthenticated()) {
      // The current user is already logged in.
      if ($this->currentUser->id() == $uid) {
        user_logout();
        // We need to begin the redirect process again because logging out will
        // destroy the session.
        return $this->redirect(
          'user.reset',
          [
            'uid' => $uid,
            'timestamp' => $timestamp,
            'hash' => $hash,
          ]
        );
      }
      // A different user is already logged in on the computer.
      else {
        /** @var \Drupal\user\UserInterface|null $reset_link_user */
        $reset_link_user = $this->userStorage->load($uid);
        if ($reset_link_user) {
          $this->messenger
            ->addWarning($this->t('Another user (%other_user) is already logged into the site on this computer, but you tried to use a one-time link for user %resetting_user. Please <a href=":logout">log out</a> and try using the link again.',
              [
                '%other_user' => $this->currentUser->getAccountName(),
                '%resetting_user' => $reset_link_user->getAccountName(),
                ':logout' => Url::fromRoute('user.logout')->toString(),
              ]));
        }
        else {
          // Invalid one-time link specifies an unknown user.
          $this->messenger->addError($this->t('The one-time login link you clicked is invalid.'));
        }
        return $this->redirect('<front>');
      }
    }

    $session = $request->getSession();
    $session->set('pass_reset_hash', $hash);
    $session->set('pass_reset_timeout', $timestamp);
    return $this->redirect(
      'user.reset.form',
      ['uid' => $uid]
    );
  }

}
