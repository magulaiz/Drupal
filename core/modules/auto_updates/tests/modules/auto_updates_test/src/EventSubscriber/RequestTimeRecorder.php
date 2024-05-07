<?php

declare(strict_types=1);

namespace Drupal\auto_updates_test\EventSubscriber;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\State\StateInterface;
use Drupal\package_manager\Event\PostApplyEvent;
use Drupal\package_manager\Event\PreApplyEvent;
use Drupal\package_manager\Event\StageEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Records the request time during various events.
 */
class RequestTimeRecorder implements EventSubscriberInterface {

  /**
   * The state service.
   */
  protected StateInterface $state;

  /**
   * The time service.
   */
  protected TimeInterface $time;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(StateInterface $state, TimeInterface $time) {
    $this->state = $state;
    $this->time = $time;
  }

  /**
   * Records the request time.
   *
   * @param \Drupal\package_manager\Event\StageEvent $event
   *   The event object.
   */
  public function updateState(StageEvent $event) {
    $key = get_class($event) . ' time';
    $this->state->set($key, $this->time->getRequestMicroTime());
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      PreApplyEvent::class => 'updateState',
      PostApplyEvent::class => 'updateState',
    ];
  }

}
