<?php

declare(strict_types=1);

namespace Drupal\Core\EventDispatcher;

enum EventDispatcherFactoryStage {

  case PreBootstrap;
  case BootstrapContainer;
  case FullContainer;

}
