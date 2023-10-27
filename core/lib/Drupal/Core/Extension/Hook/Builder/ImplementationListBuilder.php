<?php

namespace Drupal\Core\Extension\Hook\Builder;

use Drupal\Core\Extension\Hook\CompactList\CompactImplementationList;
use Drupal\Core\Extension\Hook\CompactList\CompactImplementationListEmpty;
use Drupal\Core\Extension\Hook\CompactList\ModuleIncludeDecorator;
use Drupal\Core\Extension\Hook\CompactList\CompactImplementationListInterface;

/**
 * Builder for a list of implementations.
 *
 * Used during discovery.
 */
class ImplementationListBuilder {

  /**
   * Include file groups.
   *
   * @var array<string, array<string, true>>
   */
  private array $includeFileGroups = [];

  /**
   * Implementations by weight and id.
   *
   * Every id is either a module name, or a key in the before/after map.
   *
   * @var array<int, array<string, list<array{
   *   module: string,
   *   function?: string,
   *   class?: string,
   *   service?: string,
   *   method?: string,
   * }>>>
   */
  private array $implementations = [];

  /**
   * Array with before/after settings.
   *
   * @var array<string, array{bool, array<string, true>}>
   */
  private array $relativePositionMap = [];

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
   * Adds an include file group.
   *
   * This will cause inclusion of "$path_to_module/$module.$group.inc".
   *
   * @param string $module
   *   Module name.
   * @param string $group
   *   Part of the file to be included.
   */
  public function addIncludeFileGroup(string $module, string $group): void {
    $this->includeFileGroups[$module][$group] = TRUE;
  }

  /**
   * Adds a traditional procedural implementation.
   *
   * @param string $module_or_prefix
   *   Module name, or a fake module name to use as prefix.
   * @param string|false $group
   *   Include file group, or FALSE if not.
   *
   * @return $this
   */
  public function addProcedural(string $module_or_prefix, string|false $group = FALSE): static {
    if ($group) {
      $this->includeFileGroups[$module_or_prefix][$group] = TRUE;
    }
    $this->implementations[0][$module_or_prefix][] = [
      'function' => $module_or_prefix . '_' . $this->hook,
      'module' => $module_or_prefix,
    ];
    return $this;
  }

  /**
   * Adds an implementation.
   *
   * @param string $module
   *   Module name.
   * @param int $weight
   *   Weight.
   * @param string|list<string>|null $before
   *   Module name(s) before which the implementation should be inserted.
   *   This cannot be combined with $after.
   * @param string|list<string>|null $after
   *   Module name(s) after which the implementation should be inserted.
   *   This cannot be combined with $before.
   * @param string ...$info
   *   Remaining info.
   */
  public function addImplementation(
    string $module,
    int $weight = 0,
    string|array $before = NULL,
    string|array $after = NULL,
    string ...$info,
  ) {
    // Only run this expensive check if assertions are enabled.
    assert($this->validateInfo(...$info));
    if ($before !== NULL) {
      assert($after === NULL);
      $id = $this->createBeforeAfterId(FALSE, $before);
    }
    elseif ($after !== NULL) {
      $id = $this->createBeforeAfterId(TRUE, $after);
    }
    else {
      $id = $module;
    }
    $info['module'] = $module;
    $this->implementations[$weight][$id][] = $info;
  }

  /**
   * Validates additional values from implementation info.
   *
   * @param string|null $function
   *   Function name.
   * @param string|null $class
   *   Class name.
   * @param string|null $service
   *   Service id.
   * @param string|null $method
   *   Method name.
   * @param mixed ...$args
   *   Additional arguments. This is expected to be empty.
   *
   * @return true
   *   Value to indicate that all is ok.
   */
  private function validateInfo(
    string $function = NULL,
    string $class = NULL,
    string $service = NULL,
    string $method = NULL,
    mixed ...$args,
  ): bool {
    assert(!$args);
    if ($function !== NULL) {
      assert($class === NULL);
      assert($method === NULL);
      assert($service === NULL);
    }
    elseif ($service !== NULL) {
      assert($class === NULL);
      assert($method !== NULL);
    }
    elseif ($class !== NULL) {
      assert($method !== NULL);
    }
    else {
      assert(FALSE);
    }
    return TRUE;
  }

