<?php

namespace Drupal\syslog\Logger;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LogMessageParserInterface;
use Drupal\Core\Logger\RfcLoggerTrait;
use Psr\Log\LoggerInterface;

// cspell:ignore DGRAM

/**
 * Redirects logging messages to syslog.
 */
class SysLog implements LoggerInterface {
  use RfcLoggerTrait;

  /**
   * A configuration object containing syslog settings.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Remote Syslog server hostname.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $hostname;

  /**
   * Remote Syslog server port (default 514).
   *
   * @var mixed
   */
  protected $port;

  /**
   * Syslog identity (usually "drupal").
   *
   * @var mixed
   */
  protected $identity;

  /**
   * Remote Syslog server port (default LOG_LOCAL0 / 128).
   *
   * @var mixed
   */
  protected $facility;

  /**
   * The message's placeholders parser.
   *
   * @var \Drupal\Core\Logger\LogMessageParserInterface
   */
  protected $parser;

  /**
   * Stores whether there is a system logger connection opened or not.
   *
   * @var bool
   */
  protected $connectionOpened = FALSE;

  /**
   * Constructs a SysLog object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory object.
   * @param \Drupal\Core\Logger\LogMessageParserInterface $parser
   *   The parser to use when extracting message variables.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LogMessageParserInterface $parser) {
    $this->config = $config_factory->get('syslog.settings');
    $this->hostname = $this->config->get("hostname") ?? '';
    $this->port = $this->config->get("port") ?? 514;
    $this->identity = $this->config->get("identity") ?? '';
    $this->facility = $this->config->get("facility") ?? LOG_LOCAL0;
    $this->parser = $parser;
  }

  /**
   * Opens a connection to the system logger.
   */
  protected function openConnection() {
    if (!$this->connectionOpened) {
      $this->connectionOpened = openlog($this->identity, LOG_NDELAY, $this->facility);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []) {
    global $base_url;

    $format = $this->config->get('format');
    // If no format is configured then a message will not be written to syslog
    // so return early. This occurs during installation of the syslog module
    // before configuration has been written.
    if (empty($format)) {
      return;
    }

    // Populate the message placeholders and then replace them in the message.
    $message_placeholders = $this->parser->parseMessagePlaceholders($message, $context);
    $message = empty($message_placeholders) ? $message : strtr($message, $message_placeholders);

    $entry = strtr($format, [
      '!base_url' => $base_url,
      '!timestamp' => $context['timestamp'],
      '!type' => $context['channel'],
      '!ip' => $context['ip'],
      '!request_uri' => $context['request_uri'],
      '!referer' => $context['referer'],
      '!severity' => $level,
      '!uid' => $context['uid'],
      '!link' => strip_tags($context['link']),
      '!message' => strip_tags($message),
    ]);

    $this->syslogWrapper($level, $entry);
  }

  /**
   * A syslog wrapper to make syslog functionality testable.
   *
   * @param int $level
   *   The syslog priority.
   * @param string $entry
   *   The message to send to syslog function.
   */
  protected function syslogWrapper($level, $entry) {
    if (empty($this->hostname)) {
      // Ensure we have a connection available.
      $this->openConnection();

      syslog($level, $entry);
      return;
    }
    $this->sendUdp($level, $entry);
  }

  /**
   * Sends a syslog message to a UDP socket.
   *
   * @param int $level
   *   The syslog priority.
   * @param string $entry
   *   The message to send to syslog function.
   */
  protected function sendUdp($level, $entry) {
    $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    $msg = '<' . $this->facility . '>' . $this->identity . ': ' . $entry;
    socket_sendto($sock, $msg, strlen($msg), 0, $this->hostname, $this->port);
    socket_close($sock);
  }

}
