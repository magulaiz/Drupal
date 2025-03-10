<?php

namespace Drupal\Core\DependencyInjection;

use Drupal\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyContainerBuilder;
use Symfony\Component\DependencyInjection\Container as SymfonyContainer;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Drupal's dependency injection container builder.
 *
 * @ingroup container
 */
class ContainerBuilder extends SymfonyContainerBuilder implements ContainerInterface {

  /**
   * @var true
   */
  protected bool $frozen = FALSE;

  /**
   * {@inheritdoc}
   */
  public function __construct(?ParameterBagInterface $parameterBag = NULL) {
    parent::__construct($parameterBag);
    $this->setResourceTracking(FALSE);
  }

  /**
   * Overrides Symfony\Component\DependencyInjection\ContainerBuilder::set().
   *
   * Upstream container builder removes the definition on set before calling
   * grandparent which breaks further rounds of container dumping.
   */
  public function set($id, $service): void {
    SymfonyContainer::set($id, $service);
  }

  /**
   * {@inheritdoc}
   */
  public function register($id, $class = NULL): Definition {
    $definition = new Definition($class);
    // As of Symfony 5.2 all services are private by default, but in Drupal
    // services are still public by default.
    $definition->setPublic(TRUE);
    return $this->setDefinition($id, $definition);
  }

  /**
   * {@inheritdoc}
   */
  public function setAlias($alias, $id): Alias {
    $alias = parent::setAlias($alias, $id);
    // As of Symfony 3.4 all aliases are private by default.
    $alias->setPublic(TRUE);
    return $alias;
  }

  /**
   * {@inheritdoc}
   */
  public function setParameter($name, $value): void {
    if (strtolower($name) !== $name) {
      throw new \InvalidArgumentException("Parameter names must be lowercase: $name");
    }
    if ($this->frozen) {
      throw new LogicException('Impossible to call set() on a frozen ParameterBag.');
    }
    parent::setParameter($name, $value);
  }

  /**
   * {@inheritdoc}
   */
  public function setPluginParameter(string $plugin_type, string $name, mixed $value): void {
    if ($this->parameterBag instanceof FrozenParameterBag) {
      $this->frozen = TRUE;
      $this->parameterBag = new ParameterBag($this->parameterBag->all());
    }
    parent::setParameter("drupal_plugin.$plugin_type.$name", $value);
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginParameter(string $plugin_type, string $name): mixed {
    return $this->hasParameter("drupal_plugin.$plugin_type.$name") ? $this->getParameter("drupal_plugin.$plugin_type.$name") : NULL;
  }

  public function __serialize(): array {
    return [
      'definitions' => $this->getDefinitions(),
      'aliases' => $this->getAliases(),
      'parameters' => $this->getParameterBag()->all(),
    ];
  }

  public function __unserialize(array $data): void {
    $this->setDefinitions($data['definitions']);
    $this->setAliases($data['aliases']);
    $this->parameterBag = new ParameterBag($data['parameters']);
    $this->setResourceTracking(FALSE);
  }

  public function prepareNewBuilder(ContainerBuilder $old): static {
    foreach (['BeforeOptimizationPasses', 'OptimizationPasses', 'BeforeRemovingPasses', 'RemovingPasses', 'AfterRemovingPasses'] as $key) {
      $getter = 'get' . $key;
      $setter = 'set' . $key;
      $this->getCompilerPassConfig()->$setter($old->getCompilerPassConfig()->$getter());
    }
    $this->services = $old->services;
    return $this;
  }

}
