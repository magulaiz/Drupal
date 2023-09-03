<?php

namespace Drupal\update;

/**
 * Defines the Mailer interface for update module.
 */
interface UpdateMailerInterface {

  /**
   * Performs any notifications that should be done once cron fetches new data.
   *
   * This method checks the status of the site using the new data and,
   * depending on the configuration of the site, notifies administrators via
   * email if there are new releases or missing security updates.
   *
   * @see update_requirements()
   */
  public function cronNotify();

}
