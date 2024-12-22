<?php

namespace Drupal\Core\Block;

use Drupal\Component\Plugin\CategorizingPluginManagerInterface;
use Drupal\Core\Plugin\Context\ContextAwarePluginManagerInterface;
use Drupal\Core\Plugin\FilteredPluginManagerInterface;

/**
 * Provides an interface for the discovery and instantiation of block plugins.
 *
 * @template-extends ContextAwarePluginManagerInterface<\Drupal\Core\Block\BlockPluginInterface>
 * @template-extends CategorizingPluginManagerInterface<\Drupal\Core\Block\BlockPluginInterface>
 * @template-extends FilteredPluginManagerInterface<\Drupal\Core\Block\BlockPluginInterface>
 */
interface BlockManagerInterface extends ContextAwarePluginManagerInterface, CategorizingPluginManagerInterface, FilteredPluginManagerInterface {

}
