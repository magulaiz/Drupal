<?php

namespace Drupal\datetime\Hook;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for datetime.
 */
class DatetimeHooks {

  use StringTranslationTrait;

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name, RouteMatchInterface $route_match): ?string {
    switch ($route_name) {
      case 'help.page.datetime':
        $output = '';
        $output .= '<h2>' . $this->t('About') . '</h2>';
        $output .= '<p>' . $this->t('The Datetime module provides a Date field that stores dates and times. It also provides the Form API elements <em>datetime</em> and <em>datelist</em> for use in programming modules. See the <a href=":field">Field module help</a> and the <a href=":field_ui">Field UI module help</a> pages for general information on fields and how to create and manage them.', [
          ':field' => Url::fromRoute('help.page', ['name' => 'field'])->toString(),
          ':field_ui' => \Drupal::moduleHandler()->moduleExists('field_ui') ? Url::fromRoute('help.page', ['name' => 'field_ui'])->toString() : '#',
        ]) . '</p>';

        $output .= '<h2>' . $this->t('Handling Timezones') . '</h2>';
        $output .= '<p>' . $this->t('Drupal stores all Date fields in UTC format. When a user enters a date, Drupal converts it to UTC based on their timezone. During display:') . '</p>';
        $output .= '<ul>';
        $output .= '<li>' . $this->t('<strong>Logged-in users</strong> see the date in their account’s timezone.') . '</li>';
        $output .= '<li>' . $this->t('<strong>Anonymous users</strong> see the date in the site’s default timezone.') . '</li>';
        $output .= '</ul>';

        $output .= '<h2>' . $this->t('Configuring Display') . '</h2>';
        $output .= '<p>' . $this->t('Use the Date format settings to control how dates are displayed, ensuring clarity for multi-timezone audiences.') . '</p>';

        return $output;
    }
    return NULL;
  }

}
