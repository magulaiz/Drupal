<?php

declare(strict_types = 1);

namespace Drupal\migrate\Instrument;

/**
 * Instrument for migration performance measurement.
 */
class MigrateInstrument {

  /**
   * Storage for marking the points where iterations start.
   *
   * Default iteration ID is 0;
   */
  protected static array $iterationPoints;

  /**
   * List of instruments.
   *
   * Each instrument structure is:
   *
   * @code
   * 'id' => [
   *   'timer' => sum of time in ms,
   *   'count' => how many times it was triggered,
   *   'min' => lowest value,
   *   'max' => highest value,
   *   and more...
   * ]
   * @endcode
   */
  protected static array $instruments = [];

  /**
   * Flag if migration instrument is enabled or disabled.
   */
  protected static bool $enabled = FALSE;

  /**
   * Storage for the 1st registered instrument.
   *
   * It is used as a base of total time for calculation of time fraction.
   */
  protected static string $firstInstrumentId;

  /**
   * Enable instruments.
   */
  public static function enable(): void {
    static::$enabled = TRUE;
  }

  /**
   * Disable instruments.
   */
  public static function disable(): void {
    static::$enabled = FALSE;
  }

  /**
   * Are instruments enabled? Getter for the instrument system status.
   *
   * @return bool
   *   TRUE if instruments are enabled.
   */
  public static function isEnabled(): bool {
    return static::$enabled;
  }

  /**
   * Start measuring for given instrument.
   *
   * This method is used in similar way as Timer::start.
   *
   * @param string $id
   *   Instrument ID.
   */
  public static function start(string $id): void {
    if (self::isEnabled()) {
      // Register new instrument and start time measuring.
      self::registerInstrument($id);
      MigrateTimer::start($id);
    }
  }

  /**
   * Stop measuring for given instrument.
   *
   * This method is used in similar way as Timer::stop.
   *
   * @param string $id
   *   Instrument ID.
   */
  public static function stop(string $id): void {
    // Only registered instrument can be stopped.
    if (self::isEnabled() && self::isRegistered($id)) {
      MigrateTimer::stop($id);
    }
  }

  /**
   * Get current data for all instruments.
   *
   * @return array
   *   All instruments data.
   */
  public static function getInstruments(): array {
    foreach (self::getInstrumentIds() as $id) {
      self::$instruments[$id] = self::getInstrument($id);
    }
    return self::$instruments;
  }

  /**
   * Get IDs (names) of all registered instruments.
   *
   * @return array
   *   List of Instrument IDs.
   */
  protected static function getInstrumentIds(): array {
    return array_keys(self::$instruments);
  }

  /**
   * Register a new instrument.
   *
   * @param string $id
   *   Instrument ID.
   */
  protected static function registerInstrument(string $id): void {
    if (!isset(self::$instruments[$id])) {
      self::$instruments[$id] = [];
    }
    // Remember the 1st instrument for calculation of percent of total time.
    if (count(self::$instruments) == 1) {
      self::$firstInstrumentId = $id;
    }
  }

  /**
   * Get current Instrument data for given ID.
   *
   * It returns all Timer data for given ID and calculates average value.
   *
   * @param string $id
   *   Instrument ID.
   *
   * @return array
   *   Instrument data.
   */
  protected static function getInstrument(string $id): array {
    $instrument = MigrateTimer::get($id);
    // Add ID.
    $instrument['id'] = $id;
    // Add a percentage fraction of total time.
    $instrument['fraction'] = self::calculateTimeFraction($instrument);
    // Add calculated average time value for the instrument.
    $instrument['avg'] = self::calculateAvg($instrument);
    // Add calculated range value for the instrument.
    $instrument['range'] = self::calculateRange($instrument);
    // Add calculated mid-range for the instrument.
    $instrument['mid-range'] = self::calculateMidRange($instrument);
    // Add how far is AVG from mid-range for the instrument.
    $instrument['offset'] = self::calculateAvgOffset($instrument);
    // Convert list of trace points to readable string.
    $instrument['points'] = implode(',', array_unique($instrument['point']));
    if (strlen($instrument['points']) >= 12) {
      $instrument['points'] = substr($instrument['points'], 0, 11) . '~';
    }
    // Remove useless info.
    unset($instrument['start']);
    unset($instrument['point']);

    return $instrument;
  }

  /**
   * Calculate average value of the instrument.
   *
   * @param array $instrument
   *   Instrument ID.
   *
   * @return float
   *   Average value (mean).
   */
  private static function calculateAvg(array $instrument): float {
    if ($instrument['count'] > 0) {
      return round($instrument['time'] / $instrument['count'], 2);
    }
    return 0;
  }

  /**
   * Calculate statistical range value of the instrument.
   *
   * @param array $instrument
   *   Instrument ID.
   *
   * @return float
   *   Range.
   */
  private static function calculateRange(array $instrument): float {
    if (isset($instrument['min'])) {
      return $instrument['max'] - $instrument['min'];
    }
    return 0;
  }

