<?php

namespace Drupal\Core\Logger;

/**
 * A copy of \Psr\Log\LoggerTrait that uses RFC 5424 compliant log levels.
 *
 * Internal Drupal logger implementations should use this trait instead of
 * \Psr\Log\LoggerTrait. Callers of those implementations are responsible for
 * translating any other log level format to RFC 5424 compliant integers.
 *
 * @see https://groups.google.com/forum/#!topic/php-fig/Rc5YDhNdGz4
 * @see https://www.drupal.org/node/2267545
 */
trait RfcLoggerTrait {

  /**
   * {@inheritdoc}
   */
  public function emergency(string|\Stringable $message, array $context = []): void {
    $this->log(RfcLogLevelEnum::Emergency->value, $message, $context);
  }

  /**
   * {@inheritdoc}
   */
  public function alert(string|\Stringable $message, array $context = []): void {
    $this->log(RfcLogLevelEnum::Alert->value, $message, $context);
  }

  /**
   * {@inheritdoc}
   */
  public function critical(string|\Stringable $message, array $context = []): void {
    $this->log(RfcLogLevelEnum::Critical->value, $message, $context);
  }

  /**
   * {@inheritdoc}
   */
  public function error(string|\Stringable $message, array $context = []): void {
    $this->log(RfcLogLevelEnum::Error->value, $message, $context);
  }

  /**
   * {@inheritdoc}
   */
  public function warning(string|\Stringable $message, array $context = []): void {
    $this->log(RfcLogLevelEnum::Warning->value, $message, $context);
  }

  /**
   * {@inheritdoc}
   */
  public function notice(string|\Stringable $message, array $context = []): void {
    $this->log(RfcLogLevelEnum::Notice->value, $message, $context);
  }

  /**
   * {@inheritdoc}
   */
  public function info(string|\Stringable $message, array $context = []): void {
    $this->log(RfcLogLevelEnum::Info->value, $message, $context);
  }

  /**
   * {@inheritdoc}
   */
  public function debug(string|\Stringable $message, array $context = []): void {
    $this->log(RfcLogLevelEnum::Debug->value, $message, $context);
  }

  /**
   * {@inheritdoc}
   */
  abstract public function log($level, string|\Stringable $message, array $context = []): void;

}
