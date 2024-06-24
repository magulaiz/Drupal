<?php

declare(strict_types=1);

namespace Drupal\Tests\locale\Unit;

use Drupal\Tests\UnitTestCase;

/**
 * Tests locale.bulk.inc.
 *
 * @group locale
 */
class LocaleBulkTest extends UnitTestCase {

  protected function setUp(): void {
    parent::setUp();

    include_once DRUPAL_ROOT . '/core/modules/locale/locale.bulk.inc';
  }

  /**
   * Tests the deprecation of locale_config_batch_refresh_name().
   *
   * @group legacy
   *
   * @see locale_config_batch_refresh_name()
   */
  public function testDeprecatedLocaleConfigBatchRefreshName() {
    $this->expectDeprecation('locale_config_batch_refresh_name() is deprecated in drupal:10.2.3 and is removed from drupal:11.0.0. Use locale_config_batch_update_config_translations() instead. See https://www.drupal.org/project/drupal/issues/3422977');
    $names = ['English', 'German'];
    $langcodes = ['en', 'de'];
    \locale_config_batch_refresh_name($names, $langcodes, $context);
  }

  /**
   * Tests the deprecation of locale_config_batch_set_config_langcodes().
   *
   * @group legacy
   *
   * @see locale_config_batch_set_config_langcodes()
   */
  public function testDeprecatedLocaleConfigBatchSetConfigLangcodes() {
    $this->expectDeprecation('locale_config_batch_set_config_langcodes() is deprecated in drupal:10.2.3 and is removed from drupal:11.0.0. Use locale_config_batch_update_config_translations() instead. See https://www.drupal.org/project/drupal/issues/3422977');
    $context = [];
    locale_config_batch_set_config_langcodes($context);
  }

}
