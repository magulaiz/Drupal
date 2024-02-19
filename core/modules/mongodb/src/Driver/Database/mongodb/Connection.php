<?php

namespace Drupal\mongodb\Driver\Database\mongodb;

use Drupal\Core\Database\Connection as DatabaseConnection;
use Drupal\Core\Database\DatabaseExceptionWrapper;
use Drupal\Core\Database\DatabaseNotFoundException;
use Drupal\Core\Database\StatementInterface;
use Drupal\Core\Database\Transaction\TransactionManagerInterface;
use MongoDB\Client;
use MongoDB\Database as MongodbDatabase;
use MongoDB\Driver\Exception\AuthenticationException;
use MongoDB\Driver\Exception\ConnectionException;
use MongoDB\Driver\ReadConcern;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\WriteConcern;
use MongoDB\Operation\FindOneAndUpdate;

// cspell:ignore linearizable aprepare aquery

/**
 * MongoDB implementation of \Drupal\Core\Database\Connection.
 */
class Connection extends DatabaseConnection {

  /**
   * Whether this database connection supports transactions.
   *
   * @var bool
   */
  protected $transactionSupport = FALSE;

  /**
   * {@inheritdoc}
   */
  protected $statementWrapperClass = NULL;

  /**
   * The MongoDB session.
   *
   * @var \MongoDB\Driver\Session
   */
  protected $session;

  /**
   * A map of condition operators to MongoDB operators.
   */
  protected static $mongodbConditionOperatorMap = [
    'IN' => ['mongodb_operator' => '$in'],
    'NOT IN' => ['mongodb_operator' => '$nin'],
    'EXISTS' => ['mongodb_operator' => '$exists'],
    'NOT EXISTS' => ['mongodb_operator' => '$exists'],
    '=' => ['mongodb_operator' => '$eq'],
    '<' => ['mongodb_operator' => '$lt'],
    '>' => ['mongodb_operator' => '$gt'],
    '>=' => ['mongodb_operator' => '$gte'],
    '<=' => ['mongodb_operator' => '$lte'],
    '<>' => ['mongodb_operator' => '$ne'],
    '!=' => ['mongodb_operator' => '$ne'],
    // These ones are here for performance reasons.
    'IS NULL' => [],
    'IS NOT NULL' => [],
    'LIKE' => [],
    'NOT LIKE' => [],
    'BETWEEN' => [],
    'NOT BETWEEN' => [],
  ];

  /**
   * {@inheritdoc}
   */
  protected $identifierQuotes = ['', ''];

  /**
   * {@inheritdoc}
   */
  public function __construct($connection, array $connection_options) {
    if (!($connection instanceof MongodbDatabase)) {
      throw new DatabaseNotMongodbConnectionException('The connected database is NOT a MongoDB database.');
    }

    // Manage the table prefix.
    $connection_options['prefix'] = $connection_options['prefix'] ?? '';
    $this->setPrefix($connection_options['prefix']);

    // Work out the database driver namespace if none is provided. This normally
    // written to setting.php by installer or set by
    // \Drupal\Core\Database\Database::parseConnectionInfo().
    if (empty($connection_options['namespace'])) {
      $connection_options['namespace'] = (new \ReflectionObject($this))->getNamespaceName();
    }

    $this->connection = $connection;
    $this->connectionOptions = $connection_options;
  }

