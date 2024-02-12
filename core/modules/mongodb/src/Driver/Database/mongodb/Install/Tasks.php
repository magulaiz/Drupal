<?php

namespace Drupal\mongodb\Driver\Database\mongodb\Install;

use Drupal\Core\Database\Database;
use Drupal\Core\Database\Install\Tasks as InstallTasks;

/**
 * Specifies installation tasks for MongoDB databases.
 */
class Tasks extends InstallTasks {

  /**
   * {@inheritdoc}
   */
  protected $pdoDriver = 'mongodb';

  /**
   * {@inheritdoc}
   */
  protected $error = NULL;

  /**
   * {@inheritdoc}
   */
  protected $tasks = array(
    array(
      'function'    => 'checkEngineVersion',
      'arguments'   => [],
    ),
    array(
      'function'    => 'checkDropCollectionIfExists',
      'arguments'   => array('name' => 'drupal_install_test'),
    ),
    array(
      'function'    => 'checkCreateCollection',
      'arguments'   => array('name' => 'drupal_install_test', 'definition' => [
        'fields' => [
          'id'  => [
            'type' => 'int',
            'default' => NULL,
          ],
        ],
      ]),
    ),
    array(
      'function'    => 'checkInsertCollection',
      'arguments'   => array('name' => 'drupal_install_test', 'fields' => ['id' => 1]),
    ),
    array(
      'function'    => 'checkUpdateCollection',
      'arguments'   => array('name' => 'drupal_install_test', 'condition' => ['id', 1, '='], 'fields' => ['id' => 2]),
    ),
    array(
      'function'    => 'checkDeleteCollection',
      'arguments'   => array('name' => 'drupal_install_test', 'condition' => ['id', 2, '=']),
    ),
    array(
      'function'    => 'checkDropCollection',
      'arguments'   => array('name' => 'drupal_install_test'),
    ),
  );

  /**
   * {@inheritdoc}
   */
  public function name() {
    return t('MongoDB');
  }

  /**
   * {@inheritdoc}
   */
  public function minimumVersion() {
    return '3.6';
  }

  /**
   * Check whether Drupal is installable on the database.
   */
  public function installable() {
    return extension_loaded('mongodb') && empty($this->error);
  }

  /**
   * {@inheritdoc}
   */
  protected function connect() {
    try {
      Database::getConnection();
      $this->pass('Drupal can CONNECT to MongoDB.');
    }
    catch (\Exception $e) {
      $this->fail('Failed to connect to MongoDB');
    }
    return TRUE;
  }

  /**
   * Enable the MongoDB module.
   */
  public function enableModule() {
    /** @var \Drupal\Core\Extension\ModuleInstallerInterface  */
    $installer = \Drupal::service('module_installer');
    $installer->install(['mongodb']);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormOptions(array $database) {
    $form = parent::getFormOptions($database);
    if (empty($form['advanced_options']['port']['#default_value'])) {
      $form['advanced_options']['port']['#default_value'] = '27017';
    }
    return $form;
  }

  /**
   * Check the if the test collection can be dropped if it exists.
   */
  protected function checkDropCollectionIfExists($name) {
    try {
      if (Database::getConnection()->schema()->tableExists($name)) {
        Database::getConnection()->schema()->dropTable($name);
        $this->pass(t("The database server was able to drop the existing collection %name.", array('%name' => $name)));
      }
    }
    catch (\Exception $e) {
      $this->fail(t("The database server is unable to drop the existing collection %name.", array('%name' => $name)));
    }
  }

  /**
   * Check the if the test collection can be created.
   */
  protected function checkCreateCollection($name, $definition) {
    try {
      Database::getConnection()->schema()->createTable($name, $definition);
      $this->pass(t("The database server was able to create the collection %name.", array('%name' => $name)));
    }
    catch (\Exception $e) {
      $this->fail(t("The database server is unable to create the collection %name.", array('%name' => $name)));
    }
  }

  /**
   * Check the if data can be inserted into the test collection.
   */
  protected function checkInsertCollection($name, $fields) {
    try {
      Database::getConnection()->insert($name)->fields($fields)->execute();
      $this->pass(t("The database server was able to insert data into the collection %name.", array('%name' => $name)));
    }
    catch (\Exception $e) {
      $this->fail(t("The database server is unable to insert data into the collection %name.", array('%name' => $name)));
    }
  }

  /**
   * Check the if data can be updated in the test collection.
   */
  protected function checkUpdateCollection($name, array $condition = [], array $fields = []) {
    try {
      $query = Database::getConnection()->update($name)->fields($fields)->condition($condition[0], $condition[1], $condition[2])->execute();
      $this->pass(t("The database server was able to update data in the collection %name.", array('%name' => $name)));
    }
    catch (\Exception $e) {
      $this->fail(t("The database server is unable to update data in the collection %name.", array('%name' => $name)));
    }
  }

  /**
   * Check the if data can be deleted in the test collection.
   */
  protected function checkDeleteCollection($name, array $condition = []) {
    try {
      $query = Database::getConnection()->delete($name)->condition($condition[0], $condition[1], $condition[2])->execute();
      $this->pass(t("The database server was able to delete data in the collection %name.", array('%name' => $name)));
    }
    catch (\Exception $e) {
      $this->fail(t("The database server is unable to delete data in the collection %name.", array('%name' => $name)));
    }
  }

  /**
   * Check the if the test collection can be dropped.
   */
  protected function checkDropCollection($name) {
    try {
      Database::getConnection()->schema()->dropTable($name);
      $this->pass(t("The database server was able to drop the collection %name.", array('%name' => $name)));
    }
    catch (\Exception $e) {
      $this->fail(t("The database server is unable to drop the collection %name.", array('%name' => $name)));
    }
  }

}
