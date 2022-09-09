<?php

namespace Drupal\dblog\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Component\Render\MarkupInterface;
use Drupal\dblog\DblogFormatterInterface;

/**
 * Interface for dblog entries.
 */
interface DblogEntryInterface extends ContentEntityInterface {

  /**
   * Hostname of the user who triggered the event.
   *
   * @return string
   *   The hostname of the user who triggered the event.
   */
  public function getHostname() : string;

  /**
   * Link to view the result of the event.
   *
   * Some modules can provide more information about an event, for example when
   * creating nodes. In this case link would be a link to edit node created.
   *
   * @return string
   *   The link to the event information.
   */
  public function getLink() : string;

  /**
   * URL of the origin of the event.
   *
   * @return string
   *   The origin of the event.
   */
  public function getLocation() : string;

  /**
   * URL of referring page.
   *
   * @return string
   *   The referer of the event.
   */
  public function getReferer() : string;

  /**
   * The severity level of the event.
   *
   * @see \Drupal\Core\Logger\RfcLogLevel::getLevels()
   *
   * @return int
   *   The severity of the event.
   */
  public function getSeverity() : int;

  /**
   * Unix timestamp of when event occurred.
   *
   * @return int
   *   The unix timestamp.
   */
  public function getTimestamp() : int;

  /**
   * Type of log message, for example "user" or "page not found".
   *
   * @return string
   *   The type of the event.
   */
  public function getType() : string;

  /**
   * The id of the user who triggered the event.
   *
   * @return int
   *   The user id.
   */
  public function getUid() : int;

  /**
   * Returns the messages formatted with placeholders already replaced.
   *
   * @param \Drupal\dblog\DblogFormatterInterface $formatter
   *   A dblog formatter service.
   *
   * @return \Drupal\Component\Render\MarkupInterface
   *   The formatted message.
   */
  public function getFormattedMessage(DblogFormatterInterface $formatter) : MarkupInterface;

}
