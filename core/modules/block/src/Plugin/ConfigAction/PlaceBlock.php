<?php

declare(strict_types=1);

namespace Drupal\block\Plugin\ConfigAction;

use Drupal\block\BlockInterface;
use Drupal\Core\Config\Action\Attribute\ConfigAction;
use Drupal\Core\Config\Action\ConfigActionException;
use Drupal\Core\Config\Action\ConfigActionPluginInterface;
use Drupal\Core\Config\Action\Plugin\ConfigAction\EntityCreate;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
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
    private readonly ConfigEntityStorageInterface $blockStorage,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('plugin.manager.config_action')->createInstance('entity_create:create'),
      $plugin_definition['which_theme'],
      $container->get(ConfigFactoryInterface::class),
      $container->get(EntityTypeManagerInterface::class)->getStorage('block'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function apply(string $configName, mixed $value): void {
    assert(is_array($value));

    $theme = $this->configFactory->get('system.theme')->get($this->whichTheme);
    $value['theme'] = $theme;

    if (array_key_exists('region', $value)) {
      assert(is_array($value['region']));
      $value['region'] = $value['region'][$theme] ?? $value['default_region'] ?? throw new ConfigActionException("Cannot determine which region to place this block into, because no default region was provided.");
      unset($value['default_region']);
    }

    if (array_key_exists('position', $value)) {
      $blocks = $this->blockStorage->loadByProperties([
        'theme' => $theme,
        'region' => $value['region'],
      ]);
      // Sort the blocks by weight.
      uasort($blocks, fn (BlockInterface $a, BlockInterface $b) => $a->getWeight() <=> $b->getWeight());

      $value['weight'] = match ($value['position']) {
        'first' => reset($blocks)->getWeight() - 1,
        'last' => end($blocks)->getWeight() + 1,
      };
    }
    else {
      // Ensure a weight is set by default.
      $value += ['weight' => 0];
    }

    $this->entityCreate->apply($configName, $value);
  }

}
