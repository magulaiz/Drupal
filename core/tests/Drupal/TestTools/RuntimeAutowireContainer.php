<?php

declare(strict_types = 1);

namespace Drupal\TestTools;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\AutowiringFailedException;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Container that is useful during tests.
 */
class RuntimeAutowireContainer implements ContainerInterface {

  /**
   * Service factories.
   *
   * @var array<class-string, \Closure(self): object>
   */
  private array $serviceFactories = [];

  /**
   * Services.
   *
   * @var array<class-string, object>
   */
  private array $services = [];

  /**
   * Parameter callbacks.
   *
   * Storing callbacks instead of values allows to manage the parameters outside
   * the container.
   *
   * @var array<string, \Closure(self): \UnitEnum|float|array|bool|int|string|null>
   */
  private array $parameterCallbacks = [];

  /**
   * Constructor.
   */
  public function __construct() {
    $this->serviceFactories[static::class] = static fn (self $container) => $container;
    $this->addAliases(static::class);
  }

  /**
   * Adds or sets multiple parameters.
   *
   * @param array<string, \UnitEnum|float|array|bool|int|string|null> $values
   *   Parameter values.
   */
  public function addParameters(array $values): void {
    foreach ($values as $name => $value) {
      $this->setParameter($name, $value);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setParameter(string $name, \UnitEnum|float|array|bool|int|string|null $value): void {
    $this->parameterCallbacks[$name] = static fn () => $value;
  }

  /**
   * Sets a parameter callback.
   *
   * @param string $name
   *   Parameter name.
   * @param \Closure $callback
   *   Callback.
   *
   * @phpstan-param (\Closure(): \UnitEnum|float|array|bool|int|string|null) $callback
   */
  public function setParameterCallback(string $name, \Closure $callback): void {
    $this->parameterCallbacks[$name] = $callback;
  }

  /**
   * Adds a parameter alias.
   */
  public function addParameterAlias(string $alias, string $name): void {
    $this->parameterCallbacks[$alias] = static fn (self $container) => ($container->parameterCallbacks[$name] ?? throw new \Exception())();
  }

  /**
   * Gets a parameter value.
   */
  public function getParameter(string $name): array|bool|string|int|float|\UnitEnum|null {
    return ($this->parameterCallbacks[$name] ?? throw new ParameterNotFoundException($name))($this);
  }

  /**
   * Checks whether a parameter exists.
   */
  public function hasParameter(string $name): bool {
    return isset($this->parameterCallbacks[$name]);
  }

  /**
   * Adds a service object.
   *
   * This can still be replaced later.
   *
   * @param object $service
   *   Service object.
   *   The class will be used as the id.
   */
  public function addService(object $service): void {
    $class = get_class($service);
    $this->doAddFactory($class, static fn () => $service);
    $this->addAliases($class);
  }

  /**
   * Registers multiple classes as services.
   *
   * @param list<class-string> $classes
   *   Class names.
   */
  public function addClasses(array $classes): void {
    foreach ($classes as $class) {
      $this->addClass($class);
    }
  }

  /**
   * Registers a class as a service.
   *
   * @param class-string $class
   *   Class name.
   * @param array $args
   *   Fixed arguments, to be mixed with autowire arguments.
   */
  public function addClass(string $class, array $args = []): void {
    $this->doAddFactory(
      $class,
      static fn (self $container) => $container->instantiateClass($class, $args),
    );
    $this->addAliases($class);
  }

  /**
   * Registers a class as a decorator.
   *
   * @param class-string $class
   *   Class name.
   * @param array $args
   *   Fixed arguments, to be mixed with autowire arguments.
   * @param int $param_index
   *   Index of the decorator parameter. Defaults to 0.
   *
   * @throws \ReflectionException
   *   Class does not exist.
   */
  public function addDecoratorClass(string $class, array $args = [], int $param_index = 0): void {
    $rc = (new \ReflectionClass($class))->getConstructor();
    assert($rc !== NULL, "Decorator class $class has a constructor.");
    $param = $rc->getParameters()[$param_index] ?? NULL;
    assert($param !== NULL, "Decorator class $class has a constructor parameter $param_index.");
    $type = $param->getType();
    assert($type instanceof \ReflectionNamedType && !$type->isBuiltin(), "Decorated parameter has a class-like type $param.");
    $param_class = $type->getName();
    assert(is_a($class, $param_class, TRUE), "The decorator type $class is a subtype of the decorated type $param_class.");
    $param_factory = $this->serviceFactories[$param_class] ?? NULL;
    assert($param_factory !== NULL, "The decorated service for `$param_class` was already registered.");
    $this->doAddFactory(
      $class,
      static function (self $container) use ($class, $args, $param_factory, $param_index) {
        $args = [$param_index => $param_factory($container)] + $args;
        return $container->instantiateClass($class, $args);
      },
    );
    $this->addAliases($class);
  }

  /**
   * Instantiates a class using autowire parameter mapping.
   *
   * @param class-string $class
   *   Class name.
   * @param array $args
   *   Fixed constructor arguments, to be mixed with autowire arguments.
   *
   * @return object
   *   New instance.
   *
   * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
   *   Class does not exist.
   */
  private function instantiateClass(string $class, array $args): object {
    try {
      $rc = new \ReflectionClass($class);
    }
    catch (\ReflectionException $e) {
      throw new ServiceNotFoundException($class, msg: sprintf('Class %s does not exist, or cannot be loaded. Message: %s', $class, $e->getMessage()));
    }
    $params = $rc->getConstructor()?->getParameters() ?? [];
    $args = $this->paramsGetArgs($params, $class, $args);
    return new $class(...$args);
  }

  /**
   * Adds a factory.
   *
   * @param \Closure $factory
   *   Factory. The return type will be used as id, if provided.
   * @param array $args
   *   Fixed arguments, to be mixed with autowire arguments.
   * @param string|null $id
   *   (optional) Id for the service, if different from the return type.
   */
  public function addFactory(\Closure $factory, array $args = [], string $id = NULL): void {
    $rf = new \ReflectionFunction($factory);
    if ($id === NULL) {
      $type = $rf->getReturnType();
      assert($type instanceof \ReflectionNamedType);
      assert(!$type->isBuiltin());
      assert(!$type->allowsNull());
      $id = $type->getName();
      if ($id === 'self' || $id === 'static') {
        $id = $rf->getClosureScopeClass()->getName();
      }
    }
    $this->doAddFactory(
      $id,
      static fn (self $container) => $container->invokeFactory($factory, $args, $id),
    );
    $this->addAliases($id);
  }

  /**
   * Adds a factory.
   *
   * @param class-string $id
   *   Id.
   * @param \Closure $factory
   *   Factory.
   */
  private function doAddFactory(string $id, \Closure $factory): void {
    if (isset($this->services[$id])) {
      throw new \RuntimeException("Service $id was already instantiated.");
    }
    $this->serviceFactories[$id] = $factory;
  }

  /**
   * Invokes a factory callback with autowire arguments.
   *
   * @param \Closure $factory
   *   Factory callback.
   * @param array $args
   *   Fixed arguments, to be mixed with autowire arguments.
   * @param string|null $id
   *   (optional) Id for the service, if different from the return type.
   *
   * @return object
   *   New instance.
   */
  private function invokeFactory(\Closure $factory, array $args, string $id = NULL): object {
    $rf = new \ReflectionFunction($factory);
    $params = $rf->getParameters();
    $args = $this->paramsGetArgs($params, $id, $args);
    $return = $factory(...$args);
    if (!is_object($return)) {
      throw new ServiceNotFoundException($id, msg: sprintf('Factory callback returned %s instead of an object.', get_debug_type($return)));
    }
    return $return;
  }

  /**
   * Registers a mock service.
   *
   * @param class-string $class
   *   Class name, also used as service id.
   * @param list<string>|null $methods
   *   Methods to mock, or NULL for all methods.
   * @param array $args
   *   Fixed arguments to mix with autowire arguments.
   */
  public function addMock(string $class, array $methods = NULL, array $args = []): void {
    $this->doAddFactory(
      $class,
      static fn(self $container) => $container->createMock($class, $methods, $args),
    );
    $this->addAliases($class);
  }

  /**
   * Adds a mock service and gets the instance.
   *
   * @phpstan-template T
   *
   * @param class-string $class
   *   Class name, also used as service id.
   * @param list<string>|null $methods
   *   Methods to mock, or NULL for all methods.
   * @param array $args
   *   Fixed arguments to mix with autowire arguments.
   *
   * @return object
   *   New mock instance.
   *
   * @phpstan-param class-string<T> $class
   *
   * @phpstan-return T&MockObject
   */
  public function getMock(string $class, array $methods = NULL, array $args = []): object {
    $this->addMock($class, $methods, $args);
    return $this->get($class);
  }

  /**
   * Creates a mock object.
   *
   * @phpstan-template T
   *
   * @param class-string $class
   *   Class name.
   * @param list<string>|null $methods
   *   Methods to mock, or NULL for all methods.
   * @param array $args
   *   Fixed arguments to mix with autowire arguments.
   *
   * @return \PHPUnit\Framework\MockObject\MockObject
   *   New mock object.
   *
   * @phpstan-param class-string<T> $class
   *
   * @phpstan-return T&MockObject
   */
  private function createMock(string $class, ?array $methods, array $args): MockObject {
    $test_case = $this->getOrNull(TestCase::class);
    if ($test_case === NULL) {
      throw new \RuntimeException('To add a mock, first inject the test case as a service.');
    }
    $builder = $test_case->getMockBuilder($class);
    $rc = new \ReflectionClass($class);
    $params = $rc->getConstructor()?->getParameters() ?? [];
    $args = $this->paramsGetArgs($params, $class, $args);
    $builder->setConstructorArgs($args);
    if ($methods !== NULL) {
      $builder->onlyMethods($methods);
    }
    return $builder->getMock();
  }

  /**
   * Finds argument values for autowire parameters.
   *
   * @param list<\ReflectionParameter> $params
   *   Parameters.
   * @param string $id
   *   Service id.
   * @param array $fixed_args
   *   Fixed arguments, to be mixed with autowire arguments.
   *
   * @return list<mixed>
   *   Argument values.
   */
  private function paramsGetArgs(array $params, string $id, array $fixed_args): array {
    return array_map(function (\ReflectionParameter $param) use ($id, $fixed_args): mixed {
      if (array_key_exists($param->getPosition(), $fixed_args)) {
        return $fixed_args[$param->getPosition()];
      }
      if (array_key_exists($param->name, $fixed_args)) {
        return $fixed_args[$param->name];
      }
      $type = $param->getType();
      if ($type instanceof \ReflectionNamedType) {
        if (!$type->isBuiltin()) {
          return $this->get($type->getName());
        }
        else {
          $argument_name = $type->getName() . ' $' . $param->name;
          $argument_factory = $this->parameterCallbacks[$argument_name] ?? NULL;
          if ($argument_factory !== NULL) {
            return $argument_factory($this);
          }
        }
      }
      if ($param->isOptional()) {
        return $param->getDefaultValue();
      }
      throw new AutowiringFailedException($id, "Cannot get value for parameter $param for service $id.");
    }, $params);
  }

  /**
   * Adds aliases based on class interfaces and parents.
   *
   * @param class-string $class
   *   Class name.
   */
  private function addAliases(string $class): void {
    $rc = new \ReflectionClass($class);
    foreach ($rc->getInterfaceNames() as $interfaceName) {
      $this->doAddFactory(
        $interfaceName,
        static fn (self $container) => $container->get($class),
      );
    }
    while ($rc = $rc->getParentClass()) {
      $this->doAddFactory(
        $rc->name,
        static fn (self $container) => $container->get($class),
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function has(string $id): bool {
    return isset($this->serviceFactories[$id]);
  }

  /**
   * Gets a service by id.
   *
   * @phpstan-template T
   *
   * @param string $id
   *   Service id. For this container, ids are classes and interfaces.
   * @param int $invalidBehavior
   *   What to do if the service does not exist.
   *
   * @return object|null
   *   The service, or NULL if not found and invalid behavior allows NULL.
   *
   * @phpstan-param class-string<T> $id
   *
   * @phpstan-return T
   */
  public function get(string $id, int $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE): ?object {
    return $this->services[$id]
      ??= ($this->serviceFactories[$id] ?? $this->miss($id, $invalidBehavior))
      ?->__invoke($this);
  }

  /**
   * Gets a service, if it exists.
   *
   * @phpstan-template T
   *
   * @param string $id
   *   Service id. For this container, ids are classes and interfaces.
   *
   * @return object|null
   *   Service, or NULL if it does not exist.
   *
   * @phpstan-param class-string<T> $id
   *
   * @phpstan-return T|null
   */
  public function getOrNull(string $id): ?object {
    return $this->services[$id]
      ??= ($this->serviceFactories[$id] ?? NULL)
      ?->__invoke($this);
  }

  /**
   * Handles a request for a missing service.
   *
   * @param string $id
   *   Service id.
   * @param int $invalidBehavior
   *   Invalid behavior.
   *
   * @return null
   *   This always returns NULL, if no exception is thrown.
   *
   * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
   */
  private function miss(string $id, int $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE) {
    if ($invalidBehavior === self::NULL_ON_INVALID_REFERENCE) {
      return NULL;
    }
    if ($invalidBehavior === self::EXCEPTION_ON_INVALID_REFERENCE) {
      throw new ServiceNotFoundException($id);
    }
    throw new \RuntimeException("Unsupported invalid behavior mode for service $id.");
  }

  /**
   * Sets a service object.
   *
   * This is not supported, because it would allow to use a service id that is
   * different from the class name.
   *
   * @param string $id
   *   Service id. In this container, the ids are classes.
   * @param object|null $service
   *   Service object to register.
   *
   * @return never
   *   Unsupported.
   *
   * @see self::addService()
   */
  public function set(string $id, ?object $service): never {
    throw new \RuntimeException('Unsupported operation. Use ->addService() instead.');
  }

  /**
   * Checks whether a service is initialized.
   *
   * @param string $id
   *   Service id.
   *
   * @return bool
   *   TRUE if initialized.
   */
  public function initialized(string $id): bool {
    return isset($this->services[$id]);
  }

  /**
   * Resets all services.
   */
  public function reset(): void {
    $this->services = [];
  }

}
