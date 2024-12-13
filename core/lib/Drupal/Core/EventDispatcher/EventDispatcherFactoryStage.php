<?php

declare(strict_types=1);

namespace Drupal\Core\EventDispatcher;

/**
 * Enumeration of the possible creation stages of the event dispatcher.
 */
enum EventDispatcherFactoryStage: string {

  // Indicates creation happened before or during kernel bootstrap, while no
  // container is fully complied yet. This is the fallback case while the
  // container is not ready.
  case PreBootstrap = 'PreBootstrap';

  // Indicates creation happened during kernel bootstrap, and is part of the
  // bootstrap container.
  case BootstrapContainer = 'BootstrapContainer';

  // Indicates creation happened in the full container. This is the normal
  // case.
  case FullContainer = 'FullContainer';

}
