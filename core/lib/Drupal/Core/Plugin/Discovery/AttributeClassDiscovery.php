<?php

namespace Drupal\Core\Plugin\Discovery;

use Drupal\Component\Plugin\Attribute\AttributeInterface;
use Drupal\Component\Plugin\Attribute\Dependencies;
use Drupal\Component\Plugin\Discovery\AttributeClassDiscovery as ComponentAttributeClassDiscovery;
use Drupal\Core\Extension\ModuleHandlerInterface;
use PhpParser\ConstExprEvaluationException;
use PhpParser\ConstExprEvaluator;
use PhpParser\Node\Attribute;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeFinder;

/**
 * Defines a discovery mechanism to find plugins using attributes.
 */
class AttributeClassDiscovery extends ComponentAttributeClassDiscovery {

  /**
   * A suffix to append to each PSR-4 directory associated with a base namespace.
   *
   * This suffix is used to form the directories where plugins are found.
   *
   * @var string
   */
  protected $directorySuffix = '';

  /**
   * A suffix to append to each base namespace.
   *
   * This suffix is used to obtain the namespaces where plugins are found.
   *
   * @var string
   */
  protected $namespaceSuffix = '';

  /**
   * Constructs an AttributeClassDiscovery object.
   *
   * @param string $subdir
   *   Either the plugin's subdirectory, for example 'Plugin/views/filter', or
   *   empty string if plugins are located at the top level of the namespace.
   * @param \Traversable $rootNamespacesIterator
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   *   If $subdir is not an empty string, it will be appended to each namespace.
   * @param string $pluginDefinitionAttributeName
   *   (optional) The name of the attribute that contains the plugin definition.
   *   Defaults to 'Drupal\Component\Plugin\Attribute\Plugin'.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface|null $moduleHandler
   *   The module handler.
   */
  public function __construct(
    string $subdir,
    protected \Traversable $rootNamespacesIterator,
    string $pluginDefinitionAttributeName = 'Drupal\Component\Plugin\Attribute\Plugin',
    protected ?ModuleHandlerInterface $moduleHandler = NULL,
  ) {
    if ($subdir) {
      // Prepend a directory separator to $subdir,
      // if it does not already have one.
      if ('/' !== $subdir[0]) {
        $subdir = '/' . $subdir;
      }
      $this->directorySuffix = $subdir;
      $this->namespaceSuffix = str_replace('/', '\\', $subdir);
    }
    parent::__construct([], $pluginDefinitionAttributeName);
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareAttributeDefinition(AttributeInterface $attribute, string $class): void {
    parent::prepareAttributeDefinition($attribute, $class);

    if (!$attribute->getProvider()) {
      $attribute->setProvider($this->getProviderFromNamespace($class));
    }
  }

  /**
   * Extracts the provider name from a Drupal namespace.
   *
   * @param string $namespace
   *   The namespace to extract the provider from.
   *
   * @return string|null
   *   The matching provider name, or NULL otherwise.
   */
  protected function getProviderFromNamespace(string $namespace): ?string {
    preg_match('|^Drupal\\\\(?<provider>[\w]+)\\\\|', $namespace, $matches);

    if (isset($matches['provider'])) {
      return mb_strtolower($matches['provider']);
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  protected function getPluginNamespaces(): array {
    $plugin_namespaces = [];
    if ($this->namespaceSuffix) {
      foreach ($this->rootNamespacesIterator as $namespace => $dirs) {
        // Append the namespace suffix to the base namespace, to obtain the
        // plugin namespace; for example, 'Drupal\views' may become
        // 'Drupal\views\Plugin\Block'.
        $namespace .= $this->namespaceSuffix;
        foreach ((array) $dirs as $dir) {
          // Append the directory suffix to the PSR-4 base directory, to obtain
          // the directory where plugins are found. For example,
          // DRUPAL_ROOT . '/core/modules/views/src' may become
          // DRUPAL_ROOT . '/core/modules/views/src/Plugin/Block'.
          $plugin_namespaces[$namespace][] = $dir . $this->directorySuffix;
        }
      }
    }
    else {
      // Both the namespace suffix and the directory suffix are empty,
      // so the plugin namespaces and directories are the same as the base
      // directories.
      foreach ($this->rootNamespacesIterator as $namespace => $dirs) {
        $plugin_namespaces[$namespace] = (array) $dirs;
      }
    }

    return $plugin_namespaces;
  }

  /**
   * Getter for module handler.
   *
   * @return \Drupal\Core\Extension\ModuleHandlerInterface
   *   The module handler.
   */
  protected function getModuleHandler(): ModuleHandlerInterface {
    if (!isset($this->moduleHandler)) {
      $this->moduleHandler = \Drupal::moduleHandler();
    }
    return $this->moduleHandler;
  }

  /**
   * Injection setter for module handler.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   *
   * @return $this
   */
  public function setModuleHandler(ModuleHandlerInterface $moduleHandler): static {
    $this->moduleHandler = $moduleHandler;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  protected function getClassDependencies(Class_ $static_parsed_class): array {
    // Include modules identified in the Dependencies attribute as dependencies.
    $dependencies = parent::getClassDependencies($static_parsed_class);
    $modules = [];
    $nodeFinder = new NodeFinder();
    $attributes = $nodeFinder->findInstanceOf($static_parsed_class->attrGroups, Attribute::class);
    foreach ($attributes as $attribute) {
      if (((string) $attribute->name === Dependencies::class) &&
          !empty($attribute->args)) {
        // Dependencies attribute has only one argument.
        $arg = reset($attribute->args);
        if ($arg->value instanceof Array_) {
          try {
            $modules = (new ConstExprEvaluator())->evaluateSilently($arg->value);
          }
          catch (ConstExprEvaluationException) {
          }
          break;
        }
      }
    }

    return $dependencies + ['module' => $modules];
  }

  /**
   * {@inheritdoc}
   */
  protected function hasMissingClassDependencies(array $dependencies): bool {
    // Check module dependencies first, to prevent errors from testing whether
    // interfaces, classes, or traits exist.
    $modules = $dependencies['module'] ?? [];
    if ($modules && !empty(array_diff($modules, array_keys($this->getModuleHandler()->getModuleList())))) {
      return TRUE;
    }

    return parent::hasMissingClassDependencies($dependencies);
  }

}
