<?php

namespace Drupal\system\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Theme suggestions for system.
 */
class SystemThemeSuggestionHook {

  /**
   * Implements hook_theme_suggestions_HOOK() for 'html'.
   */
  #[Hook('theme_suggestions_html')]
  public function themeSuggestionsHtml(array $variables): array {
    $path_args = explode('/', trim(\Drupal::service('path.current')->getPath(), '/'));
    return theme_get_suggestions($path_args, 'html');
  }

  /**
   * Implements hook_theme_suggestions_HOOK().
   */
  #[Hook('theme_suggestions_page')]
  public function themeSuggestionsPage(array $variables): array {
    $path_args = explode('/', trim(\Drupal::service('path.current')->getPath(), '/'));
    $suggestions = theme_get_suggestions($path_args, 'page');

    $supported_http_error_codes = [401, 403, 404];
    $exception = \Drupal::requestStack()->getCurrentRequest()->attributes->get('exception');
    if ($exception instanceof HttpExceptionInterface && in_array($exception->getStatusCode(), $supported_http_error_codes, TRUE)) {
      $suggestions[] = 'page__4xx';
      $suggestions[] = 'page__' . $exception->getStatusCode();
    }

    return $suggestions;
  }

  /**
   * Implements hook_theme_suggestions_HOOK().
   */
  #[Hook('theme_suggestions_maintenance_page')]
  public function themeSuggestionsMaintenancePage(array $variables): array {
    $suggestions = [];

    // Dead databases will show error messages so supplying this template will
    // allow themers to override the page and the content completely.
    $offline = defined('MAINTENANCE_MODE');
    try {
      \Drupal::service('path.matcher')->isFrontPage();
    }
    catch (\Exception) {
      // The database is not yet available.
      $offline = TRUE;
    }
    if ($offline) {
      $suggestions[] = 'maintenance_page__offline';
    }

    return $suggestions;
  }

  /**
   * Implements hook_theme_suggestions_HOOK().
   */
  #[Hook('theme_suggestions_region')]
  public function themeSuggestionsRegion(array $variables): array {
    $suggestions = [];
    if (!empty($variables['elements']['#region'])) {
      $suggestions[] = 'region__' . $variables['elements']['#region'];
    }
    return $suggestions;
  }

  /**
   * Implements hook_theme_suggestions_HOOK().
   */
  #[Hook('theme_suggestions_field')]
  public function themeSuggestionsField(array $variables): array {
    $suggestions = [];
    $element = $variables['element'];

    $suggestions[] = 'field__' . $element['#field_type'];
    $suggestions[] = 'field__' . $element['#field_name'];
    $suggestions[] = 'field__' . $element['#entity_type'] . '__' . $element['#bundle'];
    $suggestions[] = 'field__' . $element['#entity_type'] . '__' . $element['#field_name'];
    $suggestions[] = 'field__' . $element['#entity_type'] . '__' . $element['#field_name'] . '__' . $element['#bundle'];

    return $suggestions;
  }

}
