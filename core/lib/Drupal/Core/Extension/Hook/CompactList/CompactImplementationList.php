<?php

namespace Drupal\Core\Extension\Hook\CompactList;

use Drupal\Core\Extension\Hook\Builder\ImplementationListBuilder;
use Drupal\Core\Extension\Hook\CallbackList\HookImplementationCallbackList;
use Drupal\Core\Extension\Hook\SingleModuleCallbackList\SingleModuleCallbackList;
use Drupal\Core\Extension\Hook\SingleModuleCallbackList\SingleModuleCallbackListEmpty;
use Drupal\Core\Extension\Hook\SingleModuleCallbackList\SingleModuleCallbackListInterface;
use Drupal\Core\Extension\Hook\SingleModuleCallbackList\SingleModuleCallbackListSingle;
use Drupal\Core\Extension\ModuleLoaderInterface;
use Psr\Container\ContainerInterface;

/**
 * Compact list allowing a mix of different implementation types.
 */
class CompactImplementationList implements CompactImplementationListInterface {

  /**
   * Module names.
   *
   * @var list<string>
   */
  private array $modules = [];

  /**
   * Callback stubs.
   *
   * @var list<callable-string|array{class-string|null, string}&callable>
   */
  private array $callbackStubs = [];

  /**
   * Service ids for those implementations that are service methods.
   *
   * @var array<int, string>
   */
  private array $serviceIds = [];

  /**
   * Constructor.
   *
   * @param string $hook
   *   Hook name.
   */
  public function __construct(
    private readonly string $hook,
  ) {}

  /**
   * Starts a builder.
   *
   * @param string $hook
   *   Hook name.
   *
   * @return \Drupal\Core\Extension\Hook\Builder\ImplementationListBuilder
   */
  public static function build(string $hook): ImplementationListBuilder {
    return new ImplementationListBuilder($hook);
  }

  /**
   * Creates an empty list.
   *
   * @return \Drupal\Core\Extension\Hook\CompactList\CompactImplementationListInterface
   *   New empty list.
   */
  public static function createEmpty(): CompactImplementationListInterface {
    return new CompactImplementationListEmpty();
  }

  /**
   * Gets an optimized version of this list.
   *
   * @return \Drupal\Core\Extension\Hook\CompactList\CompactImplementationListInterface
   *   Optimized version.
   */
  public function optimize(): CompactImplementationListInterface {
    if (!$this->callbackStubs) {
      return new CompactImplementationListEmpty();
    }
    return $this;
  }

