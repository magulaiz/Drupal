<?php

declare(strict_types=1);

namespace Drupal\Tests\jsonapi\Functional;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Url;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\user\Entity\User;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

// cspell:ignore èxample msword

/**
 * Tests binary data file upload route.
 *
 * @group jsonapi
 */
class FileUploadTest extends ResourceTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['entity_test', 'file'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   *
   * @see $entity
   */
  protected static $entityTypeId = 'entity_test';

  /**
   * {@inheritdoc}
   *
   * @see $entity
   */
  protected static $resourceTypeName = 'entity_test--entity_test';

  /**
   * The POST URI.
   *
   * @var string
   */
  protected static $postUri = '/jsonapi/entity_test/entity_test/field_rest_file_test';

  /**
   * Test file data.
   *
   * @var string
   */
  protected $testFileData = 'Hares sit on chairs, and mules sit on stools.';

  /**
   * The test field storage config.
   *
   * @var \Drupal\field\Entity\FieldStorageConfig
   */
  protected $fieldStorage;

  /**
   * The field config.
   *
   * @var \Drupal\field\Entity\FieldConfig
   */
  protected $field;

  /**
   * The parent entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * Created file entity.
   *
   * @var \Drupal\file\Entity\File
   */
  protected $file;

  /**
   * An authenticated user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * The entity storage for the 'file' entity type.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

  /**
   * A list of test methods to skip.
   *
   * @var array
   */
  const SKIP_METHODS = ['testGetIndividual', 'testIndividual', 'testCollection', 'testRelationships'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    if (in_array($this->name(), static::SKIP_METHODS, TRUE)) {
      $this->markTestSkipped('Irrelevant for this test');
    }

    parent::setUp();

    $this->fileStorage = $this->container->get('entity_type.manager')
      ->getStorage('file');

    // Add a file field.
    $this->fieldStorage = FieldStorageConfig::create([
      'entity_type' => 'entity_test',
      'field_name' => 'field_rest_file_test',
      'type' => 'file',
      'settings' => [
        'uri_scheme' => 'public',
      ],
    ])
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);
    $this->fieldStorage->save();

    $this->field = FieldConfig::create([
      'entity_type' => 'entity_test',
      'field_name' => 'field_rest_file_test',
      'bundle' => 'entity_test',
      'settings' => [
        'file_directory' => 'foobar',
        'file_extensions' => 'txt',
        'max_filesize' => '',
      ],
    ])
      ->setLabel('Test file field')
      ->setTranslatable(FALSE);
    $this->field->save();

    // Reload entity so that it has the new field.
    $this->entity = $this->entityStorage->loadUnchanged($this->entity->id());
  }

  /**
   * {@inheritdoc}
   */
  protected function createEntity() {
    // Create an entity that a file can be attached to.
    $entity_test = EntityTest::create([
      'name' => 'Llama',
      'type' => 'entity_test',
    ]);
    $entity_test->setOwnerId($this->account->id());
    $entity_test->save();

    return $entity_test;
  }

  /**
   * {@inheritdoc}
   */
  public function testRelated(): void {
    \Drupal::service('router.builder')->rebuild();
    parent::testRelated();
  }

  /**
   * Returns the JSON:API POST document referencing the uploaded file.
   *
   * @return array
   *   A JSON:API request document.
   *
   * @see ::testPostFileUpload()
   * @see \Drupal\Tests\jsonapi\Functional\EntityTestTest::getPostDocument()
   */
  protected function getPostDocument(): array {
    return [
      'data' => [
        'type' => 'entity_test--entity_test',
        'attributes' => [
          'name' => 'Drama llama',
        ],
        'relationships' => [
          'field_rest_file_test' => [
            'data' => [
              'id' => File::load(1)->uuid(),
              'meta' => [
                'description' => 'The most fascinating file ever!',
              ],
              'type' => 'file--file',
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getExpectedUnauthorizedAccessMessage($method): string {
    switch ($method) {
      case 'GET':
        return "The current user is not allowed to view this relationship. The 'view test entity' permission is required.";

      case 'POST':
        return "The current user is not permitted to upload a file for this field. The following permissions are required: 'administer entity_test content' OR 'administer entity_test_with_bundle content' OR 'create entity_test entity_test_with_bundle entities'.";

      case 'PATCH':
        return "The current user is not permitted to upload a file for this field. The 'administer entity_test content' permission is required.";
    }
    return '';
  }

  /**
   * Returns the expected JSON:API document for the expected file entity.
   *
   * @param int $fid
   *   The file ID to load and create a JSON:API document for.
   * @param string $expected_filename
   *   The expected filename for the stored file.
   * @param bool $expected_as_filename
   *   Whether the expected filename should be the filename property too.
   * @param bool $expected_status
   *   The expected file status. Defaults to FALSE.
   *
   * @return array
   *   A JSON:API response document.
   */
  protected function getExpectedDocument($fid = 1, $expected_filename = 'example.txt', $expected_as_filename = FALSE, $expected_status = FALSE): array {
    $author = User::load($this->account->id());
    $file = File::load($fid);
    $this->assertInstanceOf(File::class, $file);
    $self_url = Url::fromUri('base:/jsonapi/file/file/' . $file->uuid())->setAbsolute()->toString(TRUE)->getGeneratedUrl();

    return [
      'jsonapi' => [
        'meta' => [
          'links' => [
            'self' => ['href' => 'http://jsonapi.org/format/1.0/'],
          ],
        ],
        'version' => '1.0',
      ],
      'links' => [
        'self' => ['href' => $self_url],
      ],
      'data' => [
        'id' => $file->uuid(),
        'type' => 'file--file',
        'links' => [
          'self' => ['href' => $self_url],
        ],
        'attributes' => [
          'created' => (new \DateTime())->setTimestamp($file->getCreatedTime())->setTimezone(new \DateTimeZone('UTC'))->format(\DateTime::RFC3339),
          'changed' => (new \DateTime())->setTimestamp($file->getChangedTime())->setTimezone(new \DateTimeZone('UTC'))->format(\DateTime::RFC3339),
          'filemime' => 'text/plain',
          'filename' => $expected_as_filename ? $expected_filename : 'example.txt',
          'filesize' => strlen($this->testFileData),
          'langcode' => 'en',
          'status' => $expected_status,
          'uri' => [
            'value' => 'public://foobar/' . $expected_filename,
            'url' => base_path() . $this->siteDirectory . '/files/foobar/' . rawurlencode($expected_filename),
          ],
          'drupal_internal__fid' => (int) $file->id(),
        ],
        'relationships' => [
          'uid' => [
            'data' => [
              'id' => $author->uuid(),
              'meta' => [
                'drupal_internal__target_id' => (int) $author->id(),
              ],
              'type' => 'user--user',
            ],
            'links' => [
              'related' => ['href' => $self_url . '/uid'],
              'self' => ['href' => $self_url . '/relationships/uid'],
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * Performs a file upload request. Wraps the Guzzle HTTP client.
   *
   * @param \Drupal\Core\Url $url
   *   URL to request.
   * @param string $file_contents
   *   The file contents to send as the request body.
   * @param array $headers
   *   Additional headers to send with the request. Defaults will be added for
   *   Content-Type and Content-Disposition. In order to remove the defaults set
   *   the header value to FALSE.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The received response.
   *
   * @see \GuzzleHttp\ClientInterface::request()
   */
  protected function fileRequest(Url $url, $file_contents, array $headers = []): ResponseInterface {
    $request_options = [];
    $headers = $headers + [
      // Set the required (and only accepted) content type for the request.
      'Content-Type' => 'application/octet-stream',
      // Set the required Content-Disposition header for the file name.
      'Content-Disposition' => 'file; filename="example.txt"',
      // Set the required JSON:API Accept header.
      'Accept' => 'application/vnd.api+json',
    ];
    $request_options[RequestOptions::HEADERS] = array_filter($headers, function ($value) {
      return $value !== FALSE;
    });
    $request_options[RequestOptions::BODY] = $file_contents;
    $request_options = NestedArray::mergeDeep($request_options, $this->getAuthenticationRequestOptions());

    return $this->request('POST', $url, $request_options);
  }

  /**
   * {@inheritdoc}
   */
  protected function setUpAuthorization($method): void {
    switch ($method) {
      case 'GET':
        $this->grantPermissionsToTestedRole(['view test entity']);
        break;

      case 'POST':
        $this->grantPermissionsToTestedRole(['create entity_test entity_test_with_bundle entities', 'access content']);
        break;

      case 'PATCH':
        $this->grantPermissionsToTestedRole(['administer entity_test content', 'access content']);
        break;
    }
  }

  /**
   * Asserts expected normalized data matches response data.
   *
   * @param array $expected
   *   The expected data.
   * @param \Psr\Http\Message\ResponseInterface $response
   *   The file upload response.
   *
   * @internal
   */
  protected function assertResponseData(array $expected, ResponseInterface $response): void {
    static::recursiveKSort($expected);
    $actual = $this->getDocumentFromResponse($response);
    static::recursiveKSort($actual);

    $this->assertSame($expected, $actual);
  }

  /**
   * {@inheritdoc}
   */
  protected function getExpectedUnauthorizedAccessCacheability() {
    // There is cacheability metadata to check as file uploads only allows POST
    // requests, which will not return cacheable responses.
    return new CacheableMetadata();
  }

}
