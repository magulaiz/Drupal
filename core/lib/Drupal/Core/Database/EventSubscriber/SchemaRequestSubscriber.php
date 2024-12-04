<?php

declare(strict_types=1);

namespace Drupal\Core\Database\EventSubscriber;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Event\ExecuteMethodEnsuringSchemaEvent;
use Drupal\Core\Database\Exception\SchemaObjectCreationFailureException;
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
      $event->setCallbackExecutionState(TRUE);
      return;
    }
    catch (\Exception $e) {
      // If there was an exception, try to create the schema.
      $event->setCallbackExecutionState($e);
      $schemaChanged = $this->processSchema($event);
      if (!$schemaChanged) {
        // If the exception happened for other reasons than the missing schema,
        // we have stored it and can return.
        return;
      }
      // Now that the schema has been created, try again if requested.
      if ($event->retryAfterSchemaEnsured && $schemaChanged) {
        try {
          $event->setResult(($event->execute)());
          $event->setCallbackRetryExecutionState(TRUE);
        }
        catch (\Exception $e) {
          $event->setCallbackRetryExecutionState($e);
          return;
        }
      }
    }
  }

  /**
   * Processes the schema creating the missing tables.
   *
   * @param \Drupal\Core\Database\Event\ExecuteMethodEnsuringSchemaEvent $event
   *   The event to process.
   */
  protected function processSchema(ExecuteMethodEnsuringSchemaEvent $event): bool {
    $schemaChanged = FALSE;
    foreach ($event->schema as $name => $definition) {
      if ($this->connection->schema()->tableExists($name)) {
        continue;
      }
      try {
        $this->connection->schema()->createTable($name, $definition);
        $schemaChanged = TRUE;
      }
      // In a race condition, if another process has already created the
      // table, attempting to create it will throw an exception. In this
      // case just assume the schema was changed here.
      catch (DatabaseException $e) {
        if (!$this->connection->schema()->tableExists($name)) {
          $exception = new SchemaObjectCreationFailureException(sprintf('Failed creation of table {%s}', $name), 0, $e);
          $event->setSchemaCreationState($exception);
          return FALSE;
        }
        $schemaChanged = TRUE;
      }
    }
    return $schemaChanged;
  }

}
