<?php

declare(strict_types=1);

namespace Drupal\Core\Form;

/**
 * Enumeration of the special possible return values for toConfig callables.
 */
enum ToConfig {

  // Appropriate to return from a toConfig callable when another toConfig
  // callable handles setting this property path. In other words: "no-op".
  case NoOp;

  // Appropriate to return from a toConfig callable when the given form value
  // should result in the targeted property path getting deleted.
  // @see \Drupal\Core\Config\Config::clear()
  case DeleteKey;
}
