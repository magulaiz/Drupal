<?php

namespace Drupal\layout_deprecation_test\Plugin\Layout;

use Drupal\Core\Layout\LayoutDefault;

/**
 * The plugin that handles the default layout template.
 *
 * @Layout(
 *   id = "layout_deprecated",
 *   deprecation_message = "layout_deprecated is deprecated",
 * )
 */
class LayoutDeprecated extends LayoutDefault {}
