<?php

declare(strict_types=1);

namespace Drupal\Tests\custom_module\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Tests the update-countries script.
 *
 * @group custom_module
 */
class UpdateCountriesTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system', 'locale'];

  /**
   * Test the update script's functionality.
   */
  public function testUpdateCountriesScript() {
    // Debug: Check if the database connection is available.
    $connection = \Drupal::database();
    if (!$connection) {
      $this->fail('Database connection is not available.');
    }

    $fs = new Filesystem();
    $jsonFile = DRUPAL_ROOT . '/territories.json';

    try {
      // Mock the territories.json file.
      $mockJson = [
        "main" => [
          "en" => [
            "localeDisplayNames" => [
              "territories" => [
                "US" => "United States",
                "CA" => "Canada",
                "EU" => "European Union",
                "ZZ" => "Unknown Region",
              ],
            ],
          ],
        ],
      ];
      file_put_contents($jsonFile, json_encode($mockJson));

      // Run the update script.
      $output = shell_exec("php " . DRUPAL_ROOT . "/core/scripts/update-countries.sh");

      // Check if expected updates were made.
      $countryManager = \Drupal::service('country_manager');
      $countries = $countryManager->getList();

      // Check that valid country codes exist.
      $this->assertArrayHasKey('US', $countries, "United States exists in the updated list.");
      $this->assertArrayHasKey('CA', $countries, "Canada exists in the updated list.");

      // Check that excluded country codes are not present.
      $this->assertArrayNotHasKey('EU', $countries, "European Union is correctly excluded.");
      $this->assertArrayNotHasKey('ZZ', $countries, "Unknown Region is correctly excluded.");
    } finally {
      // Clean up the mock JSON file.
      $fs->remove($jsonFile);
    }
  }

}
