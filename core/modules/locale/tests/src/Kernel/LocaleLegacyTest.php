<?php

namespace Drupal\Tests\locale\Kernel;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Language\Language;
use Drupal\KernelTests\KernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;

/**
 * Tests legacy locale module code.
 *
 * @group locale
 * @group legacy
 */
class LocaleLegacyTest extends KernelTestBase {

  protected static $modules = ['language', 'locale', 'locale_test'];

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    parent::register($container);

    $language = Language::$defaultValues;
    $language['id'] = 'hu';
    $language['name'] = 'Hungarian';
    $container->setParameter('language.default_values', $language);
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    ConfigurableLanguage::createFromLangcode('hu')->save();
    $this->installSchema('locale', ['locales_source', 'locales_target', 'locales_location']);
    $this->installConfig(['locale_test']);
  }

  /**
   * Tests locale_system_set_config_langcodes().
   */
  public function testLocaleSystemSetConfigLangcodes() {
    $this->assertNull($this->config('locale_test.no_translation')->get('langcode'));
    $this->assertNull($this->config('locale_test.translation')->get('langcode'));
    $this->expectDeprecation('locale_system_set_config_langcodes() is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use \Drupal\locale\LocaleConfigManager::updateDefaultConfigLangcodes() instead. See https://www.drupal.org/node/3350114');
    \Drupal::service('locale.config_manager')->updateDefaultConfigLangcodes();
    $this->assertEquals('hu', $this->config('locale_test.no_translation')->get('langcode'));
    $this->assertEquals('hu', $this->config('locale_test.translation')->get('langcode'));
  }

}
