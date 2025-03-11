<?php

declare(strict_types=1);

namespace Drupal\Core\Hook;

use Drupal\Core\Hook\OrderOperation\OrderOperationInterface;
use Drupal\Core\Hook\OrderOperation\RelativeOrderOperation;

/**
 * Orders an implementation relative to other implementations.
 */
abstract readonly class RelativeOrderBase implements OrderInterface {

  /**
   * Constructor.
   *
   * @param list<string> $modules
   *   A list of modules the implementations of which to order against.
   * @param list<array{class-string, string}> $classesAndMethods
   *   A list of implementations to order against, as [$class, $method].
   */
  public function __construct(
    public array $modules = [],
    public array $classesAndMethods = [],
    public array $extraTypes = [],
  ) {
    if (!$this->modules && !$this->classesAndMethods) {
      throw new \LogicException('Order must provide either modules or class-method pairs to order against.');
    }
  }

  /**
   * Specifies the ordering direction.
   *
   * @return bool
   *   TRUE, if the ordered implementation should be inserted _after_ the
   *   implementations specified in the constructor.
   */
  abstract protected function isAfter(): bool;

  /**
   * {@inheritdoc}
   */
  public function getOperation(string $identifier): OrderOperationInterface {
    return new RelativeOrderOperation(
      $identifier,
      $this->modules,
      array_map(
        fn(array $class_and_method) => implode('::', $class_and_method),
        $this->classesAndMethods,
      ),
      $this->isAfter(),
    );
  }

}
