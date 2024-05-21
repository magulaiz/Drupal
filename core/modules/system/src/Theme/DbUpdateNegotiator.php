<?php

namespace Drupal\system\Theme;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\Theme\ThemeNegotiatorInterface;

/**
 * Sets the active theme for the database update pages.
 */
class DbUpdateNegotiator implements ThemeNegotiatorInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * Constructs a DbUpdateNegotiator.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ThemeHandlerInterface $theme_handler) {
    $this->configFactory = $config_factory;
    $this->themeHandler = $theme_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    return $route_match->getRouteName() == 'system.db_update';
  }

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    $active_theme = Settings::get('maintenance_theme');

    // The information checks below would fail when falling back to the Claro
    // theme if said theme isn't enabled on the site, so we return early here.
    if (!$active_theme) {
      return 'claro';
    }

    // Check if the maintenance theme relies on any modules and, if it does,
    // fall back to Claro instead as update.php should be rendered with as few
    // modules as possible. See Drupal\Core\Theme\Registry::get() on how we only
    // allow core theme implementations on the update page.
    $list_info = $this->themeHandler->listInfo();
    $theme_info = $list_info[$active_theme]->info;
    if (!empty($theme_info['dependencies'])) {
      foreach (array_filter($theme_info['dependencies']) as $dependency) {
        if (empty($list_info[$dependency])) {
          return 'claro';
        }
      }
    }

    return $active_theme;
  }

}
