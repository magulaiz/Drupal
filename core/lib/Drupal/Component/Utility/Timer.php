<?php

namespace Drupal\Component\Utility;

/**
 * Provides helpers to use timers throughout a request.
 *
 * @ingroup utility
 */
class Timer {

  /**
   * Array to store timers.
   *
   * @var array
   */
  protected static $timers = [];

  /**
   * Starts the timer with the specified name.
   *
   * If you start and stop the same timer multiple times, the measured intervals
   * will be accumulated.
   *
   * @param string $name
   *   The name of the timer.
   */
  public static function start($name) {
    static::$timers[$name]['start'] = hrtime(TRUE);
    if (isset(static::$timers[$name]['count'])) {
      static::$timers[$name]['count']++;
    }
    else {
      static::$timers[$name]['count'] = 1;
    }
  }

  /**
   * Reads the current timer value without stopping the timer.
   *
   * @param string $name
   *   The name of the timer.
   *
   * @return int
   *   The current timer value in ms.
   */
  public static function read($name) {
    if (isset(static::$timers[$name]['start'])) {
      $stop = hrtime(TRUE);
      $start = static::$timers[$name]['start'];
      $elapsedNanoseconds = $stop[0] * 1e9 + $stop[1] - ($start[0] * 1e9 + $start[1]);
      $elapsedMilliseconds = $elapsedNanoseconds / 1e6;
      $diff = round($elapsedMilliseconds, 2);
      if (isset(static::$timers[$name]['time'])) {
        $diff += static::$timers[$name]['time'];
      }
      return $diff;
    }

    // If the timer has been stopped previously, return the stored time.
    return isset(static::$timers[$name]['time']) ?? '';
  }

  /**
   * Stops the timer with the specified name.
   *
   * @param string $name
   *   The name of the timer.
   *
   * @return array
   *   A timer array. The array contains the number of times the timer has been
   *   started and stopped (count) and the accumulated timer value in ms (time).
   */
  public static function stop($name) {
    if (isset(static::$timers[$name]['start'])) {
      $stop = hrtime(TRUE);
      $start = static::$timers[$name]['start'];
      $elapsedNanoseconds = $stop[0] * 1e9 + $stop[1] - ($start[0] * 1e9 + $start[1]);
      $elapsedMilliseconds = $elapsedNanoseconds / 1e6;
      $diff = round($elapsedMilliseconds, 2);

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

}
