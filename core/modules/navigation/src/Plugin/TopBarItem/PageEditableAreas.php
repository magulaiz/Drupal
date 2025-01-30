<?php

namespace Drupal\navigation\Plugin\TopBarItem;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\navigation\Attribute\TopBarItem;
use Drupal\navigation\EntityRouteHelper;
use Drupal\navigation\TopBarItemBase;
use Drupal\navigation\TopBarRegion;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the Editable areas top bar item.
 */
#[TopBarItem(
  id: 'page_editable_areas',
  region: TopBarRegion::Tools,
  label: new TranslatableMarkup('Editable areas'),
)]
class PageEditableAreas extends TopBarItemBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private EntityRouteHelper $entityRouteHelper,
    private AccountInterface $currentUser,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get(EntityRouteHelper::class),
      $container->get('current_user'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $build = [
      '#cache' => [
        'contexts' => ['user.permissions'],
      ],
    ];

    if (!$this->currentUser->hasPermission('access contextual links')) {
      return $build;
    }

    $build += [
      [
        '#type' => 'component',
        '#component' => 'navigation:toolbar-button',
        '#props' => [
          'icon' => 'preview',
          'extra_classes' => ['navigation-contextual-link'],
        ],
        '#slots' => [
          'content' => (string) $this->t('Editable areas'),
        ],
      ],
    ];
    $build['#attached'] = [
      'library' => [
        'contextual/drupal.contextual-toolbar',
      ],
    ];
    return $build;
  }

}
