<?php

namespace Drupal\Core\State\EventSubscriber;

use Drupal\Component\Assertion\Inspector;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Logs state keys changes during request.
 */
class StateKeysSetLoggerSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * Constructs a new StateKeysSetLoggerSubscriber.
   *
   * @param array $config
   *   The configuration:
   *     - keys: An array of state keys to log.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   The messenger.
   */
  public function __construct(
    protected array $config,
    protected StateInterface $state,
    protected TranslationInterface $string_translation,
    protected LoggerChannelFactoryInterface $loggerChannelFactory,
  ) {
    Inspector::assertAllStrings($this->config['keys']);
  }

  /**
   * Logs state keys changes during request.
   *
   * @param \Symfony\Component\HttpKernel\Event\TerminateEvent $event
   *   The event to process.
   */
  public function onTerminate(TerminateEvent $event): void {
    $keysSetDuringRequest = $this->state->getKeysSetDuringRequest();
    $logger = $this->loggerChannelFactory->get('state');
    foreach ($this->config['keys'] as $key) {
      if (array_key_exists($key, $keysSetDuringRequest)) {
        $originalValue = $keysSetDuringRequest[$key]['original'];
        $newValue = $keysSetDuringRequest[$key]['value'];
        if ($newValue !== $originalValue) {
          $logger->info(
            $this->t('The state key "%key" has been changed from "%original" to "%value"', [
              '%key' => $key,
              '%original' => $originalValue,
              '%value' => $newValue,
            ])
          );
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[KernelEvents::TERMINATE] = ['onTerminate'];

    return $events;
  }

}
