<?php

declare(strict_types=1);

namespace Drupal\navigation\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\navigation\Controller\NavigationMenuBlockController;
use Drupal\navigation\Plugin\Derivative\SystemMenuNavigationBlock as SystemMenuNavigationBlockDeriver;
use Drupal\system\Plugin\Block\SystemMenuBlock;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a generic menu navigation block.
 *
 * @internal
 */
#[Block(
  id: "navigation_menu",
  admin_label: new TranslatableMarkup("Navigation menu"),
  category: new TranslatableMarkup("Menus"),
  deriver: SystemMenuNavigationBlockDeriver::class,
)]
final class NavigationMenuBlock extends SystemMenuBlock implements ContainerFactoryPluginInterface {

  const NAVIGATION_MAX_DEPTH = 3;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('navigation.menu_tree'),
      $container->get('menu.active_trail'),
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
    $form = parent::blockForm($form, $form_state);
    unset($form['menu_levels']['expand_all_items']);
    $form['menu_levels']['depth']['#options'] = range(1, static::NAVIGATION_MAX_DEPTH);

    return $form;
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
    // To conserve bandwidth, we only include the top-level links in the HTML.
    // The subtrees are fetched through an AJAX call.
    [$hash] = _navigation_get_subtrees_hash($menu_name, $level, $depth);
    $subtrees_attached = [
      'drupalSettings' => [
        'navigation' => [
          'subtrees' => [
            $hash => [
              'hash' => $hash,
              'menuName' => $menu_name,
              'level' => $level,
              'depth' => $depth,
            ],
          ],
        ],
      ],
      'library' => [
        'navigation/menu_block',
      ],
    ];

    $build = [
      'menu' => [
        '#title' => $this->configuration['label'],
        '#attached' => $subtrees_attached,
        '#pre_render' => [[NavigationMenuBlockController::class, 'preRenderNavigationTray']],
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'navigation-menu-' . $menu_name,
          ],
          // We need to include all this information here because it is not
          // possible to get the block unique ID from here.
          // @see https://www.drupal.org/project/drupal/issues/2540088
          'data-menu-level' => $level,
          'data-menu-name' => $menu_name,
          'data-menu-depth' => $depth,
          'data-menu-hash' => $hash,
        ],
      ],
    ];

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

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts(): array {
    // We don't use menu active trails here.
    return array_filter(parent::getCacheContexts(), static fn (string $tag) => !str_starts_with($tag, 'route.menu_active_trails'));
  }

}
