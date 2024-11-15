<?php

namespace Drupal\views\Plugin\ConfigAction;

use Drupal\Core\Config\Action\ConfigActionException;
use Drupal\Core\Config\Action\ConfigActionPluginInterface;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\views\ViewEntityInterface;
use Drupal\views\ViewExecutable;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for views display option config actions.
 */
abstract class ViewsDisplayOptionBase implements ConfigActionPluginInterface, ContainerFactoryPluginInterface {

  /**
   * Constructs instance of ViewsDisplayOptionBase.
   *
   * @param \Drupal\Core\Config\ConfigManagerInterface $configManager
   *   The configuration manager.
   */
  public function __construct(
    protected readonly ConfigManagerInterface $configManager,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($container->get('config.manager'));
  }

  /**
   * {@inheritdoc}
   */
  public function apply(string $configName, mixed $value): void {
    assert(is_array($value));
    if (!array_is_list($value)) {
      $value = [$value];
    }
    // Load the views executable.
    $entity = $this->configManager->loadConfigEntityByName($configName);
    if (empty($entity)) {
      throw new ConfigActionException(sprintf('View %s does not exist', $configName));
    }
    if (!$entity instanceof ViewEntityInterface) {
      throw new ConfigActionException(sprintf('%s is not view', $configName));
    }
    $view = $entity->getExecutable();
    array_walk($value, [$this, 'applySingle'], $view);
    $errors = $view->validate();
    if (!empty($errors)) {
      throw new ConfigActionException(sprintf('Validation of the view ended with following errors: %s', implode(', ', $errors)));
    }
    $view->save();
  }

  /**
   * Apply single config action to view display option.
   *
   * @param array $value
   *   The settings.
   * @param int $key
   *   The delta of settings.
   * @param \Drupal\views\ViewExecutable $view
   *   The view executable object.
   */
  abstract protected function applySingle(array $value, int $key, ViewExecutable $view): void;

}
