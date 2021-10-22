<?php

namespace Drupal\Tests\datetime\Functional\EntityResource\EntityTest;

use Drupal\Core\Url;
use Drupal\Tests\rest\Functional\AnonResourceTestTrait;
use GuzzleHttp\RequestOptions;

/**
 * Tests datetime field constraint with datetime items using time zone storage.
 *
 * @group datetime
 */
class EntityTestDatetimeTimezoneStorageTest extends EntityTestDatetimeTest {

  use AnonResourceTestTrait;

  /**
   * The time zone string to use throughout the test.
   *
   * @var string
   */
  protected static $timezone = 'Australia/Sydney';

  /**
   * Boolean indicating whether or not to use time zone storage.
   *
   * @var boolean
   */
  protected static $timezoneStorage = TRUE;

  /**
   * {@inheritdoc}
   */
  protected function assertNormalizationEdgeCases($method, Url $url, array $request_options) {
    parent::assertNormalizationEdgeCases($method, $url, $request_options);

    if ($this->entity->getEntityType()->hasKey('bundle')) {
      $fieldName = static::$fieldName;

      // DX: 422 when time zone format is incorrect.
      $normalization = $this->getNormalizedPostEntity();
      $value = '2017-03-01T01:02:03+00:00';
      $timezone = 'Mars/Phobos';
      $normalization[static::$fieldName][0]['value'] = $value;
      $normalization[static::$fieldName][0]['timezone'] = $timezone;

      $request_options[RequestOptions::BODY] = $this->serializer->encode($normalization, static::$format);
      $response = $this->request($method, $url, $request_options);

      $message = "Unprocessable Entity: validation failed.\n{$fieldName}.0: The time zone value '{$timezone}' was not recognized as a valid time zone.\n";
      $this->assertResourceErrorResponse(422, $message, $response);
    }
  }

  /**
   * {@inheritdoc}
   *
   * @group legacy
   * @expectedDeprecation The provided datetime string format (Y-m-d\TH:i:s) is deprecated and will be removed before Drupal 9.0.0. Use the RFC3339 format instead (Y-m-d\TH:i:sP).
   */
  public function testPatch() {
    return parent::testPatch();
  }

}
