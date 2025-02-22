<?php

namespace Drupal\automated_cron\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * A subscriber running cron after a response is sent.
 */
class AutomatedCron implements EventSubscriberInterface {

  /**
   * The cron configuration.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  public function __construct(
    protected \Closure $cron_closure,
    ConfigFactoryInterface $config_factory,
    protected StateInterface $state,
  ) {
    $this->config = $config_factory->get('automated_cron.settings');
  }

  /**
   * Run the automated cron if enabled.
   *
   * @param \Symfony\Component\HttpKernel\Event\TerminateEvent $event
   *   The Event to process.
   */
  public function onTerminate(TerminateEvent $event) {
    $interval = $this->config->get('interval');
    if ($interval > 0) {
      $cron_next = $this->state->get('system.cron_last', 0) + $interval;
      if ((int) $event->getRequest()->server->get('REQUEST_TIME') > $cron_next) {
        ($this->cron_closure)()->run();
      }
    }
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  public static function getSubscribedEvents(): array {
    return [KernelEvents::TERMINATE => [['onTerminate', 100]]];
  }

}
