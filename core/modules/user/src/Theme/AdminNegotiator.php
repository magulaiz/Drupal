<?php

namespace Drupal\user\Theme;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;

/**
 * Sets the active theme on admin pages.
 */
class AdminNegotiator implements ThemeNegotiatorInterface {

  /**
   * Creates a new AdminNegotiator instance.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The current user.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Routing\AdminContext $adminContext
   *   The route admin context to determine whether the route is an admin one.
   */
  public function __construct(protected AccountInterface $user, protected ConfigFactoryInterface $configFactory, protected EntityTypeManagerInterface $entityTypeManager, protected AdminContext $adminContext)
  {
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    return ($this->entityTypeManager->hasHandler('user_role', 'storage') && $this->user->hasPermission('view the administration theme') && $this->adminContext->isAdminRoute($route_match->getRouteObject()));
  }

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    return $this->configFactory->get('system.theme')->get('admin') ?: NULL;
  }

}
