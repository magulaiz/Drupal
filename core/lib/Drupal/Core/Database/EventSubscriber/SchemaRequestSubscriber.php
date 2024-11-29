<?php

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

  public function onExecuteMethodEnsuringSchema(ExecuteMethodEnsuringSchemaEvent $event): void {
    $callMethod = $event->execute;
    $tryAgain = FALSE;
    try {
      $event->setResult($callMethod());
    }
    catch (\Exception $e) {
      // If there was an exception, try to create the table.
      $event->setResult(FALSE);
      if (!$tryAgain = $this->ensureSchemaExists($event->schema)) {
        // If the exception happened for other reason than the missing table,
        // propagate the exception.
        throw $e;
      }
    }
    // Now that the table has been created, try again if necessary.
    if ($event->retryAfterSchemaEnsured && $tryAgain) {
      $event->setResult($callMethod());
    }
  }

  /**
   * Check if the table exists and create it if not.
   */
  protected function ensureSchemaExists(array $schema): bool {
    try {
      $databaseSchema = $this->connection->schema();
      foreach ($schema as $name => $definition) {
        $databaseSchema->createTable($name, $definition);
      }
    }
    // If another process has already created the batch table, attempting to
    // recreate it will throw an exception. In this case just catch the
    // exception and do nothing.
    catch (DatabaseException) {
    }
    catch (\Exception) {
      return FALSE;
    }
    return TRUE;
  }

}