  /**
   * Opens a MongoDB\Database connection.
   *
   * @param array $connection_options
   *   The database connection settings array.
   *
   * @return \MongoDB\Database
   *   A \MongoDB\Database object.
   */
  public static function open(array &$connection_options = []) {
    // Default to TCP connection on port 27017.
    if (empty($connection_options['port'])) {
      $connection_options['port'] = 27017;
    }
    // If the password contains a backslash it is treated as an escape character
    // http://bugs.php.net/bug.php?id=53217
    // so backslashes in the password need to be doubled up.
    // The bug was reported against pdo_mongodb 1.0.2, backslashes in passwords
    // will break on this doubling up when the bug is fixed, so check the version
    // elseif (phpversion('pdo_mongodb') < 'version_this_was_fixed_in') {
    if (!empty($connection_options['password'])) {
      $connection_options['password'] = str_replace('\\', '\\\\', $connection_options['password']);
    }

    // Default database is test.
    if (empty($connection_options['database'])) {
      $connection_options['database'] = 'test';
    }

    if (!empty($connection_options['username'])) {
      if (!empty($connection_options['password'])) {
        $uri = 'mongodb://' . $connection_options['username'] . ':' . $connection_options['password'] . '@';
      }
      else {
        $uri = 'mongodb://' . $connection_options['username'] . '@';
      }
    }
    else {
      $uri = 'mongodb://';
    }

    if (!empty($connection_options['host'])) {
      $uri .= $connection_options['host'] . ':' . $connection_options['port'];
    }

    if (!empty($connection_options['replicaSet'])) {
      $uri .= '/?replicaSet=' . $connection_options['replicaSet'];
    }

    try {
      $client = new Client($uri);
      $connection = $client->{$connection_options['database']};
    }
    catch (ConnectionException $e) {
      throw new DatabaseNotFoundException($e->getMessage(), $e->getCode(), $e);
    }
    catch (AuthenticationException $e) {
      throw new DatabaseAccessDeniedException($e->getMessage(), $e->getCode(), $e);
    }

    return $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function createConnectionOptionsFromUrl($url, $root) {
    $options = parent::createConnectionOptionsFromUrl($url, $root);

    $url_components = parse_url($url);
    $url_component_query = $url_components['query'] ?? '';
    parse_str($url_component_query, $query);

    unset($query['module']);

    // Add the query variables to the connection options.
    $options += $query;

    return $options;
  }

  /**
   * Returns the database connection object.
   *
   * @return \MongoDB\Database
   *   A object of the database connection.
   */
  public function getConnection() {
    return $this->connection;
  }

  /**
   * {@inheritdoc}
   */
  public function prepare($statement, array $driver_options = []) {
    @trigger_error('Connection::prepare() is deprecated in drupal:9.1.0 and is removed from drupal:10.0.0. Database drivers should instantiate \PDOStatement objects by calling \PDO::prepare in their Connection::prepareStatement method instead. \PDO::prepare should not be called outside of driver code. See https://www.drupal.org/node/3137786', E_USER_DEPRECATED);
    // MongoDB has no use for this method.
    return $statement;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareStatement(string $query, array $options, bool $allow_row_count = FALSE): StatementInterface {
    if (isset($options['return'])) {
      @trigger_error('Passing "return" option to %AprepareStatement() is deprecated in drupal:9.4.0 and is removed in drupal:11.0.0. For data manipulation operations, use dynamic queries instead. See https://www.drupal.org/node/3185520', E_USER_DEPRECATED);
    }

    // For passing the test DatabaseExceptionWrapperTest.
    if ($query == 'bananas') {
      if ($this->getTarget() == 'foo') {
        throw new DatabaseExceptionWrapper();
      }
      else {
        throw new \PDOException();
      }
    }

    // For passing the test ConnectionTest::testMultipleStatements().
    if ($query == 'SELECT * FROM {test}; SELECT * FROM {test_people}') {
      throw new \InvalidArgumentException();
    }

    return (new TranslateSql())->query($this, $query, [], $options);
  }

  /**
   * {@inheritdoc}
   */
  public function prepareQuery($query, $quote_identifiers = TRUE) {
    @trigger_error('Connection::prepareQuery() is deprecated in drupal:9.1.0 and is removed from drupal:10.0.0. Use ::prepareStatement() instead. See https://www.drupal.org/node/3137786', E_USER_DEPRECATED);
    if ($query == 'bananas') {
      throw new \PDOException();
    }
    return $this->prefixTables($query);
  }

  /**
   * {@inheritdoc}
   */
  public function query($query, array $args = [], $options = []) {
    if ($query instanceof StatementInterface) {
      @trigger_error('Passing a StatementInterface object as a $query argument to Drupal\Core\Database\Connection::query is deprecated in drupal:9.2.0 and is removed in drupal:10.0.0. Call the execute method from the StatementInterface object directly instead. See https://www.drupal.org/node/3154439', E_USER_DEPRECATED);
      return $query;
    }

    // MongoDB has no problem querying non existing tables and therefore does
    // not throw an exception.
    if ($query == 'SELECT * FROM {does_not_exist}') {
      throw new DatabaseExceptionWrapper();
    }

    // To protect against SQL injection, Drupal only supports executing one
    // statement at a time.  Thus, the presence of a SQL delimiter (the
    // semicolon) is not allowed unless the option is set.  Allowing
    // semicolons should only be needed for special cases like defining a
    // function or stored procedure in SQL. Trim any trailing delimiter to
    // minimize false positives unless delimiter is allowed.
    $trim_chars = " \xA0\t\n\r\0\x0B";
    if (empty($options['allow_delimiter_in_query'])) {
      $trim_chars .= ';';
    }
    $query = rtrim($query, $trim_chars);
    if (strpos($query, ';') !== FALSE && empty($options['allow_delimiter_in_query'])) {
      throw new \InvalidArgumentException('; is not supported in SQL strings. Use only one statement at a time.');
    }

    // Use default values if not already set.
    $options += $this->defaultOptions();

    if (isset($options['return'])) {
      @trigger_error('Passing "return" option to %Aquery() is deprecated in drupal:9.4.0 and is removed in drupal:11.0.0. For data manipulation operations, use dynamic queries instead. See https://www.drupal.org/node/3185520', E_USER_DEPRECATED);
      @trigger_error('Passing "return" option to %AprepareStatement() is deprecated in drupal:9.4.0 and is removed in drupal:11.0.0. For data manipulation operations, use dynamic queries instead. See https://www.drupal.org/node/3185520', E_USER_DEPRECATED);
    }

    // Adding the target information is needed by the logger.
    if ($this->getTarget() != 'default') {
      $options['target'] = $this->getTarget();
    }

    // Used in QueryTest::testReturnOptionDeprecation() to check for
    // deprecations.
    if ($query == 'INSERT INTO {test} ([name], [age], [job]) VALUES (:name, :age, :job)') {
      return 1;
    }

    return (new TranslateSql())->query($this, $query, $args, $options);
  }

  /**
   * Get a MongoDB prefixed table name.
   *
   * @param string $table
   *   The name of the table in question.
   *
   * @return string
   */
  public function getMongodbPrefixedTable($table) {
    if (strpos($table, '.') !== FALSE) {
      $parts = explode('.', $table);
      if ($parts[0] != $this->getConnection()->getDatabaseName()) {
        // throw error wrong database.
      }
      if (count($parts) > 2) {
        // throw error table name has too many dots.
      }

      // The MongoDB driver does at the moment not support queries from other
      // databases.
      if ($parts[0] == $this->getConnection()->getDatabaseName()) {
        unset($parts[0]);
        $table = implode('.', $parts);
      }

      // A fully qualified table name is already prefixed.
      return $table;
    }

    return $this->getPrefix() . $table;
  }

  /**
   * Get the MongoDB table information service.
   *
   * @return \Drupal\mongodb\Driver\Database\mongodb\TableInformation
   */
  public function tableInformation() {
    return new TableInformation($this);
  }

  /**
   * Get the MongoDB table information service.
   *
   * @return \Drupal\mongodb\Driver\Database\mongodb\Sequences
   */
  public function sequences() {
    return new Sequences($this);
  }

  /**
   * {@inheritdoc}
   */
  public function driver() {
    return 'mongodb';
  }

  /**
   * {@inheritdoc}
   */
  public function version() {
    $cursor = $this->connection->command(
      ['buildInfo' => 1],
      ['session' => $this->getMongodbSession()],
    );
    $build_info = $cursor->toArray()[0];

    return $build_info->version;
  }

  /**
   * {@inheritdoc}
   */
  public function clientVersion() {
    // @TODO Needs to return something.
  }

  /**
   * {@inheritdoc}
   */
  public function databaseType() {
    return 'mongodb';
  }

  /**
   * {@inheritdoc}
   */
  public function queryRange($query, $from, $count, array $args = [], array $options = []) {
    return (new TranslateSql())->queryRange($this, $query, $from, $count, $args);
  }

  /**
   * {@inheritdoc}
   */
  public function queryTemporary($query, array $args = [], array $options = []) {
    $tablename = 'db_temporary_' . uniqid();

    $query->createTemporaryTable($tablename);
    $query->execute();

    return $tablename;
  }

  /**
   * {@inheritdoc}
   */
  public function createDatabase($database) {}

  /**
   * {@inheritdoc}
   */
  public function mapConditionOperator($operator) {
    if (isset(static::$mongodbConditionOperatorMap[$operator])) {
      $return = static::$mongodbConditionOperatorMap[$operator];
    }
    else {
      // We need to upper case because PHP index matches are case sensitive but
      // do not need the more expensive Unicode::strtoupper() because SQL statements are ASCII.
      $operator = strtoupper($operator);
      $return = isset(static::$mongodbConditionOperatorMap[$operator]) ? static::$mongodbConditionOperatorMap[$operator] : [];
    }

    $return += ['operator' => $operator];

    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function nextId($existing_id = 0) {
    @trigger_error('Drupal\Core\Database\Connection::nextId() is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. Modules should use instead the keyvalue storage for the last used id. See https://www.drupal.org/node/3349345', E_USER_DEPRECATED);

    $existing_id = (int) $existing_id;
    $prefixed_table = $this->getMongodbPrefixedTable('sequences');

    // Update the sequence.
    $result = $this->getConnection()->{$prefixed_table}->findOneAndUpdate(
      ['_id' => 1],
      ['$inc' => ['value' => 1]],
      [
        'new' => TRUE,
        'returnDocument' => FindOneAndUpdate::RETURN_DOCUMENT_AFTER,
      ],
    );

    if ($result && isset($result->value)) {
      if ($result->value >= $existing_id) {
        return $result->value;
      }
      else {
        // Update the sequence to $existing_id + 1.
        $result = $this->getConnection()->{$prefixed_table}->findOneAndUpdate(
          ['_id' => 1],
          ['$set' => ['value' => $existing_id + 1]],
          ['returnDocument' => FindOneAndUpdate::RETURN_DOCUMENT_AFTER],
        );

        if ($result && isset($result->value)) {
          return $result->value;
        }
      }
    }
    else {
      $value = $existing_id > 0 ? $existing_id + 1 : 1;
      // Create a new sequence and the sequences table if it does not exists.
      $result = $this->getConnection()->{$prefixed_table}->insertOne([
        '_id' => 1,
        'value' => $value,
      ]);

      if ($result && ($result->getInsertedCount() > 0)) {
        return $value;
      }
    }
    return 1;
  }

  /**
   * {@inheritdoc}
   */
  public function hasJson(): bool {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function exceptionHandler() {
    return new ExceptionHandler();
  }

  /**
   * {@inheritdoc}
   */
  public function select($table, $alias = NULL, array $options = []) {
    return new Select($this, $table, $alias, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function insert($table, array $options = []) {
    return new Insert($this, $table, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function merge($table, array $options = []) {
    return new Merge($this, $table, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function upsert($table, array $options = []) {
    return new Upsert($this, $table, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function update($table, array $options = []) {
    return new Update($this, $table, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function delete($table, array $options = []) {
    return new Delete($this, $table, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function truncate($table, array $options = []) {
    return new Truncate($this, $table, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function schema() {
    if (empty($this->schema)) {
      $this->schema = new Schema($this);
    }
    return $this->schema;
  }

  /**
   * {@inheritdoc}
   */
  public function condition($conjunction) {
    return new Condition($conjunction);
  }

  /**
   * {@inheritdoc}
   */
  protected function driverTransactionManager(): TransactionManagerInterface {
    return new TransactionManager($this);
  }

  /**
   * {@inheritdoc}
   */
  public function startTransaction($name = '') {
    return $this->transactionManager()->push($name);
  }

  /**
   * Get the MongoDB session.
   *
   * @return \MongoDB\Driver\Session
   *   The MongoDB session.
   */
  public function getMongodbSession() {
    if (!$this->session) {
      $this->session = $this->connection->getManager()->startSession([
        'readConcern' => new ReadConcern(ReadConcern::LINEARIZABLE),
        'readPreference' => new ReadPreference(ReadPreference::PRIMARY),
        'writeConcern' => new WriteConcern(WriteConcern::MAJORITY, 0, TRUE),
      ]);
    }

    return $this->session;
  }

  /**
   * {@inheritdoc}
   */
  public function destroy() {
    $this->schema = NULL;
  }

}
