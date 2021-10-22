<?php

namespace Drupal\datetime\Plugin\Field;

/**
 * Interface definition for field formatters and widgets that allow configuring
 * the timezone used.
 */
interface ConfigurableTimezoneInterface {

  /**
   * Timezone uses the site's timezone, regardless of the user's timezone.
   */
  const TIMEZONE_SITE = 'site';

  /**
   * Timezone uses the user's timezone.
   *
   * @see date_default_timezone_get()
   */
  const TIMEZONE_USER = 'user';

  /**
   * A fixed timezone is used.
   */
  const TIMEZONE_FIXED = 'none';

  /**
   * Get the time zone used as the default by a widget or formatter.
   *
   * This is determined using the widget or formatter settings and (if there
   * is one) the preferred time zone stored with the date item.
   *
   * @param string $itemTimezone
   *   A time zone stored in the field for this item, or NULL.
   *
   * @return string
   *   The default time zone.
   */
  public function getDefaultTimezone($itemTimezone);

}
