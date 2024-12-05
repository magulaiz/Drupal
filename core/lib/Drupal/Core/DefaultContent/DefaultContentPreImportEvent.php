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
   * Constructs a DefaultContentPreImportEvent object.
   *
   * @param \Drupal\Core\DefaultContent\Finder $finder
   *   The content finder, which has information on the entities to create
   *   in the necessary dependency order.
   */
  public function __construct(public readonly Finder $finder) {
  }

}
