<?php

declare(strict_types=1);

namespace Drupal\user;

use Psr\Container\ContainerInterface;

final class PermissionProvidersLocator {

  /**
   * Constructs a new PermissionProvidersLocator.
   *
   * @param array{string, array{methods: string[], provider: string}} $permissionProvidersMapping
   *   Configuration for permission providers keyed by anonymous service locator
   *   ID. Configuration contains at least one method, with the provider
   *   string matching the module name.
   * @param \Psr\Container\ContainerInterface $permissionProvidersLocator
   *   Permission provider service locator.
   */
  public function __construct(
    private readonly array $permissionProvidersMapping,
    private readonly ContainerInterface $permissionProvidersLocator,
  ) {}

  /**
   * Get permission providers.
   *
   * @return \Generator<array{string, callable}>
   *   Yields an array contain with a provider and a callable from a service
   *   method.
   */
  public function getPermissionProviders(): \Generator {
    foreach ($this->permissionProvidersMapping as $serviceId => $configuration) {
      ['methods' => $methods, 'provider' => $provider] = $configuration;
      $permissionProvider = $this->permissionProvidersLocator->get($serviceId);
      foreach ($methods as $method) {
        yield [$provider, \Closure::fromCallable([$permissionProvider, $method])];
      }
    }
  }

}
