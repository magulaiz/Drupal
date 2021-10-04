<?php

declare(strict_types=1);

namespace Drupal\system;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Drupal\Core\StackMiddleware\NegotiationMiddleware as CoreNegotiationMiddleware;
use Drupal\system\StackMiddleware\NegotiationMiddleware;

/**
 * Changes the HTTP negotiation middleware service's class.
 *
 * The overriding class receives a service parameter which enables sites to set
 * an alternative default format per host name.
 *
 * @see \Drupal\system\StackMiddleware\NegotiationMiddleware
 */
final class MenuServiceProvider implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    if ($container->has('http_middleware.negotiation') && is_a($container->getDefinition('http_middleware.negotiation')->getClass(), CoreNegotiationMiddleware::class, TRUE)) {
      $definition = $container->getDefinition('http_middleware.negotiation');
      $definition->setClass(NegotiationMiddleware::class);
      $definition->addMethodCall('setDefaultHostFormats', ['%host.default_formats%']);
    }
  }

}
