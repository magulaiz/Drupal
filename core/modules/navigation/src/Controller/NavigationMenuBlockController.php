<?php

declare(strict_types=1);

namespace Drupal\navigation\Controller;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\navigation\Ajax\SetSubtreesCommand;

/**
 * Controller for Navigation Menu Block AJAX calls.
 *
 * @internal
 */
final class NavigationMenuBlockController extends ControllerBase implements TrustedCallbackInterface {

  /**
   * Constructs a ToolbarController object.
   *
   * @param \Drupal\Component\Datetime\TimeInterface|null $time
   *   The time service.
   */
  public function __construct(
    protected ?TimeInterface $time = NULL,
  ) {}

  /**
   * Returns an AJAX response to render the navigation menu block subtrees.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The AJAX response.
   */
  public function subtreesAjax(string $menu_name, int $level, int $depth) {
    [$subtrees] = navigation_get_rendered_subtrees($menu_name, $level, $depth);
    [$hash] = _navigation_get_subtrees_hash($menu_name, $level, $depth);
    $response = new AjaxResponse();
    $response->addCommand(new SetSubtreesCommand($hash, strval($subtrees)));

    // The Expires HTTP header is the heart of the client-side HTTP caching. The
    // additional server-side page cache only takes effect when the client
    // accesses the callback URL again (e.g., after clearing the browser cache
    // or when force-reloading a Drupal page).
    $max_age = 365 * 24 * 60 * 60;
    $response->setPrivate();
    $response->setMaxAge($max_age);

    $expires = new \DateTime();
    $expires->setTimestamp($this->time->getRequestTime() + $max_age);
    $response->setExpires($expires);

    return $response;
  }

  /**
   * Checks access for the subtree controller.
   *
   * @param string $menu_name
   *   The navigation menu block menu name.
   * @param int $level
   *   The navigation menu block initial level.
   * @param int $depth
   *   The navigation menu block depth.
   * @param string $hash
   *   The hash of the navigation subtrees.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function checkSubTreeAccess(string $menu_name, int $level, int $depth, string $hash) {
    $expected_hash = _navigation_get_subtrees_hash($menu_name, $level, $depth)[0];
    return AccessResult::allowedIf($this->currentUser()->hasPermission('access navigation') && hash_equals($expected_hash, $hash))->cachePerPermissions();
  }

  /**
   * Renders the navigation's first tray.
   *
   * @param array $element
   *   A renderable array.
   *
   * @return array
   *   The updated renderable array.
   *
   * @see \Drupal\Core\Render\RendererInterface::render()
   */
  public static function preRenderNavigationTray(array $element) {
    $menu_tree = \Drupal::service('navigation.menu_tree');

    $menu_name = $element["#attributes"]["data-menu-name"];
    $level = $element["#attributes"]["data-menu-level"];
    $parameters = new MenuTreeParameters();
    $parameters
      ->setMinDepth($level)
      ->setMaxDepth($level)
      ->onlyEnabledLinks();
    $tree = $menu_tree->load($menu_name, $parameters);
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $menu_tree->transform($tree, $manipulators);
    $element['menu'] = $menu_tree->build($tree);

    return $element;
  }

  /**
   * The #pre_render callback for navigation_get_rendered_subtrees().
   *
   * @param array $data
   *   A renderable array.
   *
   * @return array
   *   The updated renderable array.
   */
  public static function preRenderGetRenderedSubtrees(array $data) {
    $renderer = \Drupal::service('renderer');
    $menu_tree = \Drupal::service('navigation.menu_tree');
    $cacheability = CacheableMetadata::createFromRenderArray($data);

    $menu_name = $data["#attributes"]["data-menu-name"];
    $level = $data["#attributes"]["data-menu-level"];
    $depth = $data["#attributes"]["data-menu-level"];
    $parameters = new MenuTreeParameters();
    $parameters
      ->setMinDepth($level)
      ->setMaxDepth(min($level + $depth, $menu_tree->maxDepth()))
      ->onlyEnabledLinks();
    $tree = $menu_tree->load($menu_name, $parameters);
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $menu_tree->transform($tree, $manipulators);

    $element = $menu_tree->build($tree);
    $cacheability->merge(CacheableMetadata::createFromRenderArray($element))->applyTo($data);
    $data['#subtrees'] = $renderer->executeInRenderContext(new RenderContext(), function () use ($renderer, $element) {
      return $renderer->render($element);
    });

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['preRenderNavigationTray', 'preRenderGetRenderedSubtrees'];
  }

}
