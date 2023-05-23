<?php

namespace Drupal\path_alias\PathProcessor;

use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Processes the inbound path using path alias lookups.
 */
class AliasPathProcessor implements InboundPathProcessorInterface, OutboundPathProcessorInterface {

  /**
   * An alias manager for looking up the system path.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * A config factory for retrieving required config settings.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Constructs a AliasPathProcessor object.
   *
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   An alias manager for looking up the system path.
   */
  public function __construct(AliasManagerInterface $alias_manager) {
    $this->aliasManager = $alias_manager;
    $this->config = \Drupal::service('config.factory');
  }

  /**
   * {@inheritdoc}
   */
  public function processInbound($path, Request $request) {
    $path = $this->aliasManager->getPathByAlias($path);
    return $path;
  }

  /**
   * {@inheritdoc}
   */
  public function processOutbound($path, &$options = [], Request $request = NULL, BubbleableMetadata $bubbleable_metadata = NULL) {
    if (empty($options['alias'])) {
      $langcode = isset($options['language']) ? $options['language']->getId() : NULL;
      $path = $this->aliasManager->getAliasByPath($path, $langcode);
      // Ensure the resulting path has at most one leading slash, to prevent it
      // becoming an external URL without a protocol like //example.com. This
      // is done in \Drupal\Core\Routing\UrlGenerator::generateFromRoute()
      // also, to protect against this problem in arbitrary path processors,
      // but it is duplicated here to protect any other URL generation code
      // that might call this method separately.
      if (str_starts_with($path, '//')) {
        $path = '/' . ltrim($path, '/');
      }
    }

    // Look for paths that are not already the front page.
    if ($path != '/') {
      $system_config = $this->config->get('system.site');
      if (!empty($system_config)) {
        $front = $system_config->get('page.front');
        $langcode = !empty($options['language']) ? $options['language']->getId() : NULL;

        // Get path and alias for the configured frontpage setting
        $alias_manager = \Drupal::service('path_alias.manager');
        $front_path = $alias_manager->getPathByAlias($front, $langcode);
        $front_alias = $alias_manager->getAliasByPath($front, $langcode);

        // Replace the path and alias with default frontpage path
        if (!empty($front_path) && $path === $front_path) {
          $path = '/';
        }
        if (!empty($front_alias) && $path === $front_alias) {
          $path = '/';
        }
      }
    }

    // The special path '<front>' links to the default front page.
    if ($path === '/<front>') {
      $path = '/';
    }

    return $path;
  }

}
