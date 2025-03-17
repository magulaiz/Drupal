<?php

declare(strict_types=1);

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

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['language', 'locale', 'locale_test'];

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container): void {
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
   * Tests the deprecation of locale_system_set_config_langcodes().
   *
   * @group legacy
   *
   * @see locale_system_set_config_langcodes()
   */
  public function testLocaleSystemSetConfigLangcodes(): void {
    $this->assertEquals('en', $this->config('locale_test.no_translation')->get('langcode'));
    $this->assertEquals('en', $this->config('locale_test.translation')->get('langcode'));
    $this->expectDeprecation(
      'locale_system_set_config_langcodes() is deprecated in drupal:11.2.0 and is removed from drupal:12.0.0.'
      . 'Use \Drupal\locale\LocaleConfigManager::updateDefaultConfigLangcodes() instead.'
      . 'See https://www.drupal.org/node/3350114'
    );
    locale_system_set_config_langcodes();
    $this->assertEquals('hu', $this->config('locale_test.no_translation')->get('langcode'));
    $this->assertEquals('hu', $this->config('locale_test.translation')->get('langcode'));
  }

}
