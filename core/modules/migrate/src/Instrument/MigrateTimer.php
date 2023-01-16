<?php

declare(strict_types = 1);

namespace Drupal\migrate\Instrument;

use Drupal\Component\Utility\Timer;

/**
 * Migrate Timer extends Drupal Timer due to lack of getter for all timer data.
 *
 * This class extends Timer with average, min and max values and some more.
 */
class MigrateTimer extends Timer {

  /**
   * Number of measuring point, like a step.
   */
  protected static int $tracePoint = 0;

  /**
   * Timer start.
   *
   * @param mixed $name
   *   Timer ID.
   *
   * @see Timer::start()
   */
  public static function start($name): void {
    parent::start($name);
    static::$timers[$name]['point'][] = self::getNextTracePoint();
  }

  /**
   * Get the current timer data.
   *
   * @param string $name
   *   The name of the timer.
   *
   * @return array
   *   The current timer data.
   */
  public static function get(string $name): array {
    if (isset(static::$timers[$name]['start'])) {
      // Timer is still running.
      $time = Timer::read($name);
      static::$timers[$name]['time'] = $time;
      static::$timers[$name]['status'] = 'running';
    }
    else {
      static::$timers[$name]['status'] = 'stopped';
    }
    return static::$timers[$name];
  }

  /**
   * Stops the timer with the specified name, calculate min and max values.
   *
   * Extended Timer::stop method.
   *
   * @param mixed $name
   *   The name of the timer.
   *
   * @return array
   *   A timer data. The array contains the number of times the timer has been
   *   started and stopped (count) and the accumulated timer value in ms (time),
   *   smallest (min) and biggest (max) value.
   */
  public static function stop($name): array {
    if (isset(static::$timers[$name]['start'])) {
      $diff = self::getCurrentTimeDiff($name);

      // Store smallest value.
      if (!isset(static::$timers[$name]['min'])) {
        static::$timers[$name]['min'] = $diff;
      }
      elseif ($diff < static::$timers[$name]['min']) {
        static::$timers[$name]['min'] = $diff;
      }
      // Store biggest value.
      if (!isset(static::$timers[$name]['max'])) {
        static::$timers[$name]['max'] = $diff;
      }
      elseif ($diff > static::$timers[$name]['max']) {
        static::$timers[$name]['max'] = $diff;
      }
      // Store time difference.
      if (isset(static::$timers[$name]['time'])) {
        static::$timers[$name]['time'] += $diff;
      }
      else {
        static::$timers[$name]['time'] = $diff;
      }
      unset(static::$timers[$name]['start']);
    }

    return static::$timers[$name];
  }

  /**
   * Reads the current timer counter (how many times it was stopped).
   *
   * @param string $name
   *   The name of the timer.
   *
   * @return int
   *   The current timer counter.
   */
  public static function count(string $name): int {
    if (isset(static::$timers[$name]['count'])) {
      return static::$timers[$name]['count'];
    }
    return 0;
  }

  /**
   * Get current time interval since the last start.
   *
   * @param string $name
   *   Timer ID.
   *
   * @return float
   *   Time interval in ms.
   */
  private static function getCurrentTimeDiff(string $name): float {
    $stop = microtime(TRUE);
    return round(($stop - static::$timers[$name]['start']) * 1000, 2);
  }

  /**
   * Get next trace point number.
   *
   * @return int
   *   Incremented number of point.
   */
  private static function getNextTracePoint(): int {
    return ++self::$tracePoint;
  }

  /**
   * Get current trace point number.
   *
   * @return int
   *   Number of point.
   */
  public static function getTracePoint(): int {
    return self::$tracePoint;
  }

  /**
   * Set trace point to a specific number.
   *
   * @param int $point
   *   Number of the point.
   */
  public static function setTracePoint(int $point): void {
    self::$tracePoint = $point;
  }

  /**
   * Restart trace point number.
   */
  public static function restartTracePoint(): void {
    self::$tracePoint = 0;
  }

}
