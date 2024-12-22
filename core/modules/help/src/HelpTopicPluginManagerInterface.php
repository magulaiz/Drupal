<?php

namespace Drupal\help;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Defines an interface for managing help topics and storing their definitions.
 *
 * @template-extends \Drupal\Component\Plugin\PluginManagerInterface<\Drupal\help\HelpTopicPluginInterface>
 */
interface HelpTopicPluginManagerInterface extends PluginManagerInterface {
}
