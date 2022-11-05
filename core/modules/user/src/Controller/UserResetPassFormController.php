<?php

namespace Drupal\user\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\user\Form\UserPasswordResetForm;
use Drupal\user\UserStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Returns the user password reset form.
 */
class UserResetPassFormController extends ControllerBase {

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Constructs a UserResetPassFormController object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\Core\Form\FormBuilderInterface $formBuilder
   *   The form builder service.
   * @param \Drupal\user\UserStorageInterface $userStorage
   *   The user storage.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   *   The date formatter service.
   */
  public function __construct(ConfigFactoryInterface $configFactory, FormBuilderInterface $formBuilder, UserStorageInterface $userStorage, DateFormatterInterface $dateFormatter) {
    $this->configFactory = $configFactory;
    $this->formBuilder = $formBuilder;
    $this->userStorage = $userStorage;
    $this->dateFormatter = $dateFormatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('form_builder'),
      $container->get('entity_type.manager')->getStorage('user'),
      $container->get('date.formatter'),
    );
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
  public function __invoke(Request $request, $uid) {
    $session = $request->getSession();
    $timestamp = $session->get('pass_reset_timeout');
    $hash = $session->get('pass_reset_hash');
    // As soon as the session variables are used they are removed to prevent the
    // hash and timestamp from being leaked unexpectedly. This could occur if
    // the user does not click on the log in button on the form.
    $session->remove('pass_reset_timeout');
    $session->remove('pass_reset_hash');
    if (!$hash || !$timestamp) {
      throw new AccessDeniedHttpException();
    }

    /** @var \Drupal\user\UserInterface|null $user */
    $user = $this->userStorage->load($uid);
    if ($user === NULL || !$user->isActive()) {
      // Blocked or invalid user ID, so deny access. The parameters will be in
      // the watchdog's URL for the administrator to check.
      throw new AccessDeniedHttpException();
    }

    // Time out, in seconds, until login URL expires.
    $timeout = $this->configFactory->get('user.settings')->get('password_reset_timeout');

    $expiration_date = $user->getLastLoginTime() ? $this->dateFormatter->format($timestamp + $timeout) : NULL;
    return $this->formBuilder->getForm(UserPasswordResetForm::class, $user, $expiration_date, $timestamp, $hash);
  }

}
