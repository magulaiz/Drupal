<?php

declare(strict_types=1);

namespace Drupal\Core\Hook;

use Drupal\Core\Hook\OrderOperation\BeforeOrAfterIdentifier;
use Drupal\Core\Hook\OrderOperation\BeforeOrAfterModule;

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
  public function getOperations(string $identifier): array {
    $operations = [];
    foreach ($this->modules as $module) {
      $operations[] = new BeforeOrAfterModule(
        $identifier,
        $module,
        $this->isAfter(),
      );
    }
    foreach ($this->classesAndMethods as [$class, $method]) {
      $operations[] = new BeforeOrAfterIdentifier(
        $identifier,
        $class . '::' . $method,
        $this->isAfter(),
      );
    }
    return $operations;
  }

}
