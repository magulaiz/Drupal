<?php

declare(strict_types=1);

namespace Drupal\Core\Database;

use Drupal\Core\Database\Event\DatabaseEvent;

/**
 * A decorator class wrapping a connection, not supporting transactions.
 *
 * @internal
 */
final class NonTransactionalConnection implements DatabaseConnectionInterface {

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Database\DatabaseConnectionInterface $wrappedConnection
   *   Database connection to wrap calls.
   */
  public function __construct(protected DatabaseConnectionInterface $wrappedConnection) {}

  /**
   * {@inheritdoc}
   */
  public function databaseType() {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function getProvider(): string {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function hasJson(): bool {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function driver() {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function version() {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function clientVersion() {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function getKey() {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function getTarget() {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function supportsTransactionalDDL() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getLogger() {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function setLogger(Log $logger) {
    $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function getPrefix(): string {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function query($query, array $args = [], $options = []) {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function queryRange($query, $from, $count, array $args = [], array $options = []) {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function schema() {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function select($table, $alias = NULL, array $options = []) {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function insert($table, array $options = []) {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function lastInsertId(?string $name = NULL): string {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function merge($table, array $options = []) {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function upsert($table, array $options = []) {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function update($table, array $options = []) {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function delete($table, array $options = []) {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function truncate($table, array $options = []) {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function condition($conjunction) {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function escapeTable($table) {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function dispatchEvent(DatabaseEvent $event, ?string $eventName = NULL): DatabaseEvent {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function disableEvents(array $eventNames): DatabaseConnectionInterface {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function enableEvents(array $eventNames): DatabaseConnectionInterface {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function isEventEnabled(string $eventName): bool {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

}
