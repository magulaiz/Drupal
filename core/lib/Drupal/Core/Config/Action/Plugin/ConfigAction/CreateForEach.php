<?php

declare(strict_types=1);

namespace Drupal\Core\Config\Action\Plugin\ConfigAction;

use Drupal\Core\Config\Action\Attribute\ConfigAction;
use Drupal\Core\Config\Action\ConfigActionException;
use Drupal\Core\Config\Action\ConfigActionManager;
use Drupal\Core\Config\Action\ConfigActionPluginInterface;
use Drupal\Core\Config\Action\Plugin\ConfigAction\Deriver\CreateForEachDeriver;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @internal
 *   This API is experimental.
 */
#[ConfigAction(
  id: 'create_for_bundle',
  admin_label: new TranslatableMarkup('Create entities based for each bundle'),
  entity_types: '*',
  deriver: CreateForEachDeriver::class,
)]
final class CreateForEach implements ConfigActionPluginInterface, ContainerFactoryPluginInterface {

  public function __construct(
    private readonly ConfigManagerInterface $configManager,
    private readonly string $pluginId,
    private readonly string $createAction,
    private readonly ConfigActionManager $configActionManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static(
      $container->get(ConfigManagerInterface::class),
      $plugin_id,
      $plugin_definition['create_action'],
      $container->get('plugin.manager.config_action'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function apply(string $configName, mixed $value): void {
    assert(is_array($value));

    // Ensure the entity we're working on is a bundle of another entity type --
    // for example, a node type, media type, taxonomy vocabulary, etc.
    $bundle = $this->configManager->loadConfigEntityByName($configName);
    $bundle_of = $bundle?->getEntityType()->getBundleOf();
    if (empty($bundle_of)) {
      throw new ConfigActionException("The $this->pluginId action only works on config entities that are bundles of another entity type.");
    }

    // In all of the options passed to this action, replace the `%bundle`
    // placeholder with the actual ID of the entity we're working on.
    $value = static::replaceBundleIdPlaceholder($value, $bundle->id());

    foreach ($value as $name => $values) {
      // Invoke the actual create action via the config action manager, so that
      // the created entity will be validated.
      $this->configActionManager->applyAction($this->createAction, $name, $values);
    }
  }

  private static function replaceBundleIdPlaceholder(string|array $values, string $replace): string|array {
    $search = '%bundle';

    if (is_string($values)) {
      return str_replace($search, $replace, $values);
    }
    foreach ($values as $key => $value) {
      $key = str_replace($search, $replace, $key);
      $values[$key] = static::replaceBundleIdPlaceholder($value, $replace);
    }
    return $values;
  }

}
