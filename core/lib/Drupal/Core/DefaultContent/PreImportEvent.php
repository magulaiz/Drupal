<?php

declare(strict_types=1);

namespace Drupal\Core\DefaultContent;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched before default content is imported.
 *
 * Subscribers to this event should avoid modifying content, because it is
 * probably about to change again. This event is best used for tasks like
 * notifications, logging, or updating a value in state. It can also be used
 * to skip importing certain entities, identified by their UUID.
 */
final class PreImportEvent extends Event {

  /**
   * Entity UUIDs that should not be imported.
   *
   * @var string[]
   */
  private array $skip = [];

  /**
   * Constructs a PreImportEvent object.
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
   *
   * @throws \InvalidArgumentException
   *   If the given UUID is not one of the ones being imported.
   */
  public function skip(string $uuid): void {
    if (array_key_exists($uuid, $this->finder->data)) {
      $this->skip[] = $uuid;
    }
    else {
      throw new \InvalidArgumentException("Content entity '$uuid' cannot be skipped, because it is not one of the entities being imported.");
    }
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
