<?php

declare(strict_types=1);

namespace Drupal\Core\Recipe;

use Drupal\Component\Assertion\Inspector;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Collects default input values for a recipe and all of its dependencies.
 *
 * @internal
 *   This API is experimental.
 */
final class DefaultValueResolver extends InputCollectorBase implements ContainerInjectionInterface {

  public function __construct(
    private readonly ConfigFactoryInterface $configFactory,
    TypedDataManagerInterface $typedDataManager,
  ) {
    parent::__construct($typedDataManager);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get(ConfigFactoryInterface::class),
      $container->get(TypedDataManagerInterface::class),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function collectValue(string $name, array $definition): mixed {
    if ($definition['default']['source'] === 'config') {
      [$name, $key] = $definition['default']['config'];
      return $this->configFactory->get($name)->get($key);
    }
    return $definition['default']['value'];
  }

}
