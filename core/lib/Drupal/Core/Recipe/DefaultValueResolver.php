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
    $default = $definition['default'] ?? NULL;

    if (is_array($default) && $default['source'] === 'config') {
      [$name, $key] = $default['config'];
      return $this->configFactory->get($name)->get($key);
    }
    return $default;
  }

  public static function validateDefinition(mixed $definition, ExecutionContextInterface $context): void {
    if (is_array($definition) && array_key_exists('source', $definition) && $definition['source'] === 'config') {
      $is_valid = function ($definition): bool {
        return (
          array_key_exists('config', $definition) &&
          is_array($definition['config']) &&
          count($definition['config']) === 2 &&
          array_is_list($definition['config']) &&
          Inspector::assertAllStrings($definition['config']) &&
          Inspector::assertAllNotEmpty($definition['config'])
        );
      };
      if ($is_valid($definition) === FALSE) {
        $context->addViolation('Default values from config must have a "config" array with exactly two elements: the name of a config object, and a property path.');
      }
    }
  }

}
