<?php

declare(strict_types=1);

namespace Drupal\user_data_test;

use Drupal\Core\Database\Connection;
use Drupal\user\UserDataInterface;

/**
 * Demonstration JSON-backed user data service.
 *
 * The syntax here is rather typical SQL syntax, however every driver is
 * different. This example targets MySQL. Since the user data service is
 * backend-overridable, this would necessarily be tailored to specific drivers.
 */
class JsonBackedUserData implements UserDataInterface {

  /**
   * Constructs a new user data service.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection to use.
   */
  public function __construct(
    protected Connection $connection,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function get($module, $uid = NULL, $name = NULL) {
    // If $module, $uid, and $name were passed, return the value.
    if (isset($name) && isset($uid)) {
      $query = $this->connection->select('users_data_json', 'ud');
      $query->addExpression('JSON_UNQUOTE(JSON_EXTRACT(data, :path))', NULL, [
        ':path' => sprintf('$.%s.%s', $module, $name),
      ]);
      return $query->execute()->fetchField() ?: NULL;
    }
    // Module data for a particular uid. Very boring implementation by design.
    // There is no real advantage in filtering in this case.
    if (isset($uid)) {
      return $this->getAllForUser($uid)[$module] ?? [];
    }

    $jsonpath = isset($name)
      ? sprintf('$.%s.%s', $module, $name)
      : sprintf('$.%s', $module);
    $query = $this->connection->select('users_data_json', 'ud');
    $query->where('JSON_CONTAINS_PATH(data, :one_all, :path)', [
      ':one_all' => 'all',
      ':path' => $jsonpath,
    ]);
    $query->addExpression('JSON_UNQUOTE(JSON_EXTRACT(data, :path))', 'value', [
      ':path' => $jsonpath,
    ]);
    $query->fields('ud', ['uid']);
    return array_map(fn (object $row) => isset($name) ? $row->value : json_decode($row->value, TRUE), $query->execute()->fetchAllAssoc('uid'));
  }

  /**
   * Get all data for a particular uid.
   *
   * @param int $uid
   *   User ID.
   *
   * @return array
   *   Data array, keyed by module name.
   */
  public function getAllForUser(int $uid): array {
    $query = $this->connection->select('users_data_json', 'ud')
      ->fields('ud', ['data'])
      ->condition('uid', $uid);
    $response = $query->execute()->fetchField();
    return !empty($response) ? json_decode($response, TRUE) : [];
  }

  /**
   * {@inheritdoc}
   */
  public function set($module, $uid, $name, $value): void {
    $record = $this->getAllForUser($uid);
    $record[$module][$name] = $value;
    $this->setRecordForUser($uid, $record);
  }

  protected function setRecordForUser(int $uid, array $record): void {
    $this->connection->merge('users_data_json')
      ->keys([
        'uid' => $uid,
      ])
      ->fields([
        'data' => json_encode($record),
      ])
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function delete($module = NULL, $uid = NULL, $name = NULL): void {
    // In theory we could get even more polymorphic than the legacy
    // service, but let's not get too crazy quite yet.
    // Note, this does not implement multiple module deletion and other
    // polymorphic weirdness the legacy service does. This is for
    // demonstration of basic functionality, only, and none of this code
    // directly implements anything special about JSON data storage.
    $users = [];
    if (isset($module)) {
      if ($uid === NULL && $name === NULL) {
        $users = array_keys($this->get($module));
      }
      elseif ($uid !== NULL) {
        $users = [$uid];
      }
    }
    // Delete all instances of data for a module.
    foreach ($users as $uid) {
      // This is not super performant, however we can't JSON-patch with SQL drivers.
      $record = $this->getAllForUser($uid);
      if ($name === NULL) {
        unset($record[$module]);
      }
      else {
        unset($record[$module][$name]);
      }
      $this->setRecordForUser($uid, $record);
    }
  }

}
