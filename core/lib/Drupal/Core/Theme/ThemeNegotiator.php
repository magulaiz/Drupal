<?php

namespace Drupal\Core\Theme;

use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Provides a class which determines the active theme of the page.
 *
 * It therefore uses ThemeNegotiatorInterface objects which are passed in
 * using the 'theme_negotiator' tag.
 */
class ThemeNegotiator implements ThemeNegotiatorInterface {

  /**
   * Holds an array of theme negotiator IDs, sorted by priority.
   *
   * @var string[]
   */
  protected $negotiators = [];

  /**
   * The access checker for themes.
   *
   * @var \Drupal\Core\Theme\ThemeAccessCheck
   */
  protected $themeAccess;

  /**
   * Constructs a new ThemeNegotiator.
   *
   * @param \Drupal\Core\Theme\ThemeAccessCheck $theme_access
   *   The access checker for themes.
   * @param string[] $negotiators
   *   An array of negotiator IDs.
   */
  public function __construct(ThemeAccessCheck $theme_access, iterable $negotiators) {
    $this->themeAccess = $theme_access;
    $this->negotiators = $negotiators;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    foreach ($this->negotiators as $negotiator) {
      if ($negotiator->applies($route_match)) {
        $theme = $negotiator->determineActiveTheme($route_match);
        if ($theme !== NULL && $this->themeAccess->checkAccess($theme)) {
          return $theme;
        }
      }
    }
  }

}
