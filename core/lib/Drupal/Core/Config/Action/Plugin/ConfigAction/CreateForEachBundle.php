<?php

declare(strict_types=1);

namespace Drupal\Core\Config\Action\Plugin\ConfigAction;

use Drupal\Core\Config\Action\Attribute\ConfigAction;
use Drupal\Core\Config\Action\ConfigActionManager;
use Drupal\Core\Config\Action\ConfigActionPluginInterface;
use Drupal\Core\Config\Action\Plugin\ConfigAction\Deriver\CreateForEachBundleDeriver;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @internal
 *   This API is experimental.
 */
#[ConfigAction(
  id: 'create_for_each_bundle',
  admin_label: new TranslatableMarkup('Create entities for each bundle of an entity type'),
  deriver: CreateForEachBundleDeriver::class,
)]
final class CreateForEachBundle implements ConfigActionPluginInterface, ContainerFactoryPluginInterface {

  public function __construct(
    private readonly ConfigManagerInterface $configManager,
    private readonly string $createAction,
    private readonly ConfigActionManager $configActionManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static(
      $container->get(ConfigManagerInterface::class),
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
    $bundle = $this->configManager->loadConfigEntityByName($configName);
    assert(is_object($bundle));
    $value = static::replacePlaceholders($value, $bundle->id(), $bundle->label());

    foreach ($value as $name => $values) {
      // Invoke the actual create action via the config action manager, so that
      // the created entity will be validated.
      $this->configActionManager->applyAction('entity_create:' . $this->createAction, $name, $values);
    }
  }

  private static function replacePlaceholders(mixed $subject, string $bundle_id, string $label): mixed {
    $search = ['%bundle', '%label'];
    $replace = [$bundle_id, $label];

    if (is_string($subject)) {
      $subject = str_replace($search, $replace, $subject);
    }
    elseif (is_array($subject)) {
      foreach ($subject as $old_key => $value) {
        $value = static::replacePlaceholders($value, $bundle_id, $label);

        // Only replace the `%bundle` placeholder in array keys.
        $new_key = str_replace($search[0], $replace[0], $old_key);
        if ($old_key !== $new_key) {
          unset($subject[$old_key]);
        }
        $subject[$new_key] = $value;
      }
    }
    return $subject;
  }

}