  /**
   * Get Instrument result table.
   *
   * It gets data from all registered instruments and prepare values
   * for displaying in table format.
   *
   * @return array
   *   Table data.
   */
  public static function getInstrumentResultTable(): array {
    // Make values right-aligned.
    $instruments = self::makeNumericValuesRightAligned(self::getInstruments());
    return $instruments;
  }

  /**
   * Iteration starts. Remember the tracing point.
   *
   * @param string $iterationId
   *   Optional ID of iteration (if multiple iterations are in game).
   */
  public static function iterationStart(string $iterationId = '0'): void {
    // Remember iteration current point if it is the first call.
    if (!isset(self::$iterationPoints[$iterationId])) {
      self::$iterationPoints[$iterationId] = MigrateTimer::getTracePoint();
    }
    else {
      // Reset current point to saved starting iteration point.
      MigrateTimer::setTracePoint(self::$iterationPoints[$iterationId]);
    }
  }

  /**
   * Calculate Average offset, a relative distance of AVG from mid-range.
   *
   * Offset in this context means how far is the AVG from the center of range
   * (mid-range). It reports how the values are distributed. If the most values
   * are above the mid-range, the offset is a positive number. It means there
   * are a few extreme low values in the measurement.
   *
   * @param array $instrument
   *   Instrument ID.
   *
   * @return float
   *   Relative distance of the average from the mid-range.
   */
  private static function calculateAvgOffset(array $instrument): float {
    $midRange = self::calculateMidRange($instrument);
    $offset = 0;
    if ($instrument['range'] > 0 && $midRange > 0) {
      $offset = round(($instrument['avg'] - $midRange) / ($instrument['range'] / 2) * 100);
    }
    return $offset;
  }

  /**
   * Calculate mid-range.
   *
   * @param array $instrument
   *   Instrument.
   *
   * @return float
   *   Mid-range.
   */
  private static function calculateMidRange(array $instrument): float {
    $midRange = round(($instrument['min'] + $instrument['max']) / 2, 4);
    return $midRange;
  }

  /**
   * Make numeric values right-aligned.
   *
   * Get the longest value from each key (column) and add spaces to be aligned.
   *
   * @param array $instruments
   *   List of instruments.
   *
   * @return array
   *   List of instruments with numeric values aligned.
   */
  private static function makeNumericValuesRightAligned(array $instruments): array {
    $longest = [];
    foreach ($instruments as $instrument) {
      foreach ($instrument as $key => $value) {
        // Align only numeric columns.
        if (!is_numeric($value)) {
          continue;
        }
        if (!isset($longest[$key])) {
          $longest[$key] = 0;
        }
        $value = (string) $value;
        if (strlen($value) > $longest[$key]) {
          $longest[$key] = strlen($value);
        }
      }
    }
    foreach ($instruments as &$instrument) {
      foreach ($instrument as $key => &$value) {
        if (!isset($longest[$key])) {
          continue;
        }
        $format = self::getColumnFormat($key, $longest[$key]);
        $value = sprintf($format, $value);
      }
    }
    return $instruments;
  }

  /**
   * Calculate Time fraction, percentage of the 1st instrument time.
   *
   * @param array $instrument
   *   Instrument.
   *
   * @return float
   *   Fraction in percent.
   */
  private static function calculateTimeFraction(array $instrument): float {
    $total = self::getTimeOfFirstInstrument();
    return round($instrument['time'] / $total * 100, 2);
  }

  /**
   * Get ID of the 1st registered instrument.
   *
   * @return string
   *   ID.
   */
  public static function getFirstInstrumentId(): string {
    return self::$firstInstrumentId;
  }

  /**
   * Get time of the 1st registered instrument.
   *
   * @return float
   *   Time of the 1st instrument.
   */
  private static function getTimeOfFirstInstrument(): float {
    return MigrateTimer::get(self::getFirstInstrumentId())['time'];
  }

  /**
   * Get sprintf format for specific table column.
   *
   * @param string $key
   *   Instrument key (column).
   * @param int $width
   *   Width of the column in characters.
   *
   * @return string
   *   Formatted string.
   */
  private static function getColumnFormat(string $key, int $width): string {
    $formats = [
      'default' => "% " . $width . ".2f",
      'fraction' => "% 6.1f%%",
      'offset' => "%+4d%%",
      'points' => "%-" . $width . "s",
      'count' => "% " . $width . "d",
    ];
    if (!isset($formats[$key])) {
      $key = 'default';
    }
    return $formats[$key];
  }

  /**
   * Check if given instrument ID is registered in the system.
   *
   * @param string $id
   *   Instrument ID.
   *
   * @return bool
   *   TRUE for registered instrument. FALSE for unknown instrument ID.
   */
  public static function isRegistered(string $id): bool {
    return in_array($id, self::getInstrumentIds());
  }

}
