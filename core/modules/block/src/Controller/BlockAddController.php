<?php

namespace Drupal\block\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for building the block instance add form.
 */
class BlockAddController extends ControllerBase {

  /**
   * Build the block instance add form.
   *
   * @param string $plugin_id
   *   The plugin ID for the block instance.
   * @param string $theme
   *   The name of the theme for the block instance.
   *
   * @return array
   *   The block instance edit form.
   */
  public function blockAddConfigureForm($plugin_id, $theme) {
    // Create a block entity.
    $entity = $this->entityTypeManager()->getStorage('block')->create(['plugin' => $plugin_id, 'theme' => $theme]);

    return $this->entityFormBuilder()->getForm($entity);
  }

  /**
   * Provides a redirect to the block instance edit form.
   *
   * @param string $plugin_id
   *   The plugin ID for the block instance.
   * @param string $theme
   *   The name of the theme.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *
   * @deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use
   *   /admin/appearance/block/add/{plugin_id}/{theme} directly instead of
   *   /admin/structure/block/add/{plugin_id}/{theme}
   *
   * @see https://www.drupal.org/node/3318112
   */
  public function blockAddConfigureFormRedirect(string $plugin_id, string $theme, Request $request): RedirectResponse {
    @trigger_error('The path /admin/structure/block/add/{plugin_id}/{theme} is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use /admin/appearance/block/add/{plugin_id}/{theme}. See https://www.drupal.org/node/3318112.', E_USER_DEPRECATED);
    $route = 'block.admin_add';
    $params = [
      '%old_path' => Url::fromRoute("$route.bc", ['plugin_id' => $plugin_id, 'theme' => $theme])->toString(),
      '%new_path' => Url::fromRoute($route, ['plugin_id' => $plugin_id, 'theme' => $theme])->toString(),
      '%change_record' => 'https://www.drupal.org/node/3320855',
    ];
    $warning_message = $this->t('You have been redirected from %old_path. Update links, shortcuts, and bookmarks to use %new_path.', $params);
    $this->messenger()->addWarning($warning_message);
    $this->getLogger('block')->warning('A user was redirected from %old_path to %new_path. This redirect will be removed in a future version of Drupal. Update links, shortcuts, and bookmarks to use %new_path. See %change_record for more information.', $params);

    $options = [];
    if ($region = $request->query->get('region')) {
      $options['query']['region'] = $region;
    }
    return $this->redirect($route, ['plugin_id' => $plugin_id, 'theme' => $theme], $options, 301);
  }

}
