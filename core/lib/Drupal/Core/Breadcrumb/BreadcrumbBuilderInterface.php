<?php

namespace Drupal\Core\Breadcrumb;

use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Defines an interface for classes that build breadcrumbs.
 */
interface BreadcrumbBuilderInterface {

  /**
   * Whether this breadcrumb builder should be used to build the breadcrumb.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * phpcs:disable Drupal.Commenting
   * @todo Uncomment new method parameters before drupal:12.0.0.
   * @see https://www.drupal.org/project/drupal/issues/7654321
   *
   * @param \Drupal\Core\Cache\CacheableMetadata $cacheable_metadata
   *   The cacheable metadata to add to. If your result varies by something, you
   *   must specify the cache contexts that represent said variability. This
   *   will automatically be added to the outcome of build().
   * phpcs:enable
   *
   * @return bool
   *   TRUE if this builder should be used or FALSE to let other builders
   *   decide.
   */
  public function applies(RouteMatchInterface $route_match /* , CacheableMetadata $cacheable_metadata */);

  /**
   * Builds the breadcrumb.
   *
   * There is no need to add any cacheable metadata that was already added in
   * applies() as that will be automatically added for you.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   *
   * @return \Drupal\Core\Breadcrumb\Breadcrumb
   *   A breadcrumb.
   */
  public function build(RouteMatchInterface $route_match);

}
