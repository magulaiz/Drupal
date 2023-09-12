<?php

namespace Drupal\views\Plugin\views\display;

use Symfony\Component\HttpFoundation\Response;

/**
 * Defines a trait for response display plugins.
 *
 * This trait is meant to be used for display plugins that implement
 * ResponseDisplayPluginInterface.
 */
trait ResponseDisplayPluginTrait {

  /**
   * Alter the response.
   *
   * @param \Symfony\Component\HttpFoundation\Response $response
   *   The response.
   * @param $view_id
   *   The view id.
   * @param $display_id
   *   The view display id.
   */
  public static function alterResponse(Response $response, $view_id, $display_id) {
    $view_config = \Drupal::entityTypeManager()->getStorage('view')->load($view_id);
    $display_config = [];
    if ($view_config) {
      $display_config = $view_config->getDisplay($display_id);
    }

    if ($display_config && !empty($display_config['display_options']['style']['type'])) {
      $style_plugin = \Drupal::service('plugin.manager.views.style')->getDefinition($display_config['display_options']['style']['type']);
      if ($style_plugin &&
          !empty($style_plugin['class']) &&
          is_subclass_of($style_plugin['class'], 'Drupal\views\Plugin\views\style\ResponseStylePluginInterface')) {
        $style_plugin_class = $style_plugin['class'];
        $style_plugin_class::alterResponse($response);
      }
    }
  }

}
