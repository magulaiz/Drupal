<?php

namespace Drupal\navigation;

use Drupal\Core\Block\BlockPluginInterface;

/**
 * Defines the required interface for all navigation block plugins.
 *
 * @todo Do we break the coupling here and flesh out a independent block-like
 *   plugin interface here?
 */
interface NavigationBlockPluginInterface extends BlockPluginInterface {
}
