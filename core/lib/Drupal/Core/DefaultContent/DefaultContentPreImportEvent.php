<?php

declare(strict_types=1);

namespace Drupal\Core\DefaultContent;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched before default_content content import.
 *
 * Subscribers to this event should avoid modifying content, because
 * content is probably about to change again. This event is best
 * used for tasks like notifications, logging or updating a value in state.
 */
final class DefaultContentPreImportEvent extends Event {

  /**
   * Entity UUIDs that should not be imported.
   *
   * @var string[]
   */
  private array $skip = [];

  /**
   * Constructs a DefaultContentPreImportEvent object.
   *
   * @param \Drupal\Core\DefaultContent\Finder $finder
   *   The content finder, which has information on the entities to create
   *   in the necessary dependency order.
   * @param \Drupal\Core\DefaultContent\Existing $existing
   *   What the importer will do when importing an entity that already exists.
   */
  public function __construct(
    public readonly Finder $finder,
    public readonly Existing $existing,
  ) {}

  /**
   * Adds an entity UUID to the skip list.
   *
   * @param string $uuid
   *   The UUID of an entity that should not be imported.
   */
  public function skip(string $uuid): void {
    $this->skip[] = $uuid;
  }

  /**
   * Returns the list of entity UUIDs that should not be imported.
   *
   * @return string[]
   *   The UUIDs of entities that should not be imported.
   */
  public function getSkipList(): array {
    return array_unique($this->skip);
  }

}
