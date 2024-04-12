<?php

declare(strict_types=1);

namespace Drupal\navigation\Plugin\NavigationBlock;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\navigation\Attribute\NavigationBlock;
use Drupal\navigation\NavigationBlockPluginBase;
use Drupal\navigation\UserLazyBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a user navigation block.
 */
#[NavigationBlock(
  id: 'user',
  admin_label: new TranslatableMarkup('User'),
)]
class UserNavigationBlock extends NavigationBlockPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs the plugin instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\navigation\UserLazyBuilder $userLazyBuilder
   *   The navigation user lazy builder.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected readonly UserLazyBuilder $userLazyBuilder,
    protected readonly ModuleHandlerInterface $moduleHandler,
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
      $container->get('navigation.user_lazy_builder'),
      $container->get('module_handler'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    return [
      '#lazy_builder' => ['navigation.user_lazy_builder:renderNavigationLinks', []],
      '#create_placeholder' => TRUE,
      '#cache' => [
        'keys' => ['user_set_navigation_links'],
        'contexts' => ['user'],
      ],
      '#lazy_builder_preview' => [
        '#help' => $this->moduleHandler->moduleExists('help'),
        '#theme' => 'menu_region__footer',
        '#items' => $this->userLazyBuilder->userOperationLinks(FALSE),
        '#menu_name' => 'user',
        '#title' => $this->t('My Account'),
      ],
    ];
  }

}
