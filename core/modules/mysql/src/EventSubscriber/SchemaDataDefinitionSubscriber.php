<?php

namespace Drupal\mysql\EventSubscriber;

use Drupal\Core\Database\Event\SchemaIndexDefinitionEvent;
use Drupal\Core\Database\Event\SchemaKeyColumnDefinitionEvent;
use Drupal\Core\Database\Event\SchemaPrimaryKeyDefinitionEvent;
use Drupal\Core\Database\Schema\KeyColumn;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Response subscriber to schema data definition events.
 */
class SchemaDataDefinitionSubscriber implements EventSubscriberInterface {

  /**
   * Constructor.
   *
   * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   The event dispatcher.
   */
  public function __construct(
    public readonly EventDispatcherInterface $eventDispatcher,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      SchemaIndexDefinitionEvent::class => 'onIndexDefinition',
      SchemaKeyColumnDefinitionEvent::class => 'onKeyColumnDefinition',
      SchemaPrimaryKeyDefinitionEvent::class => 'onPrimaryKeyDefinition',
    ];
  }

  /**
   * Subscribes to a index definition event.
   *
   * @param \Drupal\Core\Database\Event\SchemaIndexDefinitionEvent $event
   *   The database event.
   */
  public function onIndexDefinition(SchemaIndexDefinitionEvent $event): void {
    $event->index->name = $event->index->definition->name;
    foreach ($event->index->definition->columns as $column) {
      $event->index->columns[] = $this->eventDispatcher->dispatch(new SchemaKeyColumnDefinitionEvent(new KeyColumn($column)))->keyColumn;
    }
  }

  /**
   * Subscribes to a key column definition event.
   *
   * @param \Drupal\Core\Database\Event\SchemaKeyColumnDefinitionEvent $event
   *   The database event.
   */
  public function onKeyColumnDefinition(SchemaKeyColumnDefinitionEvent $event): void {
    $event->keyColumn->name = $event->keyColumn->definition->name;
    $event->keyColumn->length = $event->keyColumn->definition->length;
  }

  /**
   * Subscribes to a primary key definition event.
   *
   * @param \Drupal\Core\Database\Event\SchemaPrimaryKeyDefinitionEvent $event
   *   The database event.
   */
  public function onPrimaryKeyDefinition(SchemaPrimaryKeyDefinitionEvent $event): void {
    foreach ($event->primaryKey->definition->columns as $column) {
      $event->primaryKey->columns[] = $this->eventDispatcher->dispatch(new SchemaKeyColumnDefinitionEvent(new KeyColumn($column)))->keyColumn;
    }
  }

}
