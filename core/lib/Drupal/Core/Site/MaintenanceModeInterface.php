<?php

namespace Drupal\Core\Site;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the interface for the maintenance mode service.
 */
interface MaintenanceModeInterface {

  /**
   * The state options of the enabled maintenance.
   */
  public const MODE = [
    'offline' => NULL,
    'install' => 'install',
    'update' => 'update',
  ];

  /**
   * State storage key.
   */
  public const STATE_KEY = 'system.maintenance_mode';

  /**
   * Session storage key.
   */
  public const SESSION_KEY = 'maintenance_mode';

  /**
   * Settings key where selected maintenance theme id is stored.
   */
  public const THEME_KEY = 'maintenance_theme';

  /**
   * Returns whether the site is in maintenance mode.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   *
   * @return bool
   *   TRUE if the site is in maintenance mode.
   */
  public function applies(RouteMatchInterface $route_match);

  /**
   * Determines whether a user has access to the site in maintenance mode.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The logged in user.
   *
   * @return bool
   *   TRUE if the user should be exempted from maintenance mode.
   */
  public function exempt(AccountInterface $account);

  /**
   * Gets the site maintenance message.
   *
   * @return \Drupal\Component\Render\MarkupInterface
   *   The formatted site maintenance message.
   */
  public function getSiteMaintenanceMessage();

  /**
   * Sets up the theming system for maintenance page.
   *
   * Used for site installs, updates and when the site is in maintenance mode.
   * It also applies when the database is unavailable or bootstrap was not
   * complete. Seven is always used for the initial install and update
   * operations. In other cases, Bartik is used, but this can be overridden by
   * setting a "maintenance_theme" key in the $settings variable in
   * settings.php.
   */
  public function setTheme();

  /**
   * Set flags that maintenance mode is enabled.
   *
   * @return self
   */
  public function enable();

  /**
   * Set flags that maintenance mode is disabled.
   *
   * @return self
   */
  public function disable();

  /**
   * Set the maintenance mode state.
   *
   * The mode state is empty by the default. But for specific cases it can be
   * set for 'install' or 'update' mode state.
   *
   * @param null|string $mode
   *   Mode value.
   *
   * @throws \Exception
   *
   * @see \Drupal\Core\Site\MaintenanceMode::MODE
   */
  public static function setMode($mode = self::MODE['offline']);

  /**
   * Returns the maintenance mode state.
   *
   * The mode state is empty by the default. But for specific cases it can be
   * set for 'install' or 'update' mode state.
   *
   * @return string|null
   *   Current stored mode state.
   *
   * @throws \Exception
   *
   * @see \Drupal\Core\Site\MaintenanceMode::MODE
   */
  public static function getMode();

  /**
   * Returns state of the maintenance.
   *
   * @return bool
   *   Returns state of the maintenance:
   *   - TRUE if maintenance mode enabled and site is offline;
   *   - FALSE if maintenance mode disabled and site is online;
   */
  public function isEnabled();

}
