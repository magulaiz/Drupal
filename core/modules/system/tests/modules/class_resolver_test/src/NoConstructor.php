<?php

declare(strict_types=1);

namespace Drupal\class_resolver_test;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a class for testing the creation of objects no constructor.
 */
final class NoConstructor implements ContainerAwareInterface {

  /**
   * The service container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface|null
   */
  protected ?ContainerInterface $container;

  /**
   * {@inheritdoc}
   */
  public function setContainer(?ContainerInterface $container): void {
    $this->container = $container;
  }

}
