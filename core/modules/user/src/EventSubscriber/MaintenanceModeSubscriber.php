<?php

namespace Drupal\user\EventSubscriber;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Site\MaintenanceModeEvents;
use Drupal\Core\Site\MaintenanceModeInterface;
use Drupal\Core\Url;
use Drupal\user\UserSessionFinalize;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Maintenance mode subscriber to log out users.
 */
class MaintenanceModeSubscriber implements EventSubscriberInterface {

  /**
   * The maintenance mode.
   *
   * @var \Drupal\Core\Site\MaintenanceMode
   */
  protected $maintenanceMode;

  /**
   * The current account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * Constructs a new MaintenanceModeSubscriber.
   *
   * @param \Drupal\Core\Site\MaintenanceModeInterface $maintenance_mode
   *   The maintenance mode.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   * @param \Drupal\user\UserSessionFinalize|null $userSessionFinalize
   *   The user session finalize service..
   */
  public function __construct(MaintenanceModeInterface $maintenance_mode, AccountInterface $account, protected ?UserSessionFinalize $userSessionFinalize = NULL) {
    $this->maintenanceMode = $maintenance_mode;
    $this->account = $account;
    if (!$this->userSessionFinalize) {
      @trigger_error('Calling ' . __METHOD__ . '() without the $userSessionFinalize argument is deprecated in drupal:10.2.0 and is required in drupal:11.0.0. See https://www.drupal.org/node/3379194', E_USER_DEPRECATED);
      $this->userSessionFinalize = \Drupal::service('user.session_finalize');
    }
  }

  /**
   * Logout users if site is in maintenance mode and user is not exempt.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The event to process.
   */
  public function onMaintenanceModeRequest(RequestEvent $event) {
    // If the site is offline, log out unprivileged users.
    if ($this->account->isAuthenticated()) {
      $this->userSessionFinalize->finalizeLogout();
      // Redirect to homepage.
      $event->setResponse(
        new RedirectResponse(Url::fromRoute('<front>')->toString())
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[MaintenanceModeEvents::MAINTENANCE_MODE_REQUEST][] = [
      'onMaintenanceModeRequest',
      -900,
    ];
    return $events;
  }

}
