<?php

namespace Drupal\Core\Extension;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Fires hook events.
 */
class HookEventDispatcher {

  protected array $invokeMap = [];

  public function __construct(protected EventDispatcherInterface $eventDispatcher, protected array $allImplementations) {

  }

  public function invokeAllWith(string $hook, callable $callback): void {
    $implementations = $this->allImplementations[$hook] ?? [];
    foreach ($this->eventDispatcher->getListeners("drupal_hook.$hook") as $listener) {
      if (is_array($listener) && is_object($listener[0])) {
        $callback($listener(...), $implementations[get_class($listener[0])][$listener[1]]['module']);
      }
    }
  }

  public function invoke($module, $hook, array $args = []) {
    $implementations = $this->allImplementations[$hook] ?? [];
    if (!isset($this->invokeMap[$hook])) {
      foreach ($this->eventDispatcher->getListeners("drupal_hook.$hook") as $listener) {
        if (is_array($listener) && is_object($listener[0])) {
          $implementation_module = $implementations[get_class($listener[0])][$listener[1]]['module'];
          if (isset($this->invokeMap[$hook][$implementation_module])) {
            throw new \LogicException("Module $implementation_module should not implement $hook more than once");
          }
          $this->invokeMap[$hook][$implementation_module] = $listener;
        }
      }
    }
    if (isset($this->invokeMap[$hook][$module])) {
      return ($this->invokeMap[$hook][$module])(... $args);
    }
  }

}
