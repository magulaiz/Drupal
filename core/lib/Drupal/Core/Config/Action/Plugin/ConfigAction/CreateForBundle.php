<?php

declare(strict_types=1);

namespace Drupal\Core\Config\Action\Plugin\ConfigAction;

use Drupal\Core\Config\Action\Attribute\ConfigAction;
use Drupal\Core\Config\Action\ConfigActionManager;
use Drupal\Core\Config\Action\ConfigActionPluginInterface;
use Drupal\Core\Config\Action\Plugin\ConfigAction\Deriver\CreateForBundleDeriver;
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
  admin_label: new TranslatableMarkup('Create entities for each bundle of an entity type'),
  deriver: CreateForBundleDeriver::class,
)]
final class CreateForBundle implements ConfigActionPluginInterface, ContainerFactoryPluginInterface {

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

    // In all of the options passed to this action, replace the `%bundle`
    // placeholder with the actual ID of the entity we're working on.
    $bundle_id = $this->configManager->loadConfigEntityByName($configName)?->id();
    assert(is_string($bundle_id));
    $value = static::replaceBundleIdPlaceholder($value, $bundle_id);

    foreach ($value as $name => $values) {
      // Invoke the actual create action via the config action manager, so that
      // the created entity will be validated.
      $this->configActionManager->applyAction('entity_create:' . $this->createAction, $name, $values);
    }
  }

  private static function replaceBundleIdPlaceholder(string|array $subject, string $replace): string|array {
    $search = '%bundle';

    if (is_string($subject)) {
      return str_replace($search, $replace, $subject);
    }
    foreach ($subject as $old_key => $value) {
      $value = static::replaceBundleIdPlaceholder($value, $replace);

      $new_key = str_replace($search, $replace, $old_key);
      if (str_contains($old_key, $search)) {
        unset($subject[$old_key]);
      }
      $subject[$new_key] = $value;
    }
    return $subject;
  }

}
