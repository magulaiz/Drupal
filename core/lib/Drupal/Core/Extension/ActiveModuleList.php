<?php

declare(strict_types = 1);

namespace Drupal\Core\Extension;

use Drupal\Component\Event\ResetEvent;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Active module list.
 */
class ActiveModuleList implements WritableActiveModuleListInterface {

  /**
   * Constructor.
   *
   * @param string $root
   *   Drupal root dir.
   * @param array<string, \Drupal\Core\Extension\Extension> $modules
   *   Module objects.
   * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   Event dispatcher.
   */
  public function __construct(
    private readonly string $root,
    private array $modules,
    private readonly EventDispatcherInterface $eventDispatcher,
  ) {}

  /**
   * Creates a new instance from container parameters.
   *
   * @param string $root
   *   Drupal root dir.
   * @param array<string, array> $module_list
   *   Modules from the container parameter.
   * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   Event dispatcher.
   *
   * @return self
   *   New instance.
   */
  public static function fromContainerParameter(
    #[Autowire('%app.root%')]
    string $root,
    #[Autowire('%container.modules%')]
    array $module_list,
    EventDispatcherInterface $eventDispatcher,
  ): self {
    $modules = [];
    foreach ($module_list as $name => $array) {
      $modules[$name] = new Extension(
        $root,
        $array['type'],
        $array['pathname'],
        $array['filename'],
      );
      assert($name === $modules[$name]->getName(), "Module name of '$name'.");
    }
    return new self($root, $modules, $eventDispatcher);
  }

  /**
   * {@inheritdoc}
   */
  public function setModules(array $modules): void {
    assert(array_keys($modules) === array_map(
      static fn (Extension $module): string => $module->getName(),
      array_values($modules),
    ));
    $this->modules = $modules;
    $this->dispatchEvent();
  }

  /**
   * {@inheritdoc}
   */
  public function addModule(string $name, string $path): void {
    $this->add('module', $name, $path);
  }

  /**
   * {@inheritdoc}
   */
  public function addProfile(string $name, string $path): void {
    $this->add('profile', $name, $path);
  }

  /**
   * Adds a module or profile to the list of currently active modules.
   *
   * @param string $type
   *   The extension type; either 'module' or 'profile'.
   * @param string $name
   *   The module name; e.g., 'node'.
   * @param string $path
   *   The module path; e.g., 'core/modules/node'.
   */
  protected function add(string $type, string $name, string $path): void {
    assert(in_array($type, ['module', 'profile']));
    $pathname = "$path/$name.info.yml";
    $filename = file_exists($this->root . "/$path/$name.$type") ? "$name.$type" : NULL;
    $this->modules[$name] = new Extension($this->root, $type, $pathname, $filename);
    $this->dispatchEvent();
  }

  /**
   * {@inheritdoc}
   */
  public function getModules(): array {
    return $this->modules;
  }

  /**
   * {@inheritdoc}
   */
  public function getModule(string $name): ?Extension {
    return $this->modules[$name] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function moduleExists(string $module): bool {
    return isset($this->modules[$module]);
  }

  /**
   * Notifies subscribers.
   */
  private function dispatchEvent(): void {
    $this->eventDispatcher->dispatch(new ResetEvent(), ExtensionEvents::MODULE_LIST_WAS_UPDATED);
  }

}
