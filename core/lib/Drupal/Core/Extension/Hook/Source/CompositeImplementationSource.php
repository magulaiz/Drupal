<?php

declare(strict_types = 1);

namespace Drupal\Core\Extension\Hook\Source;

use Psr\Container\ContainerInterface;

/**
 * Hook implementation source that combines multiple sources.
 *
 * This allows contrib modules to provide additional sources.
 */
class CompositeImplementationSource implements ImplementationSourceInterface {

  /**
   * Constructor.
   *
   * @param \Psr\Container\ContainerInterface $container
   *   Container.
   * @param list<string> $serviceIds
   *   Service ids for different implementation sources.
   */
  public function __construct(
    private readonly ContainerInterface $container,
    private readonly array $serviceIds,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function getImplementations(): array {
    $sources = array_map($this->container->get(...), $this->serviceIds);
    $lists = [];
    foreach ($sources as $source) {
      assert($source instanceof ImplementationSourceInterface);
      $lists[] = $source->getImplementations();
    }
    return array_merge(...$lists);
  }

}
