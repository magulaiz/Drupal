<?php

/**
 * @file
 * Provides a workaround for a bug in BrowserKitDriver that wrongly considers
 * as text of the page, pieces of texts inside the <head> section.
 *
 * @see https://github.com/minkphp/MinkBrowserKitDriver/issues/153
 * @see https://www.drupal.org/project/drupal/issues/3175718
 */

class_alias('\Drupal\Tests\DocumentElement', '\Behat\Mink\Element\DocumentElement', TRUE);
