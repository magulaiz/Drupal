<?php

namespace Drupal\Core\Site;

use Drupal\Core\Extension\ThemeExtensionList;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Installer\InstallerKernel;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Theme\ThemeInitializationInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides the default implementation of the maintenance mode service.
 */
class MaintenanceMode implements MaintenanceModeInterface {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected Request $request;

  /**
   * Default theme name.
   *
   * @var string
   */
  protected static string $themeName = 'claro';

  /**
   * Static maintenance mode storage.
   *
   * @var string
   */
  protected static string $mode = self::MODE['offline'];

  /**
   * Constructs a new maintenance mode service.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The state.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface|null $themeHandler
   *   Default theme handler using the config system to store installation
   *   statuses.
   * @param \Drupal\Core\Theme\ThemeInitializationInterface|null $themeInitialization
   *   Provides the theme initialization logic.
   * @param \Drupal\Core\Theme\ThemeManagerInterface|null $themeManager
   *   Provides the high level access to the active theme and methods to use it.
   * @param \Drupal\Core\Extension\ThemeExtensionList|null $themeExtensionList
   *   Provides a list of available themes.
   * @param \Drupal\Core\Http\RequestStack|null $requestStack
   *   Request stack instance.
   * @param string|null $root
   *   Gets the app root from the kernel.
   */
  public function __construct(
    protected StateInterface                $state,
    ConfigFactoryInterface                  $config_factory,
    protected ?ThemeHandlerInterface        $themeHandler = NULL,
    protected ?ThemeInitializationInterface $themeInitialization = NULL,
    protected ?ThemeManagerInterface        $themeManager = NULL,
    protected ?ThemeExtensionList           $themeExtensionList = NULL,
    protected ?RequestStack                 $requestStack = NULL,
    protected ?string                       $root = NULL
  ) {
    $this->config = $config_factory;
    if ($this->themeHandler === NULL) {
      @trigger_error('Calling ' . __METHOD__ . '() without the $themeHandler argument is deprecated in drupal:10.1.0 and will be required in drupal:11.0.0', E_USER_DEPRECATED);
      $this->themeHandler = \Drupal::service('theme_handler');
    }
    if ($this->themeInitialization === NULL) {
      @trigger_error('Calling ' . __METHOD__ . '() without the $themeInitialization argument is deprecated in drupal:10.1.0 and will be required in drupal:11.0.0', E_USER_DEPRECATED);
      $this->themeInitialization = \Drupal::service('theme.initialization');
    }
    if ($this->themeManager === NULL) {
      @trigger_error('Calling ' . __METHOD__ . '() without the $themeManager argument is deprecated in drupal:10.1.0 and will be required in drupal:11.0.0', E_USER_DEPRECATED);
      $this->themeManager = \Drupal::service('theme.manager');
    }
    if ($this->themeExtensionList === NULL) {
      @trigger_error('Calling ' . __METHOD__ . '() without the $themeExtensionList argument is deprecated in drupal:10.1.0 and will be required in drupal:11.0.0', E_USER_DEPRECATED);
      $this->themeExtensionList = \Drupal::service('extension.list.theme');
    }
    if ($this->requestStack === NULL) {
      @trigger_error('Calling ' . __METHOD__ . '() without the $requestStack argument is deprecated in drupal:10.1.0 and will be required in drupal:11.0.0', E_USER_DEPRECATED);
      $this->requestStack = \Drupal::service('request_stack');
    }
    if ($this->root === NULL) {
      @trigger_error('Calling ' . __METHOD__ . '() without the $root argument is deprecated in drupal:10.1.0 and will be required in drupal:11.0.0', E_USER_DEPRECATED);
      $this->root = \Drupal::root();
    }
    $this->request = $this->requestStack->getCurrentRequest() ?? Request::createFromGlobals();
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    if (!$this->isEnabled()) {
      return FALSE;
    }

    if ($route = $route_match->getRouteObject()) {
      if ($route->getOption('_maintenance_access')) {
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function exempt(AccountInterface $account) {
    return $account->hasPermission('access site in maintenance mode');
  }

  /**
   * {@inheritdoc}
   */
  public function getSiteMaintenanceMessage() {
    return new FormattableMarkup($this->config->get('system.maintenance')->get('message'), [
      '@site' => $this->config->get('system.site')->get('name'),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function setTheme(): static {
    // If the theme is already set, assume the others are set too, and do
    // nothing.
    if ($this->themeManager->hasActiveTheme()) {
      return $this;
    }

    // $this->root required only for including legacy files.
    require_once $this->root . '/core/includes/theme.inc';
    require_once $this->root . '/core/includes/common.inc';
    require_once $this->root . '/core/includes/module.inc';

    // Install and update pages are treated differently to prevent theming
    // overrides.
    if ($this->isEnabled() && in_array(self::getMode(), [self::MODE['install'], self::MODE['update']], TRUE)) {
      if (InstallerKernel::installationAttempted()) {
        $custom_theme = $GLOBALS['install_state']['theme'];
      }
      else {
        $custom_theme = Settings::get(self::THEME_KEY, static::$themeName);
      }
    }
    else {
      // Use the maintenance theme if specified, otherwise attempt to use the
      // default site theme.
      try {
        $custom_theme = Settings::get(self::THEME_KEY, '');
        if (!$custom_theme) {
          $custom_theme = $this->config->get('system.theme')->get('default');
        }
      }
      catch (\Exception $e) {
        // Whatever went wrong (often a database connection problem), we are
        // about to fall back to a sensible theme so there is no need for
        // special handling.
      }
      if (!$custom_theme) {
        // We have been unable to identify the configured theme, so fall back to
        // a safe default. Claro is reasonably user-friendly and fairly
        // generic.
        $custom_theme = static::$themeName;
      }
    }

    $themes = $this->themeHandler->listInfo();

    // If no themes are installed yet, or if the requested custom theme is not
    // installed, retrieve all available themes.
    if (empty($themes) || !isset($themes[$custom_theme])) {
      $themes = $this->themeExtensionList->getList();
      $this->themeHandler->addTheme($themes[$custom_theme]);
    }

    // \Drupal\Core\Extension\ThemeHandlerInterface::listInfo() triggers a
    // \Drupal\Core\Extension\ModuleHandler::alter() in maintenance mode, but we
    // can't let themes alter the .info.yml data until we know a theme's base
    // themes. So don't set active theme until after
    // \Drupal\Core\Extension\ThemeHandlerInterface::listInfo() builds its
    // cache.
    $theme = $custom_theme;

    // Find all our ancestor themes and put them in an array.
    // @todo This is just a workaround. Find a better way how to handle themes
    //   on maintenance pages, see https://www.drupal.org/node/2322619.
    // This code is basically a duplicate of
    // \Drupal\Core\Theme\ThemeInitialization::getActiveThemeByName.
    $base_themes = [];
    $ancestor = $theme;
    while ($ancestor && isset($themes[$ancestor]->base_theme)) {
      $base_themes[] = $themes[$themes[$ancestor]->base_theme];
      $ancestor = $themes[$ancestor]->base_theme;
      if ($ancestor) {
        // Ensure that the base theme is added and installed.
        $this->themeHandler->addTheme($themes[$ancestor]);
      }
    }
    $this->themeManager->setActiveTheme($this->themeInitialization->getActiveTheme($themes[$custom_theme], $base_themes));
    // Prime the theme registry.
    \Drupal::service('theme.registry');
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function enable(): static {
    if ($this->request->hasSession()) {
      $this->request->getSession()->set(self::SESSION_KEY, TRUE);
    }
    $this->state->set(self::STATE_KEY, TRUE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function disable(): static {
    if ($this->request->hasSession()) {
      $this->request->getSession()->remove(self::SESSION_KEY);
    }
    $this->state->delete(self::STATE_KEY);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function setMode(string $mode = self::MODE['offline']): void {
    if (!in_array($mode, self::MODE, TRUE)) {
      throw new \RuntimeException(new TranslatableMarkup('Maintenance transferred into unexpected mode "@mode"', ['@mode' => $mode]));
    }
    self::$mode = $mode;
  }

  /**
   * {@inheritdoc}
   */
  public static function getMode(): string {
    if (defined('MAINTENANCE_MODE')) {
      @trigger_error('MAINTENANCE_MODE is deprecated in drupal:10.1.0 and removed in drupal:11.0.0. Use \Drupal\Core\Site\Maintenance::getMode() and \Drupal\Core\Site\Maintenance::setMode() instead. See https://www.drupal.org/node/3058979', E_USER_DEPRECATED);
      self::setMode(MAINTENANCE_MODE);
    }
    return self::$mode;
  }

  /**
   * {@inheritdoc}
   */
  public function isEnabled(): bool {
    $default = InstallerKernel::installationAttempted();
    $state = (bool) $this->state->get(self::STATE_KEY, $default);
    if ($this->request->hasSession()) {
      return $state && $this->request->getSession()->get(self::SESSION_KEY, $default);
    }
    return $state;
  }

}
