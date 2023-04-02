<?php

namespace Drupal\Core\Flood;

use Drupal\Component\Datetime\TimeInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Defines the memory flood backend. This is used for testing.
 */
class MemoryBackend implements FloodInterface, PrefixFloodInterface {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * An array holding flood events, keyed by event name and identifier.
   *
   * @var array<string, array<string, array<int, array{expire: float, time: float}>>>
   */
  protected $events = [];

  /**
   * Construct the MemoryBackend.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack used to retrieve the current request.
   * @param \Drupal\Component\Datetime\TimeInterface|null $time
   *   The time service.
   */
  public function __construct(
    RequestStack $request_stack,
    protected ?TimeInterface $time = NULL,
  ) {
    $this->requestStack = $request_stack;
    if (!$time) {
      @trigger_error('Calling MemoryBackend::__construct() without the $time argument is deprecated in drupal:10.1.0 and will be required before drupal:11.0.0. See https://www.drupal.org/node/3253739.', E_USER_DEPRECATED);
    }
    $this->time = $time ?? \Drupal::time();
  }

  /**
   * {@inheritdoc}
   */
  public function register($name, $window = 3600, $identifier = NULL) {
    if (!isset($identifier)) {
      $identifier = $this->requestStack->getCurrentRequest()->getClientIp();
    }
    $time = $this->getCurrentMicroTime();
    $this->events[$name][$identifier][] = ['expire' => $time + $window, 'time' => $time];
  }

  /**
   * {@inheritdoc}
   */
  public function clear($name, $identifier = NULL) {
    if (!isset($identifier)) {
      $identifier = $this->requestStack->getCurrentRequest()->getClientIp();
    }
    unset($this->events[$name][$identifier]);
  }

  /**
   * {@inheritdoc}
   */
  public function clearByPrefix(string $name, string $prefix): void {
    foreach ($this->events as $event_name => $events_by_identifier) {
      foreach (array_keys($events_by_identifier) as $identifier_key) {
        $identifier_parts = explode('-', $identifier_key);
        $identifier_prefix = reset($identifier_parts);
        if ($prefix == $identifier_prefix && $name == $event_name) {
          unset($this->events[$event_name][$identifier_key]);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isAllowed($name, $threshold, $window = 3600, $identifier = NULL) {
    if (!isset($identifier)) {
      $identifier = $this->requestStack->getCurrentRequest()->getClientIp();
    }
    if (!isset($this->events[$name][$identifier])) {
      return $threshold > 0;
    }
    $limit = $this->getCurrentMicroTime() - $window;
    $number = count(array_filter(
      $this->events[$name][$identifier],
      function (array $timestamp) use ($limit): bool {
        return $timestamp['time'] > $limit;
      },
    ));
    return ($number < $threshold);
  }

  /**
   * {@inheritdoc}
   */
  public function garbageCollection() {
    $time = $this->getCurrentMicroTime();
    foreach ($this->events as $name => $identifiers) {
      foreach ($this->events[$name] as $identifier => $entries) {
        // Remove expired entries.
        $this->events[$name][$identifier] = array_filter(
          $entries,
          function (array $event) use ($time): bool {
            return $event['expire'] > $time;
          },
        );
      }
    }
  }

  /**
   * Returns current Unix timestamp with microseconds.
   *
   * @return float
   *   The current time in seconds with microseconds.
   */
  protected function getCurrentMicroTime(): float {
    return $this->time->getRequestMicroTime();
  }

}
