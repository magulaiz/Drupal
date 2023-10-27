<?php

namespace Drupal\dblog;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\Config\BootstrapConfigStorageFactory;

class DblogServiceProvider extends ServiceProviderBase {

  public function register(ContainerBuilder $container) {

    $config_storage = BootstrapConfigStorageFactory::get();
    $dblog_config = $config_storage->read('dblog.settings');
    if ($dblog_config['logging_paused']) {
      return;
    }
    $container->register('dblog.logger', 'Drupal\dblog\Logger\DbLog');
  }
}