  /**
   * Creates an entry in the before/after map, and returns the array key.
   *
   * @param bool $direction
   *   FALSE for before, TRUE for after.
   * @param string|list<string> $target
   *   Modules(s) that this should be inserted before or after.
   *
   * @return string
   *   New id identifying this position.
   */
  private function createBeforeAfterId(bool $direction, string|array $target): string {
    $id = $direction ? 'after:' : 'before:';
    if (is_string($target)) {
      $id .= $target;
      $target = [$target];
    }
    else {
      $id .= implode(',', $target);
    }
    $this->relativePositionMap[$id] = [$direction, array_fill_keys($target, TRUE)];
    return $id;
  }

  /**
   * Merges another list.
   *
   * This is used for alter calls with multiple hook names.
   *
   * @param \Drupal\Core\Extension\Hook\Builder\ImplementationListBuilder $other
   *   Implementation list for another hook.
   */
  public function merge(ImplementationListBuilder $other): void {
    foreach ($other->includeFileGroups as $module => $groups) {
      foreach ($groups as $group => $true) {
        $this->includeFileGroups[$module][$group] = TRUE;
      }
    }
    foreach ($other->implementations as $weight => $implementations_by_id) {
      foreach ($implementations_by_id as $id => $implementations) {
        foreach ($implementations as $implementation) {
          assert(isset($implementation['module']));
          $this->implementations[$weight][$id][] = $implementation;
        }
      }
    }
    $this->relativePositionMap += $other->relativePositionMap;
  }

  /**
   * Sets the order of implementations based on a module list.
   *
   * @param array<string, int> $module_numbers
   *   Map of module names to increasing numbers.
   */
  public function setOrder(array $module_numbers): void {
    assert(array_flip(array_keys($module_numbers)) === $module_numbers);
    if (!$this->implementations) {
      return;
    }
    if ($this->relativePositionMap) {
      foreach ($this->relativePositionMap as $id => [$direction, $modules]) {
        $numbers = array_intersect_key($module_numbers, $modules);
        if (!$numbers) {
          continue;
        }
        $module_numbers[$id] = $direction
          ? (end($numbers) + .25)
          : (reset($numbers) - .25);
      }
    }
    asort($module_numbers);
    foreach ($this->implementations as &$implementations_by_id) {
      // Sort items with same weight by enabled module list.
      $implementations_by_id = array_replace(array_intersect_key($module_numbers, $implementations_by_id), $implementations_by_id);
    }
  }

  /**
   * Gets an array suitable for hook_module_implements_alter().
   *
   * @return array<string, false>
   *   Array where the keys are module names.
   */
  public function getAlterable(): array {
    if (empty($this->implementations[0])) {
      // There are no implementations, but the alter hook might add some.
      return [];
    }
    // The alter hook can add groups, but not remove them.
    // This is to keep it simple when groups from multiple hooks were merged.
    return array_fill_keys(
      array_keys($this->implementations[0]),
      FALSE,
    );
  }

  /**
   * Sets the order of implementations with weight zero.
   *
   * @param array<string, mixed> $altered
   *   Altered implementation ids map.
   *   The array values can be anything and will be ignored.
   */
  public function setAlteredOrder(array $altered): void {
    $implementations_weight_0 = $this->implementations[0] ?? NULL;
    if (!$implementations_weight_0) {
      return;
    }
    $this->includeFileGroups = array_intersect_key($this->includeFileGroups, $altered);
    $implementations_weight_0 = array_intersect_key($implementations_weight_0, $altered);
    $this->implementations[0] = array_replace(array_intersect_key($altered, $implementations_weight_0), $implementations_weight_0);
  }

  /**
   * Builds the compact list.
   *
   * @return \Drupal\Core\Extension\Hook\CompactList\CompactImplementationListInterface
   *   Compact list that can be cached.
   */
  public function build(): CompactImplementationListInterface {
    if (!$this->implementations) {
      return new CompactImplementationListEmpty();
    }
    // Sort by weight.
    ksort($this->implementations);
    $list = new CompactImplementationList($this->hook);
    foreach ($this->implementations as $implementations_by_key) {
      foreach ($implementations_by_key as $implementations) {
        foreach ($implementations as $implementation) {
          $list->add(...$implementation);
        }
      }
    }
    $list = $list->optimize();
    if ($this->includeFileGroups) {
      $list = new ModuleIncludeDecorator($list);
      foreach ($this->includeFileGroups as $module => $groups) {
        foreach ($groups as $group => $true) {
          $list->addIncludeFileGroup($module, $group);
        }
      }
    }
    return $list;
  }

}
