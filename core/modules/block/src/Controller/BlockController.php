<?php

namespace Drupal\block\Controller;

use Drupal\Component\Utility\Html;
use Drupal\block\BlockInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller routines for admin block routes.
 */
class BlockController extends ControllerBase {

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * Constructs a new BlockController instance.
   *
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   */
  public function __construct(ThemeHandlerInterface $theme_handler) {
    $this->themeHandler = $theme_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('theme_handler')
    );
  }

  /**
   * Calls a method on a block and reloads the listing page.
   *
   * @param \Drupal\block\BlockInterface $block
   *   The block being acted upon.
   * @param string $op
   *   The operation to perform, e.g., 'enable' or 'disable'.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect back to the listing page.
   */
  public function performOperation(BlockInterface $block, $op) {
    $block->$op()->save();
    $this->messenger()->addStatus($this->t('The block settings have been updated.'));
    return $this->redirect('block.admin_display');
  }

  /**
   * Returns a block theme demo page.
   *
   * @param string $theme
   *   The name of the theme.
   *
   * @return array
   *   A #type 'page' render array containing the block region demo.
   */
  public function demo($theme) {
    if (!$this->themeHandler->hasUi($theme)) {
      throw new NotFoundHttpException();
    }

    $page = [
      '#title' => Html::escape($this->themeHandler->getName($theme)),
      '#type' => 'page',
      '#attached' => [
        'drupalSettings' => [
          // The block demonstration page is not marked as an administrative
          // page by \Drupal::service('router.admin_context')->isAdminRoute()
          // function in order to use the frontend theme. Since JavaScript
          // relies on a proper separation of admin pages, it needs to know this
          // is an actual administrative page.
          'path' => ['currentPathIsAdmin' => TRUE],
        ],
        'library' => [
          'block/drupal.block.admin',
        ],
      ],
    ];

    // Show descriptions in each visible page region, nothing else.
    $visible_regions = $this->getVisibleRegionNames($theme);
    foreach (array_keys($visible_regions) as $region) {
      $page[$region]['block_description'] = [
        '#type' => 'inline_template',
        '#template' => '<div class="block-region demo-block">{{ region_name }}</div>',
        '#context' => ['region_name' => $visible_regions[$region]],
      ];
    }

    return $page;
  }

  /**
   * Returns the human-readable list of regions keyed by machine name.
   *
   * @param string $theme
   *   The name of the theme.
   *
   * @return array
   *   An array of human-readable region names keyed by machine name.
   */
  protected function getVisibleRegionNames($theme) {
    return system_region_list($theme, REGIONS_VISIBLE);
  }

  /**
   * Provides a redirect for /admin/structure/block and child paths.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param string $route_name
   *   The name of the route whose path has changed. Do not include '.bc'.
   * @param \Drupal\block\BlockInterface|null $block
   *   The Block configuration entity to be deleted.
   * @param string $plugin_id
   *   (optional) The plugin ID for the block instance.
   * @param string $theme
   *   (optional) The name of the theme.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *
   * @deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use
   *   /admin/appearance/block and child paths directly instead of
   *   /admin/structure/block
   *
   * @see https://www.drupal.org/node/3320855
   */
  public function blockLayoutRedirect(Request $request, string $route_name, ?BlockInterface $block = NULL, string $plugin_id = '', string $theme = ''): RedirectResponse {
    @trigger_error('The path /admin/structure/block, with its child paths, is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use /admin/appearance/block. See https://www.drupal.org/node/3318112.', E_USER_DEPRECATED);
    $args = array_filter([
      'block' => $block ? $block->id() : '',
      'plugin_id' => $plugin_id,
      'theme' => $theme,
    ]);
    $query = $request->query->all();

    $params = [
      '%old_path' => Url::fromRoute("$route_name.bc", $args)->toString(),
      '%new_path' => Url::fromRoute($route_name, $args)->toString(),
      '%change_record' => 'https://www.drupal.org/node/3320855',
    ];
    $this ->messenger()
      ->addWarning($this->t('You have been redirected from %old_path. Update links, shortcuts, and bookmarks to use %new_path.', $params));
    $this->getLogger('block')
      ->warning('A user was redirected from %old_path. This redirect will be removed in a future version of Drupal. Update links, shortcuts, and bookmarks to use %new_path. See %change_record for more information.', $params);

    return $this->redirect($route_name, $args, ['query' => $query], 301);
  }

}
