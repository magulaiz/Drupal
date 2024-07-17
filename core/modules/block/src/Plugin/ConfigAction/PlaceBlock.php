<?php

declare(strict_types=1);

namespace Drupal\block\Plugin\ConfigAction;

use Drupal\Core\Config\Action\Attribute\ConfigAction;
use Drupal\Core\Config\Action\ConfigActionException;
use Drupal\Core\Config\Action\ConfigActionPluginInterface;
use Drupal\Core\Config\Action\Plugin\ConfigAction\EntityCreate;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

#[ConfigAction(
  id: 'placeBlock',
  admin_label: new TranslatableMarkup('Place a block'),
  entity_types: ['block'],
  deriver: PlaceBlockDeriver::class,
)]
final class PlaceBlock implements ConfigActionPluginInterface, ContainerFactoryPluginInterface {

  public function __construct(
    private readonly EntityCreate $entityCreate,
    private readonly string $whichTheme,
    private readonly ConfigFactoryInterface $configFactory,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('plugin.manager.config_action')->createInstance('entity_create:create'),
      $plugin_definition['which_theme'],
      $container->get(ConfigFactoryInterface::class),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function apply(string $configName, mixed $value): void {
    assert(is_array($value));

    $theme = $this->configFactory->get('system.theme')->get($this->whichTheme);
    $value['theme'] = $theme;

    assert(is_array($value['region']));
    $value['region'] = $value['region'][$theme] ?? $value['default_region'] ?? throw new ConfigActionException("Cannot determine which region to place this block into, because no default region was provided.");
    unset($value['default_region']);

    $this->entityCreate->apply($configName, $value);
  }

}