  /**
   * Adds an implementation.
   *
   * @param string $module
   *   Module name.
   * @param callable-string|null $function
   *   Function name, for procedural implementations.
   * @param string|null $service
   *   Service id, for service methods.
   * @param class-string|null $class
   *   Class name, for static methods.
   * @param string|null $method
   *   Method name, for service methods or static methods.
   */
  public function add(
    string $module,
    ?string $function = NULL,
    ?string $service = NULL,
    ?string $class = NULL,
    ?string $method = NULL,
  ) {
    $delta = count($this->callbackStubs);
    $this->modules[] = $module;
    if ($function !== NULL) {
      $this->callbackStubs[] = $function;
    }
    elseif ($service !== NULL) {
      assert($method !== NULL);
      $this->serviceIds[$delta] = $service;
      $this->callbackStubs[] = [NULL, $method];
    }
    elseif ($class !== NULL) {
      assert($method !== NULL);
      $this->callbackStubs[] = [$class, $method];
    }
    else {
      throw new \RuntimeException('Unexpected implementation info.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function hasImplementations(string|array $modules = NULL): bool {
    if ($modules === NULL) {
      return $this->modules !== [];
    }
    if (is_string($modules)) {
      return in_array($modules, $this->modules, TRUE);
    }
    return (bool) array_intersect($modules, $this->modules);
  }

  /**
   * {@inheritdoc}
   */
  public function buildSingleModuleCallbackList(
    string $module,
    ContainerInterface $container,
    ModuleLoaderInterface $module_loader,
    \Closure $invalidate,
  ): SingleModuleCallbackListInterface {
    if (!$this->callbackStubs) {
      return new SingleModuleCallbackListEmpty();
    }
    $ids = array_keys($this->modules, $module, TRUE);
    if (!$ids) {
      return new SingleModuleCallbackListEmpty();
    }
    $function = $module . '_' . $this->hook;
    if (count($ids) === 1
      && $this->callbackStubs[$ids[0]] === $function
      && function_exists($function)
    ) {
      return new SingleModuleCallbackListSingle($function(...));
    }
    // Optimize for the case where all services exist, and all implementations
    // are callable.
    try {
      $callbacks = [];
      $contains_main_function = FALSE;
      foreach ($ids as $id) {
        $implementation = $this->callbackStubs[$id];
        if ($implementation === $function) {
          $contains_main_function = TRUE;
          $callbacks[] = $implementation(...);
        }
        elseif ($service_id = $this->serviceIds[$id] ?? NULL) {
          // Service method.
          $callbacks[] = $container->get($service_id)->{$implementation[1]}(...);
        }
        else {
          // Static method, or another function.
          $callbacks[] = $implementation(...);
        }
      }
    }
    catch (\Throwable) {
      // Do it again with separate try/catch for each item.
      // @todo Profile the difference of separate try/catch compared to a
      //   single try/catch.
      $callbacks = [];
      $contains_main_function = FALSE;
      $bad_ids = [];
      foreach ($ids as $id) {
        $implementation = $this->callbackStubs[$id];
        try {
          if ($implementation === $function) {
            $contains_main_function = TRUE;
            $callbacks[] = $implementation(...);
          }
          elseif ($service_id = $this->serviceIds[$id] ?? NULL) {
            // Service method.
            $callbacks[] = $container->get($service_id)->{$implementation[1]}(...);
          }
          else {
            // Static method, or another function.
            $callbacks[] = $implementation(...);
          }
        }
        catch (\Throwable) {
          $bad_ids[] = $id;
        }
      }
      $this->removeIds($bad_ids);
      $invalidate();
    }
    return new SingleModuleCallbackList($callbacks, $contains_main_function);
  }

  /**
   * {@inheritdoc}
   */
  public function buildCallbackList(
    ContainerInterface $container,
    ModuleLoaderInterface $module_loader,
    \Closure $invalidate,
  ): HookImplementationCallbackList {
    if (!$this->callbackStubs) {
      return new HookImplementationCallbackList([], []);
    }
    // Use a big try/catch to optimize for a case where all services exist, and
    // all implementations are callable.
    $implementations = $this->callbackStubs;
    try {
      foreach ($this->serviceIds as $implementation_id => $service_id) {
        $implementations[$implementation_id][0] = $container->get($service_id);
      }
      $callbacks = array_map(\Closure::fromCallable(...), $implementations);
    }
    // @todo More precise exception catching.
    catch (\Throwable) {
      // One of the services or functions no longer exists.
      $bad_ids = [];
      foreach ($this->serviceIds as $id => $service_id) {
        try {
          $implementations[$id][0] = $container->get($service_id);
        }
        catch (\Throwable) {
          $bad_ids[$id] = $id;
        }
      }
      $callbacks = [];
      foreach (array_diff_key($implementations, $bad_ids) as $id => $callback) {
        try {
          $callbacks[] = $callback(...);
        }
        catch (\Throwable) {
          $bad_ids[$id] = $id;
        }
      }
      $this->removeIds($bad_ids);
      $invalidate();
    }
    return new HookImplementationCallbackList($callbacks, $this->modules);
  }

  /**
   * Removes implementations from the list.
   *
   * @param list<int> $bad_ids
   *   List of indices to remove.
   */
  private function removeIds(array $bad_ids): void {
    // Fill up $this->serviceIds with NULL values in correct order.
    $this->serviceIds = array_replace(array_fill_keys(array_keys($this->callbackStubs), NULL), $this->serviceIds);
    foreach ($bad_ids as $bad_id) {
      unset(
        $this->serviceIds[$bad_id],
        $this->callbackStubs[$bad_id],
        $this->modules[$bad_id],
      );
    }
    $this->serviceIds = array_filter(array_values($this->serviceIds));
    $this->callbackStubs = array_values($this->callbackStubs);
    $this->modules = array_values($this->modules);
  }

  /**
   * {@inheritdoc}
   */
  public function removeBadImplementations(ContainerInterface $container): void {
    $implementations = $this->callbackStubs;
    foreach ($this->serviceIds as $implementation_id => $service_id) {
      try {
        $implementations[$implementation_id][0] = $container->get($service_id);
      }
      catch (\Throwable) {
        unset(
          $this->serviceIds[$implementation_id],
          $this->callbackStubs[$implementation_id],
        );
      }
    }
    foreach ($implementations as $implementation_id => $implementation) {
      if (!is_callable($implementation)) {
        unset(
          $this->serviceIds[$implementation_id],
          $this->callbackStubs[$implementation_id],
        );
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getPrintableNames(bool $prepend_module = TRUE): array {
    $list = [];
    foreach ($this->callbackStubs as $delta => $implementation) {
      if (is_string($implementation)) {
        // Function.
        $printable = $implementation;
      }
      elseif ($service_id = $this->serviceIds[$delta] ?? NULL) {
        // Service method.
        $printable = '@' . $service_id . '->' . $implementation[1];
      }
      else {
        // Static method.
        $printable = $implementation[0] . '::' . $implementation[1];
      }
      if ($prepend_module) {
        $printable = $this->modules[$delta] . ': ' . $printable;
      }
      $list[] = $printable;
    }
    assert(array_keys($list) === array_keys($this->callbackStubs));
    return $list;
  }

}
