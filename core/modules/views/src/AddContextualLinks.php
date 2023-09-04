<?php

namespace Drupal\views;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Adds contextual links associated with a view display to a renderable array.
 */
class AddContextualLinks {

  /**
   * Constructs AddContextualLinks object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(protected ModuleHandlerInterface $moduleHandler, protected EntityTypeManagerInterface $entityTypeManager) {
  }

  /**
   * Adds contextual links associated with a view display to a renderable array.
   *
   * The function operates by checking the view's display plugin to see if it
   * has defined any contextual links that are intended to be displayed in the
   * requested location; if so, it attaches them. The contextual links intended
   * for a particular location are defined by the 'contextual links' and
   * 'contextual_links_locations' properties in the plugin annotation; as a
   * result, these hook implementations have full control over where and how
   * contextual links are rendered for each display.
   *
   * In addition to attaching the contextual links to the passed-in array (via
   * the standard #contextual_links property), this function also attaches
   * additional information via the #views_contextual_links_info property. This
   * stores an array whose keys are the names of each module that provided
   * views-related contextual links (same as the keys of the #contextual_links
   * array itself) and whose values are themselves arrays whose keys
   * ('location', 'view_name', and 'view_display_id') store the location, name
   * of the view, and display ID that were passed in to this function. This
   * allows you to access information about the contextual links and how they
   * were generated in a variety of contexts where you might be manipulating
   * the renderable array later on (for example, alter hooks which run later
   * during the same page request).
   *
   * @param $render_element
   *   The renderable array to which contextual links will be added. This array
   *   should be suitable for passing in to
   *   \Drupal\Core\Render\RendererInterface::render() and will normally
   *   contain a representation of the view display whose contextual links are
   *   being requested.
   * @param $location
   *   The location in which the calling function intends to render the view
   *   and its contextual links. The core system supports three options for
   *   this parameter:
   *   - block: Used when rendering a block which contains a view. This
   *     retrieves any contextual links intended to be attached to the block
   *     itself.
   *   - page: Used when rendering the main content of a page which contains
   *     a view. This retrieves any contextual links intended to be attached to
   *     the page itself (for example, links which are displayed directly next
   *     to the page title).
   *   - view: Used when rendering the view itself, in any context. This
   *     retrieves any contextual links intended to be attached directly to the
   *     view.
   *   If you are rendering a view and its contextual links in another
   *   location, you can pass in a different value for this parameter. However,
   *   you will also need to set 'contextual_links_locations' in your plugin
   *   annotation to indicate which view displays support having their
   *   contextual links rendered in the location you have defined.
   * @param string $display_id
   *   The ID of the display within $view whose contextual links will be added.
   * @param array $view_element
   *   The render array of the view. It should contain the following properties:
   *   - #view_id: The ID of the view.
   *   - #view_display_show_admin_links: A boolean whether the admin links
   *     should be shown.
   *   - #view_display_plugin_id: The plugin ID of the display.
   *
   * @see \Drupal\views\Plugin\Block\ViewsBlock::addContextualLinks()
   * @see template_preprocess_views_view()
   */
  public function viewsAddContextualLinks(&$render_element, $location, $display_id, array $view_element = NULL) {
    if (!isset($view_element)) {
      $view_element = $render_element;
    }
    $view_element['#cache_properties'] = ['view_id', 'view_display_show_admin_links', 'view_display_plugin_id'];
    $view_id = $view_element['#view_id'];
    $show_admin_links = $view_element['#view_display_show_admin_links'];
    $display_plugin_id = $view_element['#view_display_plugin_id'];

    // Do not do anything if the view is configured to hide its administrative
    // links or if the Contextual Links module is not enabled.
    if ($this->moduleHandler->moduleExists('contextual') && $show_admin_links) {
      // Also do not do anything if the display plugin has not defined any
      // contextual links that are intended to be displayed in the requested
      // location.
      $plugin = Views::pluginManager('display')->getDefinition($display_plugin_id);
      // If contextual_links_locations are not set, provide a sane default. (To
      // avoid displaying any contextual links at all, a display plugin can
      // still set 'contextual_links_locations' to, e.g., {""}.)

      if (!isset($plugin['contextual_links_locations'])) {
        $plugin['contextual_links_locations'] = ['view'];
      }
      elseif ($plugin['contextual_links_locations'] == [] || $plugin['contextual_links_locations'] == ['']) {
        $plugin['contextual_links_locations'] = [];
      }
      else {
        $plugin += ['contextual_links_locations' => ['view']];
      }

      // On exposed_forms blocks contextual links should always be visible.
      $plugin['contextual_links_locations'][] = 'exposed_filter';
      $has_links = !empty($plugin['contextual links']) && !empty($plugin['contextual_links_locations']);
      if ($has_links && in_array($location, $plugin['contextual_links_locations'])) {
        foreach ($plugin['contextual links'] as $group => $link) {
          $args = [];
          $valid = TRUE;
          if (!empty($link['route_parameters_names'])) {
            $view_storage = $this->entityTypeManager
              ->getStorage('view')
              ->load($view_id);
            foreach ($link['route_parameters_names'] as $parameter_name => $property) {
              // If the plugin is trying to create an invalid contextual link
              // (for example, "path/to/{$view->storage->property}", where
              // $view->storage->{property} does not exist), we cannot construct
              // the link, so we skip it.
              if (!property_exists($view_storage, $property)) {
                $valid = FALSE;
                break;
              }
              else {
                $args[$parameter_name] = $view_storage->get($property);
              }
            }
          }
          // If the link was valid, attach information about it to the
          // renderable array.
          if ($valid) {
            $render_element['#views_contextual_links'] = TRUE;
            $render_element['#contextual_links'][$group] = [
              'route_parameters' => $args,
              'metadata' => [
                'location' => $location,
                'name' => $view_id,
                'display_id' => $display_id,
              ],
            ];
            // If we're setting contextual links on a page, for a page view,
            // for a user that may use contextual links, attach Views'
            // contextual links JavaScript.
            $render_element['#cache']['contexts'][] = 'user.permissions';
          }
        }
      }
    }
  }

}
