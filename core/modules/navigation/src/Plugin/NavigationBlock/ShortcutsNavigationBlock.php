<?php

declare(strict_types=1);

namespace Drupal\navigation\Plugin\NavigationBlock;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\navigation\Attribute\NavigationBlock;
use Drupal\navigation\NavigationBlockPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a shortcuts navigation block class.
 *
 * @todo Move to Shortcut module as part of the core MR process.
 */
#[NavigationBlock(
  id: 'shortcuts',
  admin_label: new TranslatableMarkup('Shortcuts'),
)]
class ShortcutsNavigationBlock extends NavigationBlockPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs a new ShortcutsNavigationBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, protected ModuleHandlerInterface $moduleHandler) {
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
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account): AccessResultInterface {
    // This navigation block requires shortcut module. Once the plugin is moved
    // to the module, this should not be necessary.
    if (!$this->moduleHandler->moduleExists('shortcut')) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowedIfHasPermission($account, 'access shortcuts');
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    return [
      // @phpstan-ignore-next-line
      '#lazy_builder' => ['navigation.shortcut_lazy_builder:lazyLinks', [$this->configuration['label']]],
      '#create_placeholder' => TRUE,
      '#cache' => [
        'keys' => ['shortcut_set_navigation_links'],
        'contexts' => ['user'],
      ],
      '#lazy_builder_preview' => [
        '#items' => [
          [
            'title' => $this->configuration['label'],
            'class' => 'shortcuts',
            'below' => [],
          ],
        ],
        '#theme' => 'navigation_menu',
        '#menu_name' => 'shortcuts',
      ],
    ];
  }

}
