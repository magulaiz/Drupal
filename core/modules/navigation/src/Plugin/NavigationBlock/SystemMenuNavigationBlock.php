<?php

declare(strict_types=1);

namespace Drupal\navigation\Plugin\NavigationBlock;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\navigation\Attribute\NavigationBlock;
use Drupal\navigation\NavigationBlockPluginBase;
use Drupal\navigation\Plugin\Derivative\SystemMenuNavigationBlock as SystemMenuNavigationBlockDeriver;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a generic menu navigation block.
 */
#[NavigationBlock(
  id: "system_menu_navigation_block",
  admin_label: new TranslatableMarkup("Menu"),
  category: new TranslatableMarkup("Menus"),
  deriver: SystemMenuNavigationBlockDeriver::class,
)]
class SystemMenuNavigationBlock extends NavigationBlockPluginBase implements ContainerFactoryPluginInterface {

  const NAVIGATION_MAX_DEPTH = 3;

  /**
   * Constructs a new SystemMenuNavigationBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menuLinkTree
   *   The menu tree service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, protected MenuLinkTreeInterface $menuLinkTree) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('navigation.menu_tree')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'level' => 1,
      'depth' => 0,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state): array {
    $config = $this->configuration;

    $defaults = $this->defaultConfiguration();
    $form['menu_levels'] = [
      '#type' => 'details',
      '#title' => $this->t('Menu levels'),
      // Open if not set to defaults.
      '#open' => $defaults['level'] !== $config['level'] || $defaults['depth'] !== $config['depth'],
      '#process' => [[self::class, 'processMenuLevelParents']],
    ];

    $level_options = range(0, $this->menuLinkTree->maxDepth());
    unset($level_options[0]);

    $form['menu_levels']['level'] = [
      '#type' => 'select',
      '#title' => $this->t('Initial visibility level'),
      '#default_value' => $config['level'],
      '#options' => $level_options,
      '#description' => $this->t('The menu is visible only starting from that level.'),
      '#required' => TRUE,
    ];

    $form['menu_levels']['depth'] = [
      '#type' => 'select',
      '#title' => $this->t('Number of levels to display'),
      '#default_value' => $config['depth'],
      '#options' => range(1, static::NAVIGATION_MAX_DEPTH),
      '#description' => $this->t('This maximum number includes the initial level.'),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * Form API callback: Processes the menu_levels field element.
   *
   * Adjusts the #parents of menu_levels to save its children at the top level.
   */
  public static function processMenuLevelParents(&$element, FormStateInterface $form_state, &$complete_form): array {
    array_pop($element['#parents']);
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state): void {
    $this->configuration['level'] = $form_state->getValue('level');
    $this->configuration['depth'] = $form_state->getValue('depth');
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $menu_name = $this->getDerivativeId();
    $level = $this->configuration['level'];
    $depth = $this->configuration['depth'];
    $parameters = new MenuTreeParameters();
    $parameters
      ->setMinDepth($level)
      ->setMaxDepth(min($level + $depth, $this->menuLinkTree->maxDepth()))
      ->onlyEnabledLinks();
    $tree = $this->menuLinkTree->load($menu_name, $parameters);
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $this->menuLinkTree->transform($tree, $manipulators);
    $build = $this->menuLinkTree->build($tree);
    if (!empty($build)) {
      $build['#title'] = $this->configuration['label'];
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [
      'module' => [
        'system',
      ],
    ];
  }

}
