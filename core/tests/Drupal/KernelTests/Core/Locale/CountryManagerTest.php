<?php

namespace Drupal\KernelTests\Core\Locale;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests Country Manager functionality.
 *
 * @group CountryManager
 * @covers Drupal\Core\Locale\CountryManager
 */
class CountryManagerTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'language',
    'locale',
    'locale_test',
  ];

  /**
   * Tests that hook_countries_alters() works as expected.
   */
  public function testHookCountriesAlter(): void {
    $countries = $this->container->get('country_manager')->getList();
    self::assertArrayHasKey('EB', $countries);
    self::assertSame('Elbonia', $countries['EB']);
  }

  /**
   * Tests that function iso2ToIso3() works as expected.
   */
  public function testTwoCodeToAlphaThreeCode(): void {
    $iso3_codes = $this->container->get('country_manager')->iso2ToIso3();
    $expected_iso3_codes = [
      'AC' => 'ASC',
      'JO' => 'JOR',
    ];
    self::assertSame($expected_iso3_codes, $iso3_codes);
  }
}
