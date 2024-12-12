<?php

declare(strict_types=1);

namespace Drupal\Core\EventDispatcher;

enum EventDispatcherFactoryStage: string {

  case PreBootstrap = 'PreBootstrap';
  case BootstrapContainer = 'BootstrapContainer';
  case FullContainer = 'FullContainer';

}
