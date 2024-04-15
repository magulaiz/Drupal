<?php

namespace Drupal\Component\EventDispatcher;

use Symfony\Contracts\EventDispatcher\Event as SymfonyEvent;

trigger_error(__NAMESPACE__ . '\Event is deprecated in drupal:10.3.0 and is removed from drupal:11.0.0. Use \Symfony\Contracts\EventDispatcher\Event instead. See https://www.drupal.org/node/3440958', E_USER_DEPRECATED);

/**
 * Provides a forward-compatibility layer for the Symfony 5 event class.
 *
 * Symfony 5 relies on the Symfony\Contracts\EventDispatcher\Event class.
 * In order to prepare for updates, code that wishes to extend Symfony's Event
 * class should extend this intermediary class, which will handle switching
 * from Symfony\Component to Symfony\Contracts without a further change.
 *
 * @deprecated in drupal:10.3.0 and is removed from drupal:11.0.0. Use
 *   \Symfony\Contracts\EventDispatcher\Event instead.
 *
 * @see https://www.drupal.org/node/3440958
 */
class Event extends SymfonyEvent {}
