<?php

declare(strict_types = 1);

namespace Drupal\path_alias\Plugin\Condition;

use Drupal\path_alias\AliasManagerInterface;
use Drupal\system\Plugin\Condition\RequestPath;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Adds path alias matching to the 'Request Path' condition.
 */
class PathAliasRequestPath extends RequestPath {

  /**
   * An alias manager to find the alias for the current system path.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected AliasManagerInterface $aliasManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->aliasManager = $container->get('path_alias.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate(): bool {
    // Don't bother checking path aliases if the internal path already matches.
    if (parent::evaluate()) {
      return TRUE;
    }

    // Convert path to lowercase. This allows comparison of the same path
    // with different case. Ex: /Page, /page, /PAGE.
    $pages = mb_strtolower($this->configuration['pages']);
    $request = $this->requestStack->getCurrentRequest();

    // Compare the lowercase path alias (if any) and internal path.
    $path = $this->currentPath->getPath($request);
    // Do not trim a trailing slash if that is the complete path.
    $path = $path === '/' ? $path : rtrim($path, '/');
    $path_alias = mb_strtolower($this->aliasManager->getAliasByPath($path));

    return $this->pathMatcher->matchPath($path_alias, $pages);
  }

}
