<?php

namespace Drupal\block\Controller;

use Drupal\Core\Entity\Controller\EntityListController;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Defines a controller to list blocks.
 */
class BlockListController extends EntityListController {

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * Constructs the BlockListController.
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
   * Shows the block administration page.
   *
   * @param string|null $theme
   *   Theme key of block list.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return array
   *   A render array as expected by
   *   \Drupal\Core\Render\RendererInterface::render().
   */
  public function listing($theme = NULL, Request $request = NULL) {
    $theme = $theme ?: $this->config('system.theme')->get('default');
    if (!$this->themeHandler->hasUi($theme)) {
      throw new NotFoundHttpException();
    }

    return $this->entityTypeManager()->getListBuilder('block')->render($theme, $request);
  }

  /**
   * Provides a redirect to the block list for the default theme.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *
   * @deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use
   *   /admin/appearance/block directly instead of /admin/structure/block
   *
   * @see https://www.drupal.org/node/3318112
   */
  public function listingRedirect(): RedirectResponse {
    @trigger_error('The path /admin/structure/block is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use /admin/appearance/block. See https://www.drupal.org/node/3318112.', E_USER_DEPRECATED);
    $route = 'block.admin_display';
    $params = [
      '%old_path' => Url::fromRoute("$route.bc")->toString(),
      '%new_path' => Url::fromRoute($route)->toString(),
      '%change_record' => 'https://www.drupal.org/node/3320855',
    ];
    $warning_message = $this->t('You have been redirected from %old_path. Update links, shortcuts, and bookmarks to use %new_path.', $params);
    $this->messenger()->addWarning($warning_message);
    $this->getLogger('block')->warning('A user was redirected from %old_path to %new_path. This redirect will be removed in a future version of Drupal. Update links, shortcuts, and bookmarks to use %new_path. See %change_record for more information.', $params);

    return $this->redirect($route, [], [], 301);
  }

  /**
   * Provides a redirect to the block list for a theme.
   *
   * @param string $theme
   *   The name of the theme.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *
   * @deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use
   *   /admin/appearance/block/list/{theme} directly instead of
   *   /admin/structure/block/list/{theme}
   *
   * @see https://www.drupal.org/node/3318112
   */
  public function themeListingRedirect(string $theme): RedirectResponse {
    @trigger_error('The path /admin/structure/block/list/{theme} is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use /admin/appearance/block/list/{theme}. See https://www.drupal.org/node/3318112.', E_USER_DEPRECATED);
    $route = 'block.admin_display_theme';
    $params = [
      '%old_path' => Url::fromRoute("$route.bc", ['theme' => $theme])->toString(),
      '%new_path' => Url::fromRoute($route, ['theme' => $theme])->toString(),
      '%change_record' => 'https://www.drupal.org/node/3320855',
    ];
    $warning_message = $this->t('You have been redirected from %old_path. Update links, shortcuts, and bookmarks to use %new_path.', $params);
    $this->messenger()->addWarning($warning_message);
    $this->getLogger('block')->warning('A user was redirected from %old_path to %new_path. This redirect will be removed in a future version of Drupal. Update links, shortcuts, and bookmarks to use %new_path. See %change_record for more information.', $params);

    return $this->redirect($route, ['theme' => $theme], [], 301);
  }

}
