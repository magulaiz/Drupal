<?php

namespace Drupal\user\Controller;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\user\UserDataInterface;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Confirms cancelling a user account via an email link.
 */
class UserConfirmCancelController extends ControllerBase {

  use StringTranslationTrait;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected TimeInterface $time;

  /**
   * The user data service.
   *
   * @var \Drupal\user\UserDataInterface
   */
  protected $userData;

  /**
   * Constructs a UserConfirmCancelController object.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   String translation.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\user\UserDataInterface $userData
   *   The user data service.
   */
  public function __construct(TranslationInterface $stringTranslation, ConfigFactoryInterface $configFactory, MessengerInterface $messenger, TimeInterface $time, UserDataInterface $userData) {
    $this->stringTranslation = $stringTranslation;
    $this->configFactory = $configFactory;
    $this->messenger = $messenger;
    $this->time = $time;
    $this->userData = $userData;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('string_translation'),
      $container->get('config.factory'),
      $container->get('messenger'),
      $container->get('datetime.time'),
      $container->get('user.data'),
    );
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
  public function __invoke(UserInterface $user, $timestamp = 0, $hashed_pass = '') {
    // Time out in seconds until cancel URL expires; 24 hours = 86400 seconds.
    $timeout = 86400;
    $current = $this->time->getRequestTime();

    // Basic validation of arguments.
    $account_data = $this->userData->get('user', $user->id());
    if (isset($account_data['cancel_method']) && !empty($timestamp) && !empty($hashed_pass)) {
      // Validate expiration and hashed password/login.
      if ($timestamp <= $current && $current - $timestamp < $timeout && $user->id() && $timestamp >= $user->getLastLoginTime() && hash_equals($hashed_pass, user_pass_rehash($user, $timestamp))) {
        $edit = [
          'user_cancel_notify' => $account_data['cancel_notify'] ?? $this->config('user.settings')->get('notify.status_canceled'),
        ];
        user_cancel($edit, $user->id(), $account_data['cancel_method']);
        // Since user_cancel() is not invoked via Form API, batch processing
        // needs to be invoked manually and should redirect to the front page
        // after completion.
        return batch_process('<front>');
      }
      else {
        $this->messenger->addError($this->t('You have tried to use an account cancellation link that has expired. Please request a new one using the form below.'));
        return $this->redirect('entity.user.cancel_form', ['user' => $user->id()], ['absolute' => TRUE]);
      }
    }
    throw new AccessDeniedHttpException();
  }

}
