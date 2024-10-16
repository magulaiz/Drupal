<?php

declare(strict_types=1);

namespace Drupal\Core\Config\Action\Plugin\ConfigAction;

use Drupal\Core\Config\Action\Attribute\ConfigAction;
use Drupal\Core\Config\Action\ConfigActionManager;
use Drupal\Core\Config\Action\ConfigActionPluginInterface;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @internal
 *   This API is experimental.
 */
#[ConfigAction(
  id: 'cloneAs',
  admin_label: new TranslatableMarkup('Clone entity with a new ID'),
  entity_types: '*',
)]
final class EntityClone implements ConfigActionPluginInterface, ContainerFactoryPluginInterface {

  public function __construct(
    private readonly ConfigManagerInterface $configManager,
    private readonly ConfigActionManager $configActionManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static(
      $container->get(ConfigManagerInterface::class),
      $container->get('plugin.manager.config_action'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function apply(string $configName, mixed $duplicate_id): void {
    assert(is_string($duplicate_id));

    // If the original doesn't exist, there's nothing to duplicate.
    $original = $this->configManager->loadConfigEntityByName($configName);
    if (empty($original)) {
      return;
    }
    $duplicate = $original->createDuplicate();
    $duplicate->set($original->getEntityType()->getKey('id'), $duplicate_id);

    // Use the config action manager to invoke the `entity_create` action on
    // the duplicate, which will ensure that the duplicate is validated.
    $this->configActionManager->applyAction('entity_create:createIfNotExists', $duplicate->getConfigDependencyName(), $duplicate->toArray());
  }

}
