<?php

namespace Drupal\user\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Flood\FloodInterface;
use Drupal\Core\Url;
use Drupal\user\UserDataInterface;
use Drupal\user\UserInterface;
use Drupal\user\UserStorageInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Controller routines for user routes.
 */
class UserController extends ControllerBase {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * The user data service.
   *
   * @var \Drupal\user\UserDataInterface
   */
  protected $userData;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The flood service.
   *
   * @var \Drupal\Core\Flood\FloodInterface
   */
  protected $flood;

  /**
   * Constructs a UserController object.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\user\UserStorageInterface $user_storage
   *   The user storage.
   * @param \Drupal\user\UserDataInterface $user_data
   *   The user data service.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Flood\FloodInterface $flood
   *   The flood service.
   */
  public function __construct(DateFormatterInterface $date_formatter, UserStorageInterface $user_storage, UserDataInterface $user_data, LoggerInterface $logger, FloodInterface $flood) {
    $this->dateFormatter = $date_formatter;
    $this->userStorage = $user_storage;
    $this->userData = $user_data;
    $this->logger = $logger;
    $this->flood = $flood;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter'),
      $container->get('entity_type.manager')->getStorage('user'),
      $container->get('user.data'),
      $container->get('logger.factory')->get('user'),
      $container->get('flood')
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
  public function resetPass(Request $request, $uid, $timestamp, $hash) {
    /** @var \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernel */
    $httpKernel = \Drupal::service('http_kernel');

    $url = Url::fromRoute('user.reset', [
      'uid' => $uid,
      'timestamp' => $timestamp,
      'hash' => $hash,
    ])->toString(TRUE);
    $subRequest = Request::create($url->getGeneratedUrl(), 'GET', $request->query->all(), $request->cookies->all(), [], $request->server->all());
    return $httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
  }

  /**
   * Returns the user password reset form.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param int $uid
   *   User ID of the user requesting reset.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   The form structure or a redirect response.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   *   If the pass_reset_timeout or pass_reset_hash are not available in the
   *   session. Or if $uid is for a blocked user or invalid user ID.
   */
  public function getResetPassForm(Request $request, $uid) {
    /** @var \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernel */
    $httpKernel = \Drupal::service('http_kernel');

    $url = Url::fromRoute('user.reset.form', [
      'uid' => $uid,
    ])->toString(TRUE);
    $subRequest = Request::create($url->getGeneratedUrl(), 'GET', $request->query->all(), $request->cookies->all(), [], $request->server->all());
    return $httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
  }

  /**
   * Validates user, hash, and timestamp; logs the user in if correct.
   *
   * @param int $uid
   *   User ID of the user requesting reset.
   * @param int $timestamp
   *   The current timestamp.
   * @param string $hash
   *   Login link hash.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Returns a redirect to the user edit form if the information is correct.
   *   If the information is incorrect redirects to 'user.pass' route with a
   *   message for the user.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   *   If $uid is for a blocked user or invalid user ID.
   */
  public function resetPassLogin($uid, $timestamp, $hash, Request $request) {
    /** @var \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernel */
    $httpKernel = \Drupal::service('http_kernel');

    $url = Url::fromRoute('user.reset.login', [
      'uid' => $uid,
      'timestamp' => $timestamp,
      'hash' => $hash,
    ])->toString(TRUE);
    $subRequest = Request::create($url->getGeneratedUrl(), 'GET', $request->query->all(), $request->cookies->all(), [], $request->server->all());
    return $httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
  }

  /**
   * Redirects users to their profile page.
   *
   * This controller assumes that it is only invoked for authenticated users.
   * This is enforced for the 'user.page' route with the '_user_is_logged_in'
   * requirement.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Returns a redirect to the profile of the currently logged in user.
   */
  public function userPage() {
    /** @var \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernel */
    $httpKernel = \Drupal::service('http_kernel');
    $url = Url::fromRoute('user.page')->toString(TRUE);
    $request = \Drupal::request();
    $subRequest = Request::create($url->getGeneratedUrl(), 'GET', $request->query->all(), $request->cookies->all(), [], $request->server->all());
    return $httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
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
  public function userEditPage() {
    /** @var \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernel */
    $httpKernel = \Drupal::service('http_kernel');
    $url = Url::fromRoute('user.edit')->toString(TRUE);
    $request = \Drupal::request();
    $subRequest = Request::create($url->getGeneratedUrl(), 'GET', $request->query->all(), $request->cookies->all(), [], $request->server->all());
    return $httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
  }

  /**
   * Route title callback.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user account.
   *
   * @return string|array
   *   The user account name as a render array or an empty string if $user is
   *   NULL.
   */
  public function userTitle(UserInterface $user = NULL) {
    return $user ? ['#markup' => $user->getDisplayName(), '#allowed_tags' => Xss::getHtmlTagList()] : '';
  }

  /**
   * Logs the current user out.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirection to home page.
   */
  public function logout() {
    /** @var \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernel */
    $httpKernel = \Drupal::service('http_kernel');
    $url = Url::fromRoute('user.logout')->toString(TRUE);
    $request = \Drupal::request();
    $subRequest = Request::create($url->getGeneratedUrl(), 'GET', $request->query->all(), $request->cookies->all(), [], $request->server->all());
    return $httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
  }

  /**
   * Confirms cancelling a user account via an email link.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user account.
   * @param int $timestamp
   *   The timestamp.
   * @param string $hashed_pass
   *   The hashed password.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response.
   */
  public function confirmCancel(UserInterface $user, $timestamp = 0, $hashed_pass = '') {
    /** @var \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernel */
    $httpKernel = \Drupal::service('http_kernel');
    $url = Url::fromRoute('user.cancel_confirm', [
      'user' => $user->id(),
      'timestamp' => $timestamp,
      'hashed_pass' => $hashed_pass,
    ])->toString(TRUE);
    $request = \Drupal::request();
    $subRequest = Request::create($url->getGeneratedUrl(), 'GET', $request->query->all(), $request->cookies->all(), [], $request->server->all());
    return $httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
  }

}
