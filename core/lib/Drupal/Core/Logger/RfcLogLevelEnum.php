<?php

declare(strict_types=1);

namespace Drupal\Core\Logger;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Psr\Log\LogLevel;

enum RfcLogLevelEnum: int {

  /**
   * Log message severity -- Emergency: system is unusable.
   */
  case Emergency = 0;

  /**
   * Log message severity -- Alert: action must be taken immediately.
   */
  case Alert = 1;

  /**
   * Log message severity -- Critical conditions.
   */
  case Critical = 2;

  /**
   * Log message severity -- Error conditions.
   */
  case Error = 3;

  /**
   * Log message severity -- Warning conditions.
   */
  case Warning = 4;

  /**
   * Log message severity -- Normal but significant conditions.
   */
  case Notice = 5;

  /**
   * Log message severity -- Informational messages.
   */
  case Info = 6;

  /**
   * Log message severity -- Debug-level messages.
   */
  case Debug = 7;

  /**
   * Returns a list of severity levels, as defined in RFC 5424.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup[]
   *   Array of the possible severity levels for log messages.
   *
   * @see http://tools.ietf.org/html/rfc5424
   * @ingroup logging_severity_levels
   */
  public static function getLevels(): array {
    return [
      static::Emergency->value => new TranslatableMarkup('Emergency'),
      static::Alert->value => new TranslatableMarkup('Alert'),
      static::Critical->value => new TranslatableMarkup('Critical'),
      static::Error->value => new TranslatableMarkup('Error'),
      static::Warning->value => new TranslatableMarkup('Warning'),
      static::Notice->value => new TranslatableMarkup('Notice'),
      static::Info->value => new TranslatableMarkup('Info'),
      static::Debug->value => new TranslatableMarkup('Debug'),
    ];
  }

  /**
   * Returns the RFC 5424 log level for a given PSR-3 log level.
   *
   * @param string $level
   *    The PSR-3 log level.
   */
  public static function fromPsr3(string $level): static {
    return match ($level) {
      LogLevel::EMERGENCY => static::Emergency,
      LogLevel::ALERT => static::Alert,
      LogLevel::CRITICAL => static::Critical,
      LogLevel::ERROR => static::Error,
      LogLevel::WARNING => static::Warning,
      LogLevel::NOTICE => static::Notice,
      LogLevel::INFO => static::Info,
      LogLevel::DEBUG => static::Debug,
      default => throw new \InvalidArgumentException(sprintf('Unknown PSR-3 log level: %s', $level)),
    };
  }

}
