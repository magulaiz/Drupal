<?php

declare(strict_types=1);

namespace Drupal\Core\Database\EventSubscriber;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Event\ExecuteMethodEnsuringSchemaEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Database\DatabaseException;

/**
 * Response subscriber to schema handling requests.
 */
class SchemaRequestSubscriber implements EventSubscriberInterface {

  public function __construct(
    protected readonly Connection $connection,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      ExecuteMethodEnsuringSchemaEvent::class => 'onExecuteMethodEnsuringSchema',
    ];
  }

  /**
   * Processes a request to execute a callback with schema enforcement.
   *
   * @param \Drupal\Core\Database\Event\ExecuteMethodEnsuringSchemaEvent $event
   *   The event to process.
   */
  public function onExecuteMethodEnsuringSchema(ExecuteMethodEnsuringSchemaEvent $event): void {
    try {
      $event->setResult(($event->execute)());
      $event->setSuccess(TRUE);
      return;
    }
    catch (\Exception $e) {
      // If there was an exception, try to create the schema.
      $event->setSuccess(FALSE);
      $schemaChanged = $this->processSchema($event->schema);
      if (!$schemaChanged) {
        // If the exception happened for other reasons than the missing schema,
        // propagate the exception.
        throw $e;
      }
      // Now that the schema has been created, try again if requested.
      if ($event->retryAfterSchemaEnsured && $schemaChanged) {
        $event->setResult(($event->execute)());
        $event->setSuccess(TRUE);
      }
    }
  }

  /**
   * Processes the schema creating the missing tables.
   *
   * @param array<string,array<string,mixed>> $schema
   *   A database schema specification, with table name as key and schema
   *   array as value.
   */
  protected function processSchema(array $schema): bool {
    $schemaChanged = FALSE;
    foreach ($schema as $name => $definition) {
      try {
        if (!$this->connection->schema()->tableExists($name)) {
          try {
            $this->connection->schema()->createTable($name, $definition);
            $schemaChanged = TRUE;
          }
          // In a race condition, if another process has already created the
          // table, attempting to create it will throw an exception. In this
          // case just assume the schema was changed here.
          catch (DatabaseException) {
            $schemaChanged = TRUE;
          }
        }
      }
      catch (\Exception) {
        return FALSE;
      }
    }
    return $schemaChanged;
  }

}
