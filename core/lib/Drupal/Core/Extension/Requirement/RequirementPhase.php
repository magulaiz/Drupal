<?php

declare(strict_types=1);

namespace Drupal\Core\Extension\Requirement;

/**
 * The requirement phase.
 */
enum RequirementPhase: string {

  // The runtime phase.
  case Runtime = 'runtime';
  // The install phase.
  case Install = 'install';
  // The update phase.
  case Update = 'update';

}
