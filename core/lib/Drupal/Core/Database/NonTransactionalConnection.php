<?php

declare(strict_types=1);

namespace Drupal\Core\Database;

use Drupal\Core\Database\Event\DatabaseEvent;
use Drupal\Core\Database\Exception\TransactionsNotSupportedException;
use Drupal\Core\Database\Transaction\TransactionManagerInterface;
use Drupal\Core\Pager\PagerManagerInterface;

/**
 * A decorator class wrapping a connection, not supporting transactions.
 *
 * @internal
 */
final class NonTransactionalConnection extends Connection {

  /**
   * Constructor.
   *
   * @param Connection $wrappedConnection
   *   Database connection to wrap calls.
   */
  public function __construct(protected Connection $wrappedConnection) {}

  /**
   * {@inheritdoc}
   */
  public function transactionManager(): TransactionManagerInterface {
    throw new TransactionsNotSupportedException();
  }

  /**
   * {@inheritdoc}
   */
  public function inTransaction(): bool {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function startTransaction($name = '') {
    throw new TransactionsNotSupportedException();
  }

  /**
   * {@inheritdoc}
   */
  public static function open(array &$connection_options = []) {
    throw new \RuntimeException(sprintf('%s is a wrapper only around existing connection objects.', __CLASS__));
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
  public function schema() {
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
  public function driver() {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function databaseType() {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function createDatabase($database) {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function mapConditionOperator($operator) {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function __destruct() {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function getClientConnection(): object {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function getConnectionOptions() {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function attachDatabase(string $database): void {
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
  public function prefixTables($sql) {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function quoteIdentifiers($sql) {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function getFullQualifiedTableName($table) {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function prepareStatement(string $query, array $options, bool $allow_row_count = FALSE): StatementInterface {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function setTarget($target = NULL) {
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
  public function setKey($key) {
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
  public function setLogger(Log $logger) {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
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
  public function makeComment($comments) {
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
  public function getDriverClass($class) {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function exceptionHandler() {
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
  public function escapeDatabase($database) {
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
  public function escapeField($field) {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function escapeAlias($field) {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function escapeLike($string) {
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
  public function supportsTransactionalDDL() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function quote($string, $parameter_type = \PDO::PARAM_STR) {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep(): array {
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
  public function getPagerManager(): PagerManagerInterface {
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
  public function isEventEnabled(string $eventName): bool {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function enableEvents(array $eventNames): static {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function disableEvents(array $eventNames): static {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

  /**
   * {@inheritdoc}
   */
  public function dispatchEvent(DatabaseEvent $event, ?string $eventName = NULL): DatabaseEvent {
    return $this->wrappedConnection->{__FUNCTION__}(...func_get_args());
  }

}
