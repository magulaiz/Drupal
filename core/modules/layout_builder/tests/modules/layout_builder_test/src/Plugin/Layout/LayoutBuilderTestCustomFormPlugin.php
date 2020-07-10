<?php

namespace Drupal\layout_builder_test\Plugin\Layout;

use Drupal\Core\Layout\LayoutDefault;

/**
 * @Layout(
 *   id = "layout_builder_test_custom_form_plugin",
 *   label = @Translation("Layout Builder Test Custom Form Plugin"),
 *   regions = {
 *     "main" = {
 *       "label" = @Translation("Main Region")
 *     }
 *   },
 *   forms = {
 *     "configure" = "Drupal\layout_builder_test\PluginForm\CustomLayoutForm",
 *   },
 * )
 */
class LayoutBuilderTestCustomFormPlugin extends LayoutDefault {

}
