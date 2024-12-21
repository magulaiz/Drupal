<?php

declare(strict_types=1);

namespace Drupal\Tests\jsonapi\Functional;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Access\AccessResultReasonInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\Cache\CacheRedirect;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\ContentEntityNullStorage;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Url;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\jsonapi\JsonApiResource\JsonApiDocumentTopLevel;
use Drupal\jsonapi\ResourceResponse;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\jsonapi\Traits\GetDocumentFromResponseTrait;
use GuzzleHttp\RequestOptions;

/**
 * Subclass this for every JSON:API resource type.
 */
abstract class ResourceTestBase extends BrowserTestBase {

  use ResourceTestTrait;
  use GetDocumentFromResponseTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'jsonapi',
    'basic_auth',
    'rest_test',
    'jsonapi_test_field_access',
    'text',
  ];

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
   * Whether the tested JSON:API resource is versionable.
   *
   * @var bool
   */
  protected static $resourceTypeIsVersionable = FALSE;

  /**
   * The POST URI.
   *
   * @var string
   */
  protected static $postUri = '/jsonapi/entity_test/entity_test/field_rest_file_test';

  protected function setUp(): void {
    parent::setUp();
    $this->doSetUp();
  }

  /**
   * Tests GETting an individual resource, plus edge cases to ensure good DX.
   */
  public function testGetIndividual(): void {
    // The URL and Guzzle request options that will be used in this test. The
    // request options will be modified/expanded throughout this test:
    // - to first test all mistakes a developer might make, and assert that the
    //   error responses provide a good DX
    // - to eventually result in a well-formed request that succeeds.
    // @todo Remove line below in favor of commented line in https://www.drupal.org/project/drupal/issues/2878463.
    $url = Url::fromRoute(sprintf('jsonapi.%s.individual', static::$resourceTypeName), ['entity' => $this->entity->uuid()]);
    // $url = $this->entity->toUrl('jsonapi');
    $request_options = [];
    $request_options[RequestOptions::HEADERS]['Accept'] = 'application/vnd.api+json';
    $request_options = NestedArray::mergeDeep($request_options, $this->getAuthenticationRequestOptions());

    // DX: 403 when unauthorized, or 200 if the 'view label' operation is
    // supported by the entity type.
    $response = $this->request('GET', $url, $request_options);
    $dynamic_cache_label_only = NULL;
    if (!static::$anonymousUsersCanViewLabels) {
      $expected_403_cacheability = $this->getExpectedUnauthorizedAccessCacheability();
      $reason = $this->getExpectedUnauthorizedAccessMessage('GET');
      $message = trim("The current user is not allowed to GET the selected resource. $reason");
      $this->assertResourceErrorResponse(403, $message, $url, $response, '/data', $expected_403_cacheability->getCacheTags(), $expected_403_cacheability->getCacheContexts(), 'UNCACHEABLE (request policy)', TRUE);
      $this->assertArrayNotHasKey('Link', $response->getHeaders());
    }
    else {
      $expected_document = $this->getExpectedDocument();
      $label_field_name = $this->entity->getEntityType()->hasKey('label') ? $this->entity->getEntityType()->getKey('label') : static::$labelFieldName;
      $expected_document['data']['attributes'] = array_intersect_key($expected_document['data']['attributes'], [$label_field_name => TRUE]);
      unset($expected_document['data']['relationships']);
      $dynamic_cache_label_only = $this->generateDynamicPageCacheExpectedHeaderValue($this->getExpectedCacheContexts([$label_field_name]));
      $this->assertResourceResponse(200, $expected_document, $response, $this->getExpectedCacheTags(), $this->getExpectedCacheContexts([$label_field_name]), 'UNCACHEABLE (request policy)', TRUE);
    }

    $this->setUpAuthorization('GET');

    // Set body despite that being nonsensical: should be ignored.
    $request_options[RequestOptions::BODY] = Json::encode($this->getExpectedDocument());

    // 400 for GET request with reserved custom query parameter.
    $url_reserved_custom_query_parameter = clone $url;
    $url_reserved_custom_query_parameter = $url_reserved_custom_query_parameter->setOption('query', ['foo' => 'bar']);
    $response = $this->request('GET', $url_reserved_custom_query_parameter, $request_options);
    $expected_document = [
      'jsonapi' => static::$jsonApiMember,
      'errors' => [
        [
          'title' => 'Bad Request',
          'status' => '400',
          'detail' => "The following query parameters violate the JSON:API spec: 'foo'.",
          'links' => [
            'info' => ['href' => 'http://jsonapi.org/format/#query-parameters'],
            'via' => ['href' => $url_reserved_custom_query_parameter->toString()],
          ],
        ],
      ],
    ];
    $this->assertResourceResponse(400, $expected_document, $response, ['4xx-response', 'http_response'], ['url.query_args', 'url.site'], 'UNCACHEABLE (request policy)', TRUE);

    // 200 for well-formed HEAD request.
    $response = $this->request('HEAD', $url, $request_options);
    $this->assertResourceResponse(200, NULL, $response, $this->getExpectedCacheTags(), $this->getExpectedCacheContexts(), 'UNCACHEABLE (request policy)', TRUE);
    $head_headers = $response->getHeaders();

    // 200 for well-formed GET request. Page Cache hit because of HEAD request.
    // Same for Dynamic Page Cache hit.
    $response = $this->request('GET', $url, $request_options);

    $this->assertResourceResponse(200, $this->getExpectedDocument(), $response, $this->getExpectedCacheTags(), $this->getExpectedCacheContexts(), 'UNCACHEABLE (request policy)', $this->generateDynamicPageCacheExpectedHeaderValue($this->getExpectedCacheContexts()) === 'MISS' ? 'HIT' : 'UNCACHEABLE (poor cacheability)');
    // Assert that Dynamic Page Cache did not store a ResourceResponse object,
    // which needs serialization after every cache hit. Instead, it should
    // contain a flattened response. Otherwise performance suffers.
    // @see \Drupal\jsonapi\EventSubscriber\ResourceResponseSubscriber::flattenResponse()
    $cache_items = $this->container->get('database')
      ->select('cache_dynamic_page_cache', 'cdp')
      ->fields('cdp', ['data'])
      ->condition('cid', '%[route]=jsonapi.%', 'LIKE')
      ->execute()
      ->fetchAll();
    $this->assertLessThanOrEqual(5, count($cache_items));
    $found_cached_200_response = FALSE;
    $other_cached_responses_are_4xx = TRUE;
    foreach ($cache_items as $cache_item) {
      $cached_response = unserialize($cache_item->data);
      if (!$cached_response instanceof CacheRedirect) {
        if ($cached_response->getStatusCode() === 200) {
          $found_cached_200_response = TRUE;
        }
        elseif (!$cached_response->isClientError()) {
          $other_cached_responses_are_4xx = FALSE;
        }
        $this->assertNotInstanceOf(ResourceResponse::class, $cached_response);
        $this->assertInstanceOf(CacheableResponseInterface::class, $cached_response);
      }
    }
    $this->assertSame($this->generateDynamicPageCacheExpectedHeaderValue($this->getExpectedCacheContexts()) !== 'UNCACHEABLE (poor cacheability)' || $dynamic_cache_label_only !== 'UNCACHEABLE (poor cacheability)', $found_cached_200_response);
    $this->assertTrue($other_cached_responses_are_4xx);

    // Not only assert the normalization, also assert deserialization of the
    // response results in the expected object.
    $unserialized = $this->serializer->deserialize((string) $response->getBody(), JsonApiDocumentTopLevel::class, 'api_json', [
      'target_entity' => static::$entityTypeId,
      'resource_type' => $this->container->get('jsonapi.resource_type.repository')->getByTypeName(static::$resourceTypeName),
    ]);
    $this->assertSame($unserialized->uuid(), $this->entity->uuid());
    $get_headers = $response->getHeaders();

    // Verify that the GET and HEAD responses are the same. The only difference
    // is that there's no body. For this reason the 'Transfer-Encoding' and
    // 'Vary' headers are also added to the list of headers to ignore, as they
    // may be added to GET requests, depending on web server configuration. They
    // are usually 'Transfer-Encoding: chunked' and 'Vary: Accept-Encoding'.
    $ignored_headers = [
      'Date',
      'Content-Length',
      'X-Drupal-Cache',
      'X-Drupal-Dynamic-Cache',
      'Transfer-Encoding',
      'Vary',
    ];
    $header_cleaner = function ($headers) use ($ignored_headers) {
      foreach ($headers as $header => $value) {
        if (str_starts_with($header, 'X-Drupal-Assertion-') || in_array($header, $ignored_headers)) {
          unset($headers[$header]);
        }
      }
      return $headers;
    };
    $get_headers = $header_cleaner($get_headers);
    $head_headers = $header_cleaner($head_headers);
    $this->assertSame($get_headers, $head_headers);

    // Feature: Sparse fieldsets.
    $this->doTestSparseFieldSets($url, $request_options);
    // Feature: Included.
    $this->doTestIncluded($url, $request_options);

    // DX: 404 when GETting non-existing entity.
    $random_uuid = \Drupal::service('uuid')->generate();
    $url = Url::fromRoute(sprintf('jsonapi.%s.individual', static::$resourceTypeName), ['entity' => $random_uuid]);
    $response = $this->request('GET', $url, $request_options);
    $message_url = clone $url;
    $path = str_replace($random_uuid, '{entity}', $message_url->setAbsolute()->setOptions(['base_url' => '', 'query' => []])->toString());
    $message = 'The "entity" parameter was not converted for the path "' . $path . '" (route name: "jsonapi.' . static::$resourceTypeName . '.individual")';
    $this->assertResourceErrorResponse(404, $message, $url, $response, FALSE, ['4xx-response', 'http_response'], ['url.query_args', 'url.site'], 'UNCACHEABLE (request policy)', 'UNCACHEABLE (poor cacheability)');

    // DX: when Accept request header is missing, still 404, same response.
    unset($request_options[RequestOptions::HEADERS]['Accept']);
    $response = $this->request('GET', $url, $request_options);
    $this->assertResourceErrorResponse(404, $message, $url, $response, FALSE, ['4xx-response', 'http_response'], ['url.query_args', 'url.site'], 'UNCACHEABLE (request policy)', 'UNCACHEABLE (poor cacheability)');
  }

  /**
   * Tests GETting a collection of resources.
   */
  public function testCollection(): void {
    $entity_collection = $this->getData();
    assert(count($entity_collection) > 1, 'A collection must have more that one entity in it.');

    $collection_url = Url::fromRoute(sprintf('jsonapi.%s.collection', static::$resourceTypeName))->setAbsolute(TRUE);
    $request_options = [];
    $request_options[RequestOptions::HEADERS]['Accept'] = 'application/vnd.api+json';
    $request_options = NestedArray::mergeDeep($request_options, $this->getAuthenticationRequestOptions());

    // This asserts that collections will work without a sort, added by default
    // below, without actually asserting the content of the response.
    $expected_response = $this->getExpectedCollectionResponse($entity_collection, $collection_url->toString(), $request_options);
    $expected_cacheability = $expected_response->getCacheableMetadata();
    $response = $this->request('HEAD', $collection_url, $request_options);
    $this->assertResourceResponse(200, NULL, $response, $expected_cacheability->getCacheTags(), $expected_cacheability->getCacheContexts(), 'UNCACHEABLE (request policy)', $this->generateDynamicPageCacheExpectedHeaderValue($expected_cacheability->getCacheContexts(), $expected_cacheability->getCacheMaxAge()));

    // Different databases have different sort orders, so a sort is required so
    // test expectations do not need to vary per database.
    $default_sort = ['sort' => 'drupal_internal__' . $this->entity->getEntityType()->getKey('id')];
    $collection_url->setOption('query', $default_sort);

    // 200 for collections, even when all entities are inaccessible. Access is
    // on a per-entity basis, which is handled by
    // self::getExpectedCollectionResponse().
    $expected_response = $this->getExpectedCollectionResponse($entity_collection, $collection_url->toString(), $request_options);
    $expected_cacheability = $expected_response->getCacheableMetadata();
    $expected_document = $expected_response->getResponseData();
    $response = $this->request('GET', $collection_url, $request_options);
    $this->assertResourceResponse(200, $expected_document, $response, $expected_cacheability->getCacheTags(), $expected_cacheability->getCacheContexts(), 'UNCACHEABLE (request policy)', $this->generateDynamicPageCacheExpectedHeaderValue($expected_cacheability->getCacheContexts(), $expected_cacheability->getCacheMaxAge()));

    $this->setUpAuthorization('GET');

    // 200 for well-formed HEAD request.
    $expected_response = $this->getExpectedCollectionResponse($entity_collection, $collection_url->toString(), $request_options);
    $expected_cacheability = $expected_response->getCacheableMetadata();
    $response = $this->request('HEAD', $collection_url, $request_options);
    $this->assertResourceResponse(200, NULL, $response, $expected_cacheability->getCacheTags(), $expected_cacheability->getCacheContexts(), 'UNCACHEABLE (request policy)', $this->generateDynamicPageCacheExpectedHeaderValue($expected_cacheability->getCacheContexts(), $expected_cacheability->getCacheMaxAge()));

    // 200 for well-formed GET request.
    $expected_response = $this->getExpectedCollectionResponse($entity_collection, $collection_url->toString(), $request_options);
    $expected_cacheability = $expected_response->getCacheableMetadata();
    $expected_document = $expected_response->getResponseData();
    $response = $this->request('GET', $collection_url, $request_options);
    // Dynamic Page Cache HIT unless the HEAD request was UNCACHEABLE.
    $dynamic_cache = $this->generateDynamicPageCacheExpectedHeaderValue($expected_cacheability->getCacheContexts(), $expected_cacheability->getCacheMaxAge()) === 'UNCACHEABLE (poor cacheability)' ? 'UNCACHEABLE (poor cacheability)' : 'HIT';
    $this->assertResourceResponse(200, $expected_document, $response, $expected_cacheability->getCacheTags(), $expected_cacheability->getCacheContexts(), 'UNCACHEABLE (request policy)', $dynamic_cache);

    if ($this->entity instanceof FieldableEntityInterface) {
      // 403 for filtering on an unauthorized field on the base resource type.
      $unauthorized_filter_url = clone $collection_url;
      $unauthorized_filter_url->setOption('query', [
        'filter' => [
          'related_author_id' => [
            'operator' => '<>',
            'path' => 'field_jsonapi_test_entity_ref.status',
            'value' => 'does_not@matter.com',
          ],
        ],
      ]);
      $response = $this->request('GET', $unauthorized_filter_url, $request_options);
      $expected_error_message = "The current user is not authorized to filter by the `field_jsonapi_test_entity_ref` field, given in the path `field_jsonapi_test_entity_ref`. The 'field_jsonapi_test_entity_ref view access' permission is required.";
      $expected_cache_tags = ['4xx-response', 'http_response'];
      $expected_cache_contexts = [
        'url.query_args',
        'url.site',
        'user.permissions',
      ];
      $this->assertResourceErrorResponse(403, $expected_error_message, $unauthorized_filter_url, $response, FALSE, $expected_cache_tags, $expected_cache_contexts, 'UNCACHEABLE (request policy)', TRUE);

      $this->grantPermissionsToTestedRole(['field_jsonapi_test_entity_ref view access']);

      // 403 for filtering on an unauthorized field on a related resource type.
      $response = $this->request('GET', $unauthorized_filter_url, $request_options);
      $expected_error_message = "The current user is not authorized to filter by the `status` field, given in the path `field_jsonapi_test_entity_ref.entity:user.status`.";
      $this->assertResourceErrorResponse(403, $expected_error_message, $unauthorized_filter_url, $response, FALSE, $expected_cache_tags, $expected_cache_contexts, 'UNCACHEABLE (request policy)', TRUE);
    }

    // Remove an entity from the collection, then filter it out.
    $filtered_entity_collection = $entity_collection;
    $removed = array_shift($filtered_entity_collection);
    $filtered_collection_url = clone $collection_url;
    $entity_collection_filter = [
      'filter' => [
        'ids' => [
          'condition' => [
            'operator' => '<>',
            'path' => 'id',
            'value' => $removed->uuid(),
          ],
        ],
      ],
    ];
    $filtered_collection_url->setOption('query', $entity_collection_filter + $default_sort);
    $expected_response = $this->getExpectedCollectionResponse($filtered_entity_collection, $filtered_collection_url->toString(), $request_options, NULL, TRUE);
    $expected_cacheability = $expected_response->getCacheableMetadata();
    $expected_document = $expected_response->getResponseData();
    $response = $this->request('GET', $filtered_collection_url, $request_options);
    $this->assertResourceResponse(200, $expected_document, $response, $expected_cacheability->getCacheTags(), $expected_cacheability->getCacheContexts(), 'UNCACHEABLE (request policy)', $this->generateDynamicPageCacheExpectedHeaderValue($expected_cacheability->getCacheContexts(), $expected_cacheability->getCacheMaxAge()));

    // Filtered collection with includes.
    $relationship_field_names = array_reduce($filtered_entity_collection, function ($relationship_field_names, $entity) {
      return array_unique(array_merge($relationship_field_names, $this->getRelationshipFieldNames($entity)));
    }, []);
    $include = ['include' => implode(',', $relationship_field_names)];
    $filtered_collection_include_url = clone $collection_url;
    $filtered_collection_include_url->setOption('query', $entity_collection_filter + $include + $default_sort);
    $expected_response = $this->getExpectedCollectionResponse($filtered_entity_collection, $filtered_collection_include_url->toString(), $request_options, $relationship_field_names, TRUE);
    $expected_cacheability = $expected_response->getCacheableMetadata();
    $expected_cacheability->setCacheTags(array_values(array_diff($expected_cacheability->getCacheTags(), ['4xx-response'])));
    $expected_document = $expected_response->getResponseData();
    $response = $this->request('GET', $filtered_collection_include_url, $request_options);
    $this->assertResourceResponse(200, $expected_document, $response, $expected_cacheability->getCacheTags(), $expected_cacheability->getCacheContexts(), 'UNCACHEABLE (request policy)', $this->generateDynamicPageCacheExpectedHeaderValue($expected_cacheability->getCacheContexts(), $expected_cacheability->getCacheMaxAge()));

    // If the response should vary by a user's authorizations, grant permissions
    // for the included resources and execute another request.
    $permission_related_cache_contexts = [
      'user',
      'user.permissions',
      'user.roles',
    ];
    if (!empty($relationship_field_names) && !empty(array_intersect($expected_cacheability->getCacheContexts(), $permission_related_cache_contexts))) {
      $applicable_permissions = array_intersect_key(static::getIncludePermissions(), array_flip($relationship_field_names));
      $flattened_permissions = array_unique(array_reduce($applicable_permissions, 'array_merge', []));
      $this->grantPermissionsToTestedRole($flattened_permissions);
      $expected_response = $this->getExpectedCollectionResponse($filtered_entity_collection, $filtered_collection_include_url->toString(), $request_options, $relationship_field_names, TRUE);
      $expected_cacheability = $expected_response->getCacheableMetadata();
      $expected_document = $expected_response->getResponseData();
      $response = $this->request('GET', $filtered_collection_include_url, $request_options);
      $requires_include_only_permissions = !empty($flattened_permissions);
      $is_uncacheable = $this->generateDynamicPageCacheExpectedHeaderValue($expected_cacheability->getCacheContexts(), $expected_cacheability->getCacheMaxAge()) === 'UNCACHEABLE (poor cacheability)';
      $dynamic_cache = !$is_uncacheable ? ($requires_include_only_permissions ? 'MISS' : 'HIT') : 'UNCACHEABLE (poor cacheability)';
      $this->assertResourceResponse(200, $expected_document, $response, $expected_cacheability->getCacheTags(), $expected_cacheability->getCacheContexts(), 'UNCACHEABLE (request policy)', $dynamic_cache);
    }

    // Sorted collection with includes.
    $sorted_entity_collection = $entity_collection;
    uasort($sorted_entity_collection, function (EntityInterface $a, EntityInterface $b) {
      // Sort by ID in reverse order.
      return strcmp($b->uuid(), $a->uuid());
    });
    $sorted_collection_include_url = clone $collection_url;
    $sorted_collection_include_url->setOption('query', $include + ['sort' => "-id"]);
    $expected_response = $this->getExpectedCollectionResponse($sorted_entity_collection, $sorted_collection_include_url->toString(), $request_options, $relationship_field_names);
    $expected_cacheability = $expected_response->getCacheableMetadata();
    $expected_cacheability->setCacheTags(array_values(array_diff($expected_cacheability->getCacheTags(), ['4xx-response'])));
    $expected_document = $expected_response->getResponseData();
    $response = $this->request('GET', $sorted_collection_include_url, $request_options);
    $this->assertResourceResponse(200, $expected_document, $response, $expected_cacheability->getCacheTags(), $expected_cacheability->getCacheContexts(), 'UNCACHEABLE (request policy)', $this->generateDynamicPageCacheExpectedHeaderValue([], $expected_cacheability->getCacheMaxAge()));
  }

  /**
   * Tests GET of the related resource of an individual resource.
   *
   * Expected responses are built by making requests to 'relationship' routes.
   * Using the fetched resource identifiers, if any, all targeted resources are
   * fetched individually. These individual responses are then 'merged' into a
   * single expected ResourceResponse. This is repeated for every relationship
   * field of the resource type under test.
   */
  public function testRelated(): void {
    $request_options = [];
    $request_options[RequestOptions::HEADERS]['Accept'] = 'application/vnd.api+json';
    $request_options = NestedArray::mergeDeep($request_options, $this->getAuthenticationRequestOptions());
    $this->doTestRelated($request_options);
    $this->setUpAuthorization('GET');
    $this->doTestRelated($request_options);
  }

  /**
   * Tests CRUD of individual resource relationship data.
   *
   * Unlike the "related" routes, relationship routes only return information
   * about the "relationship" itself, not the targeted resources. For JSON:API
   * with Drupal, relationship routes are like looking at an entity reference
   * field without loading the entities. It only reveals the type of the
   * targeted resource and the target resource IDs. These type+ID combos are
   * referred to as "resource identifiers."
   */
  public function testRelationships(): void {
    if ($this->entity instanceof ConfigEntityInterface) {
      $this->markTestSkipped('Configuration entities cannot have relationships.');
    }

    $request_options = [];
    $request_options[RequestOptions::HEADERS]['Accept'] = 'application/vnd.api+json';
    $request_options = NestedArray::mergeDeep($request_options, $this->getAuthenticationRequestOptions());

    // Test GET.
    $this->doTestRelationshipGet($request_options);
    $this->setUpAuthorization('GET');
    $this->doTestRelationshipGet($request_options);

    // Test POST.
    $this->config('jsonapi.settings')->set('read_only', FALSE)->save(TRUE);
    $this->doTestRelationshipMutation($request_options);
    // Grant entity-level edit access.
    $this->setUpAuthorization('PATCH');
    $this->doTestRelationshipMutation($request_options);
    // Field edit access is still forbidden, grant it.
    $this->grantPermissionsToTestedRole([
      'field_jsonapi_test_entity_ref view access',
      'field_jsonapi_test_entity_ref edit access',
      'field_jsonapi_test_entity_ref update access',
    ]);
    $this->doTestRelationshipMutation($request_options);
  }

  /**
   * Tests POSTing an individual resource, plus edge cases to ensure good DX.
   */
  protected function doTestPostIndividual(): void {
    // @todo Remove this in https://www.drupal.org/node/2300677.
    if ($this->entity instanceof ConfigEntityInterface) {
      $this->markTestSkipped('POSTing config entities is not yet supported.');
    }

    // Try with all of the following request bodies.
    $not_parseable_request_body = '!{>}<';
    $parseable_valid_request_body = Json::encode($this->getPostDocument());
    $parseable_invalid_request_body_missing_type = Json::encode($this->removeResourceTypeFromDocument($this->getPostDocument()));
    if ($this->entity->getEntityType()->hasKey('label')) {
      $parseable_invalid_request_body = Json::encode($this->makeNormalizationInvalid($this->getPostDocument(), 'label'));
    }
    $parseable_invalid_request_body_2 = Json::encode(NestedArray::mergeDeep(['data' => ['id' => $this->randomMachineName(129)]], $this->getPostDocument()));
    $parseable_invalid_request_body_3 = Json::encode(NestedArray::mergeDeep(['data' => ['attributes' => ['field_rest_test' => $this->randomString()]]], $this->getPostDocument()));
    $parseable_invalid_request_body_4 = Json::encode(NestedArray::mergeDeep(['data' => ['attributes' => ['field_nonexistent' => $this->randomString()]]], $this->getPostDocument()));

    // The URL and Guzzle request options that will be used in this test. The
    // request options will be modified/expanded throughout this test:
    // - to first test all mistakes a developer might make, and assert that the
    //   error responses provide a good DX
    // - to eventually result in a well-formed request that succeeds.
    $url = Url::fromRoute(sprintf('jsonapi.%s.collection.post', static::$resourceTypeName));
    $request_options = [];
    $request_options[RequestOptions::HEADERS]['Accept'] = 'application/vnd.api+json';
    $request_options = NestedArray::mergeDeep($request_options, $this->getAuthenticationRequestOptions());

    // DX: 405 when read-only mode is enabled.
    $response = $this->request('POST', $url, $request_options);
    $this->assertResourceErrorResponse(405, sprintf("JSON:API is configured to accept only read operations. Site administrators can configure this at %s.", Url::fromUri('base:/admin/config/services/jsonapi')->setAbsolute()->toString(TRUE)->getGeneratedUrl()), $url, $response);
    if ($this->resourceType->isLocatable()) {
      $this->assertSame(['GET'], $response->getHeader('Allow'));
    }
    else {
      $this->assertSame([''], $response->getHeader('Allow'));
    }

    $this->config('jsonapi.settings')->set('read_only', FALSE)->save(TRUE);

    // DX: 415 when no Content-Type request header.
    $response = $this->request('POST', $url, $request_options);
    $this->assertSame(415, $response->getStatusCode());

    $request_options[RequestOptions::HEADERS]['Content-Type'] = 'application/vnd.api+json';

    // DX: 403 when unauthorized.
    $response = $this->request('POST', $url, $request_options);
    $reason = $this->getExpectedUnauthorizedAccessMessage('POST');
    $this->assertResourceErrorResponse(403, (string) $reason, $url, $response);

    $this->setUpAuthorization('POST');

    // DX: 400 when no request body.
    $response = $this->request('POST', $url, $request_options);
    $this->assertResourceErrorResponse(400, 'Empty request body.', $url, $response, FALSE);

    $request_options[RequestOptions::BODY] = $not_parseable_request_body;

    // DX: 400 when un-parseable request body.
    $response = $this->request('POST', $url, $request_options);
    $this->assertResourceErrorResponse(400, 'Syntax error', $url, $response, FALSE);

    $request_options[RequestOptions::BODY] = $parseable_invalid_request_body_missing_type;

    // DX: 400 when invalid JSON:API request body.
    $response = $this->request('POST', $url, $request_options);
    $this->assertResourceErrorResponse(400, 'Resource object must include a "type".', $url, $response, FALSE);

    if ($this->entity->getEntityType()->hasKey('label')) {
      $request_options[RequestOptions::BODY] = $parseable_invalid_request_body;
      // DX: 422 when invalid entity: multiple values sent for single-value field.
      $response = $this->request('POST', $url, $request_options);
      $label_field = $this->entity->getEntityType()->getKey('label');
      $label_field_capitalized = $this->entity->getFieldDefinition($label_field)->getLabel();
      $this->assertResourceErrorResponse(422, "$label_field: $label_field_capitalized: this field cannot hold more than 1 values.", NULL, $response, '/data/attributes/' . $label_field);
    }

    $request_options[RequestOptions::BODY] = $parseable_invalid_request_body_2;

    // DX: 403 when invalid entity: UUID field too long.
    // @todo Fix this in https://www.drupal.org/project/drupal/issues/2149851.
    if ($this->entity->getEntityType()->hasKey('uuid')) {
      $response = $this->request('POST', $url, $request_options);
      $this->assertResourceErrorResponse(422, "IDs should be properly generated and formatted UUIDs as described in RFC 4122.", $url, $response);
    }

    $request_options[RequestOptions::BODY] = $parseable_invalid_request_body_3;

    // DX: 403 when entity contains field without 'edit' access.
    $response = $this->request('POST', $url, $request_options);
    $this->assertResourceErrorResponse(403, "The current user is not allowed to POST the selected field (field_rest_test).", $url, $response, '/data/attributes/field_rest_test');

    $request_options[RequestOptions::BODY] = $parseable_invalid_request_body_4;

    // DX: 422 when request document contains non-existent field.
    $response = $this->request('POST', $url, $request_options);
    $this->assertResourceErrorResponse(422, sprintf("The attribute field_nonexistent does not exist on the %s resource type.", static::$resourceTypeName), $url, $response, FALSE);

    $request_options[RequestOptions::BODY] = $parseable_valid_request_body;

    $request_options[RequestOptions::HEADERS]['Content-Type'] = 'text/xml';

    // DX: 415 when request body in existing but not allowed format.
    $response = $this->request('POST', $url, $request_options);
    $this->assertResourceErrorResponse(415, 'No route found that matches "Content-Type: text/xml"', $url, $response);

    $request_options[RequestOptions::HEADERS]['Content-Type'] = 'application/vnd.api+json';

    // 201 for well-formed request.
    $response = $this->request('POST', $url, $request_options);
    $this->assertResourceResponse(201, FALSE, $response);
    $this->assertFalse($response->hasHeader('X-Drupal-Cache'));
    // If the entity is stored, perform extra checks.
    if (get_class($this->entityStorage) !== ContentEntityNullStorage::class) {
      $created_entity = $this->entityLoadUnchanged(static::$firstCreatedEntityId);
      $uuid = $created_entity->uuid();
      // @todo Remove line below in favor of commented line in https://www.drupal.org/project/drupal/issues/2878463.
      $location = Url::fromRoute(sprintf('jsonapi.%s.individual', static::$resourceTypeName), ['entity' => $uuid]);
      if (static::$resourceTypeIsVersionable) {
        assert($created_entity instanceof RevisionableInterface);
        $location->setOption('query', ['resourceVersion' => 'id:' . $created_entity->getRevisionId()]);
      }
      /* $location = $this->entityStorage->load(static::$firstCreatedEntityId)->toUrl('jsonapi')->setAbsolute(TRUE)->toString(); */
      $this->assertSame([$location->setAbsolute()->toString()], $response->getHeader('Location'));

      // Assert that the entity was indeed created, and that the response body
      // contains the serialized created entity.
      $created_entity_document = $this->normalize($created_entity, $url);
      $decoded_response_body = $this->getDocumentFromResponse($response);
      $this->assertEquals($created_entity_document, $decoded_response_body);
      // Assert that the entity was indeed created using the POSTed values.
      foreach ($this->getPostDocument()['data']['attributes'] as $field_name => $field_normalization) {
        // If the value is an array of properties, only verify that the sent
        // properties are present, the server could be computing additional
        // properties.
        if (is_array($field_normalization)) {
          foreach ($field_normalization as $value) {
            $this->assertContains($value, $created_entity_document['data']['attributes'][$field_name]);
          }
        }
        else {
          $this->assertEquals($field_normalization, $created_entity_document['data']['attributes'][$field_name]);
        }
      }
      if (isset($this->getPostDocument()['data']['relationships'])) {
        foreach ($this->getPostDocument()['data']['relationships'] as $field_name => $relationship_field_normalization) {
          // POSTing relationships: 'data' is required, 'links' is optional.
          static::recursiveKsort($relationship_field_normalization);
          static::recursiveKsort($created_entity_document['data']['relationships'][$field_name]);
          $this->assertEquals($relationship_field_normalization, array_diff_key($created_entity_document['data']['relationships'][$field_name], ['links' => TRUE]));
        }
      }
    }
    else {
      $this->assertFalse($response->hasHeader('Location'));
    }

    // 201 for well-formed request that creates another entity.
    // If the entity is stored, delete the first created entity (in case there
    // is a uniqueness constraint).
    if (get_class($this->entityStorage) !== ContentEntityNullStorage::class) {
      $this->entityStorage->load(static::$firstCreatedEntityId)->delete();
    }
    $response = $this->request('POST', $url, $request_options);
    $this->assertResourceResponse(201, FALSE, $response);
    $this->assertFalse($response->hasHeader('X-Drupal-Cache'));

    if ($this->entity->getEntityType()->getStorageClass() !== ContentEntityNullStorage::class && $this->entity->getEntityType()->hasKey('uuid')) {
      $second_created_entity = $this->entityStorage->load(static::$secondCreatedEntityId);
      $uuid = $second_created_entity->uuid();
      // @todo Remove line below in favor of commented line in https://www.drupal.org/project/drupal/issues/2878463.
      $location = Url::fromRoute(sprintf('jsonapi.%s.individual', static::$resourceTypeName), ['entity' => $uuid]);
      /* $location = $this->entityStorage->load(static::$secondCreatedEntityId)->toUrl('jsonapi')->setAbsolute(TRUE)->toString(); */
      if (static::$resourceTypeIsVersionable) {
        assert($created_entity instanceof RevisionableInterface);
        $location->setOption('query', ['resourceVersion' => 'id:' . $second_created_entity->getRevisionId()]);
      }
      $this->assertSame([$location->setAbsolute()->toString()], $response->getHeader('Location'));

      // 500 when creating an entity with a duplicate UUID.
      $doc = $this->getModifiedEntityForPostTesting();
      $doc['data']['id'] = $uuid;
      $label_field = $this->entity->getEntityType()->hasKey('label') ? $this->entity->getEntityType()->getKey('label') : static::$labelFieldName;
      if (isset($label_field)) {
        $doc['data']['attributes'][$label_field] = [['value' => $this->randomMachineName()]];
      }
      $request_options[RequestOptions::BODY] = Json::encode($doc);

      $response = $this->request('POST', $url, $request_options);
      $this->assertResourceErrorResponse(409, 'Conflict: Entity already exists.', $url, $response, FALSE);

      // 201 when successfully creating an entity with a new UUID.
      $doc = $this->getModifiedEntityForPostTesting();
      $new_uuid = \Drupal::service('uuid')->generate();
      $doc['data']['id'] = $new_uuid;
      if (isset($label_field)) {
        $doc['data']['attributes'][$label_field] = [['value' => $this->randomMachineName()]];
      }
      $request_options[RequestOptions::BODY] = Json::encode($doc);

      $response = $this->request('POST', $url, $request_options);
      $this->assertResourceResponse(201, FALSE, $response);
      $entities = $this->entityStorage->loadByProperties([$this->uuidKey => $new_uuid]);
      $new_entity = reset($entities);
      $this->assertNotNull($new_entity);
      $new_entity->delete();
    }
    else {
      $this->assertFalse($response->hasHeader('Location'));
    }
  }

  /**
   * Tests PATCHing an individual resource, plus edge cases to ensure good DX.
   */
  protected function doTestPatchIndividual(): void {
    // @todo Remove this in https://www.drupal.org/node/2300677.
    if ($this->entity instanceof ConfigEntityInterface) {
      $this->markTestSkipped('PATCHing config entities is not yet supported.');
    }

    $prior_revision_id = (int) $this->entityLoadUnchanged($this->entity->id())->getRevisionId();

    // Patch testing requires that another entity of the same type exists.
    $this->anotherEntity = $this->createAnotherEntity('dupe');

    // Try with all of the following request bodies.
    $not_parseable_request_body = '!{>}<';
    $parseable_valid_request_body = Json::encode($this->getPatchDocument());
    if ($this->entity->getEntityType()->hasKey('label')) {
      $parseable_invalid_request_body = Json::encode($this->makeNormalizationInvalid($this->getPatchDocument(), 'label'));
    }
    $parseable_invalid_request_body_2 = Json::encode(NestedArray::mergeDeep(['data' => ['attributes' => ['field_rest_test' => $this->randomString()]]], $this->getPatchDocument()));
    // The 'field_rest_test' field does not allow 'view' access, so does not end
    // up in the JSON:API document. Even when we explicitly add it to the JSON
    // API document that we send in a PATCH request, it is considered invalid.
    $parseable_invalid_request_body_3 = Json::encode(NestedArray::mergeDeep(['data' => ['attributes' => ['field_rest_test' => $this->entity->get('field_rest_test')->getValue()]]], $this->getPatchDocument()));
    $parseable_invalid_request_body_4 = Json::encode(NestedArray::mergeDeep(['data' => ['attributes' => ['field_nonexistent' => $this->randomString()]]], $this->getPatchDocument()));
    // It is invalid to PATCH a relationship field under the attributes member.
    if ($this->entity instanceof FieldableEntityInterface && $this->entity->hasField('field_jsonapi_test_entity_ref')) {
      $parseable_invalid_request_body_5 = Json::encode(NestedArray::mergeDeep(['data' => ['attributes' => ['field_jsonapi_test_entity_ref' => ['target_id' => $this->randomString()]]]], $this->getPostDocument()));
    }
    // Invalid PATCH request with missing id key.
    $parseable_invalid_request_body_6 = $this->getPatchDocument();
    unset($parseable_invalid_request_body_6['data']['id']);
    $parseable_invalid_request_body_6 = Json::encode($parseable_invalid_request_body_6);

    // The URL and Guzzle request options that will be used in this test. The
    // request options will be modified/expanded throughout this test:
    // - to first test all mistakes a developer might make, and assert that the
    //   error responses provide a good DX
    // - to eventually result in a well-formed request that succeeds.
    // @todo Remove line below in favor of commented line in https://www.drupal.org/project/drupal/issues/2878463.
    $url = Url::fromRoute(sprintf('jsonapi.%s.individual', static::$resourceTypeName), ['entity' => $this->entity->uuid()]);
    // $url = $this->entity->toUrl('jsonapi');
    $request_options = [];
    $request_options[RequestOptions::HEADERS]['Accept'] = 'application/vnd.api+json';
    $request_options = NestedArray::mergeDeep($request_options, $this->getAuthenticationRequestOptions());

    // DX: 405 when read-only mode is enabled.
    $response = $this->request('PATCH', $url, $request_options);
    $this->assertResourceErrorResponse(405, sprintf("JSON:API is configured to accept only read operations. Site administrators can configure this at %s.", Url::fromUri('base:/admin/config/services/jsonapi')->setAbsolute()->toString(TRUE)->getGeneratedUrl()), $url, $response);
    $this->assertSame(['GET'], $response->getHeader('Allow'));

    $this->config('jsonapi.settings')->set('read_only', FALSE)->save(TRUE);

    // DX: 415 when no Content-Type request header.
    $response = $this->request('PATCH', $url, $request_options);
    $this->assertSame(415, $response->getStatusCode());

    $request_options[RequestOptions::HEADERS]['Content-Type'] = 'application/vnd.api+json';

    // DX: 403 when unauthorized.
    $response = $this->request('PATCH', $url, $request_options);
    $reason = $this->getExpectedUnauthorizedAccessMessage('PATCH');
    $this->assertResourceErrorResponse(403, (string) $reason, $url, $response);

    $this->setUpAuthorization('PATCH');

    // DX: 400 when no request body.
    $response = $this->request('PATCH', $url, $request_options);
    $this->assertResourceErrorResponse(400, 'Empty request body.', $url, $response, FALSE);

    $request_options[RequestOptions::BODY] = $not_parseable_request_body;

    // DX: 400 when un-parseable request body.
    $response = $this->request('PATCH', $url, $request_options);
    $this->assertResourceErrorResponse(400, 'Syntax error', $url, $response, FALSE);

    // DX: 422 when invalid entity: multiple values sent for single-value field.
    if ($this->entity->getEntityType()->hasKey('label')) {
      $request_options[RequestOptions::BODY] = $parseable_invalid_request_body;
      $response = $this->request('PATCH', $url, $request_options);
      $label_field = $this->entity->getEntityType()->getKey('label');
      $label_field_capitalized = $this->entity->getFieldDefinition($label_field)->getLabel();
      $this->assertResourceErrorResponse(422, "$label_field: $label_field_capitalized: this field cannot hold more than 1 values.", NULL, $response, '/data/attributes/' . $label_field);
    }

    $request_options[RequestOptions::BODY] = $parseable_invalid_request_body_2;

    // DX: 403 when entity contains field without 'edit' access.
    $response = $this->request('PATCH', $url, $request_options);
    $this->assertResourceErrorResponse(403, "The current user is not allowed to PATCH the selected field (field_rest_test).", $url, $response, '/data/attributes/field_rest_test');

    // DX: 403 when entity trying to update an entity's ID field.
    $request_options[RequestOptions::BODY] = Json::encode($this->makeNormalizationInvalid($this->getPatchDocument(), 'id'));
    $response = $this->request('PATCH', $url, $request_options);
    $id_field_name = $this->entity->getEntityType()->getKey('id');
    $this->assertResourceErrorResponse(403, "The current user is not allowed to PATCH the selected field ($id_field_name). The entity ID cannot be changed.", $url, $response, "/data/attributes/$id_field_name");

    if ($this->entity->getEntityType()->hasKey('uuid')) {
      // DX: 400 when entity trying to update an entity's UUID field.
      $request_options[RequestOptions::BODY] = Json::encode($this->makeNormalizationInvalid($this->getPatchDocument(), 'uuid'));
      $response = $this->request('PATCH', $url, $request_options);
      $this->assertResourceErrorResponse(400, sprintf("The selected entity (%s) does not match the ID in the payload (%s).", $this->entity->uuid(), $this->anotherEntity->uuid()), $url, $response, FALSE);
    }

    $request_options[RequestOptions::BODY] = $parseable_invalid_request_body_3;

    // DX: 403 when entity contains field without 'edit' nor 'view' access, even
    // when the value for that field matches the current value. This is allowed
    // in principle, but leads to information disclosure.
    $response = $this->request('PATCH', $url, $request_options);
    $this->assertResourceErrorResponse(403, "The current user is not allowed to PATCH the selected field (field_rest_test).", $url, $response, '/data/attributes/field_rest_test');

    // DX: 403 when sending PATCH request with updated read-only fields.
    [$modified_entity, $original_values] = static::getModifiedEntityForPatchTesting($this->entity);
    // Send PATCH request by serializing the modified entity, assert the error
    // response, change the modified entity field that caused the error response
    // back to its original value, repeat.
    foreach (static::$patchProtectedFieldNames as $patch_protected_field_name => $reason) {
      $request_options[RequestOptions::BODY] = Json::encode($this->normalize($modified_entity, $url));
      $response = $this->request('PATCH', $url, $request_options);
      $this->assertResourceErrorResponse(403, "The current user is not allowed to PATCH the selected field (" . $patch_protected_field_name . ")." . ($reason !== NULL ? ' ' . $reason : ''), $url->setAbsolute(), $response, '/data/attributes/' . $patch_protected_field_name);
      $modified_entity->get($patch_protected_field_name)->setValue($original_values[$patch_protected_field_name]);
    }

    $request_options[RequestOptions::BODY] = $parseable_invalid_request_body_4;

    // DX: 422 when request document contains non-existent field.
    $response = $this->request('PATCH', $url, $request_options);
    $this->assertResourceErrorResponse(422, sprintf("The attribute field_nonexistent does not exist on the %s resource type.", static::$resourceTypeName), $url, $response, FALSE);

    // DX: 422 when updating a relationship field under attributes.
    if (isset($parseable_invalid_request_body_5)) {
      $request_options[RequestOptions::BODY] = $parseable_invalid_request_body_5;
      $response = $this->request('PATCH', $url, $request_options);
      $this->assertResourceErrorResponse(422, "The following relationship fields were provided as attributes: [ field_jsonapi_test_entity_ref ]", $url, $response, FALSE);
    }

    // DX: 400 when request document doesn't contain id.
    // This also tests that no PHP warnings raised due to non-existent key.
    $request_options[RequestOptions::BODY] = $parseable_invalid_request_body_6;
    $response = $this->request('PATCH', $url, $request_options);
    $this->assertResourceResponse(400, FALSE, $response);

    // 200 for well-formed PATCH request that sends all fields (even including
    // read-only ones, but with unchanged values).
    $valid_request_body = NestedArray::mergeDeep($this->normalize($this->entity, $url), $this->getPatchDocument());
    $request_options[RequestOptions::BODY] = Json::encode($valid_request_body);
    $response = $this->request('PATCH', $url, $request_options);
    $this->assertResourceResponse(200, FALSE, $response);
    $updated_entity = $this->entityLoadUnchanged($this->entity->id());
    $this->assertSame(static::$newRevisionsShouldBeAutomatic, $prior_revision_id < (int) $updated_entity->getRevisionId());
    $prior_revision_id = (int) $updated_entity->getRevisionId();

    $request_options[RequestOptions::BODY] = $parseable_valid_request_body;
    $request_options[RequestOptions::HEADERS]['Content-Type'] = 'text/xml';

    // DX: 415 when request body in existing but not allowed format.
    $response = $this->request('PATCH', $url, $request_options);
    $this->assertSame(415, $response->getStatusCode());

    $request_options[RequestOptions::HEADERS]['Content-Type'] = 'application/vnd.api+json';

    // 200 for well-formed request.
    $response = $this->request('PATCH', $url, $request_options);
    $this->assertResourceResponse(200, FALSE, $response);
    $this->assertFalse($response->hasHeader('X-Drupal-Cache'));
    // Assert that the entity was indeed updated, and that the response body
    // contains the serialized updated entity.
    $updated_entity = $this->entityLoadUnchanged($this->entity->id());
    $this->assertSame(static::$newRevisionsShouldBeAutomatic, $prior_revision_id < (int) $updated_entity->getRevisionId());
    if ($this->entity instanceof RevisionLogInterface) {
      if (static::$newRevisionsShouldBeAutomatic) {
        $this->assertNotSame((int) $this->entity->getRevisionCreationTime(), (int) $updated_entity->getRevisionCreationTime());
      }
      else {
        $this->assertSame((int) $this->entity->getRevisionCreationTime(), (int) $updated_entity->getRevisionCreationTime());
      }
    }
    $updated_entity_document = $this->normalize($updated_entity, $url);
    $document = $this->getDocumentFromResponse($response);
    $this->assertSame($updated_entity_document, $document);
    $prior_revision_id = (int) $updated_entity->getRevisionId();
    // Assert that the entity was indeed created using the PATCHed values.
    foreach ($this->getPatchDocument()['data']['attributes'] as $field_name => $field_normalization) {
      // If the value is an array of properties, only verify that the sent
      // properties are present, the server could be computing additional
      // properties.
      if (is_array($field_normalization)) {
        foreach ($field_normalization as $value) {
          $this->assertContains($value, $updated_entity_document['data']['attributes'][$field_name]);
        }
      }
      else {
        $this->assertSame($field_normalization, $updated_entity_document['data']['attributes'][$field_name]);
      }
    }
    if (isset($this->getPatchDocument()['data']['relationships'])) {
      foreach ($this->getPatchDocument()['data']['relationships'] as $field_name => $relationship_field_normalization) {
        // POSTing relationships: 'data' is required, 'links' is optional.
        static::recursiveKsort($relationship_field_normalization);
        static::recursiveKsort($updated_entity_document['data']['relationships'][$field_name]);
        $this->assertSame($relationship_field_normalization, array_diff_key($updated_entity_document['data']['relationships'][$field_name], ['links' => TRUE]));
      }
    }

    // Ensure that fields do not get deleted if they're not present in the PATCH
    // request. Test this using the configurable field that we added, but which
    // is not sent in the PATCH request.
    $this->assertSame('All the faith he had had had had no effect on the outcome of his life.', $updated_entity->get('field_rest_test')->value);

    // Multi-value field: remove item 0. Then item 1 becomes item 0.
    $doc_multi_value_tests = $this->getPatchDocument();
    $doc_multi_value_tests['data']['attributes']['field_rest_test_multivalue'] = $this->entity->get('field_rest_test_multivalue')->getValue();
    $doc_remove_item = $doc_multi_value_tests;
    unset($doc_remove_item['data']['attributes']['field_rest_test_multivalue'][0]);
    $request_options[RequestOptions::BODY] = Json::encode($doc_remove_item);
    $response = $this->request('PATCH', $url, $request_options);
    $this->assertResourceResponse(200, FALSE, $response);
    $updated_entity = $this->entityLoadUnchanged($this->entity->id());
    $this->assertSame([0 => ['value' => 'Two']], $updated_entity->get('field_rest_test_multivalue')->getValue());
    $this->assertSame(static::$newRevisionsShouldBeAutomatic, $prior_revision_id < (int) $updated_entity->getRevisionId());
    $prior_revision_id = (int) $updated_entity->getRevisionId();

    // Multi-value field: add one item before the existing one, and one after.
    $doc_add_items = $doc_multi_value_tests;
    $doc_add_items['data']['attributes']['field_rest_test_multivalue'][2] = ['value' => 'Three'];
    $request_options[RequestOptions::BODY] = Json::encode($doc_add_items);
    $response = $this->request('PATCH', $url, $request_options);
    $this->assertResourceResponse(200, FALSE, $response);
    $expected_document = [
      0 => ['value' => 'One'],
      1 => ['value' => 'Two'],
      2 => ['value' => 'Three'],
    ];
    $updated_entity = $this->entityLoadUnchanged($this->entity->id());
    $this->assertSame($expected_document, $updated_entity->get('field_rest_test_multivalue')->getValue());
    $this->assertSame(static::$newRevisionsShouldBeAutomatic, $prior_revision_id < (int) $updated_entity->getRevisionId());
    $prior_revision_id = (int) $updated_entity->getRevisionId();

    // Finally, assert that when Content Moderation is installed, a new revision
    // is automatically created when PATCHing for entity types that have a
    // moderation handler.
    // @see \Drupal\content_moderation\Entity\Handler\ModerationHandler::onPresave()
    // @see \Drupal\content_moderation\EntityTypeInfo::$moderationHandlers
    if ($updated_entity instanceof EntityPublishedInterface) {
      $updated_entity->setPublished()->save();
    }
    $this->assertTrue($this->container->get('module_installer')->install(['content_moderation'], TRUE), 'Installed modules.');

    if (!\Drupal::service('content_moderation.moderation_information')->canModerateEntitiesOfEntityType($this->entity->getEntityType())) {
      return;
    }

    $workflow = $this->createEditorialWorkflow();
    $workflow->getTypePlugin()->addEntityTypeAndBundle(static::$entityTypeId, $this->entity->bundle());
    $workflow->save();
    $this->grantPermissionsToTestedRole(['use editorial transition publish']);
    $doc_add_items['data']['attributes']['field_rest_test_multivalue'][2] = ['value' => '3'];
    $request_options[RequestOptions::BODY] = Json::encode($doc_add_items);
    $response = $this->request('PATCH', $url, $request_options);
    $this->assertResourceResponse(200, FALSE, $response);
    $expected_document = [
      0 => ['value' => 'One'],
      1 => ['value' => 'Two'],
      2 => ['value' => '3'],
    ];
    $updated_entity = $this->entityLoadUnchanged($this->entity->id());
    $this->assertSame($expected_document, $updated_entity->get('field_rest_test_multivalue')->getValue());
    if ($this->entity->getEntityType()->hasHandlerClass('moderation')) {
      $this->assertLessThan((int) $updated_entity->getRevisionId(), $prior_revision_id);
    }
    else {
      $this->assertSame(static::$newRevisionsShouldBeAutomatic, $prior_revision_id < (int) $updated_entity->getRevisionId());
    }

    // Ensure that PATCHing an entity that is not the latest revision is
    // unsupported.
    if (!$this->entity->getEntityType()->isRevisionable() || !$this->entity->getEntityType()->hasHandlerClass('moderation') || !$this->entity instanceof FieldableEntityInterface) {
      return;
    }
    assert($this->entity instanceof RevisionableInterface);

    $request_options[RequestOptions::HEADERS]['Content-Type'] = 'application/vnd.api+json';
    $request_options[RequestOptions::BODY] = Json::encode([
      'data' => [
        'type' => static::$resourceTypeName,
        'id' => $this->entity->uuid(),
      ],
    ]);
    $this->setUpAuthorization('PATCH');
    $this->grantPermissionsToTestedRole([
      'use editorial transition create_new_draft',
      'use editorial transition archived_published',
      'use editorial transition publish',
    ]);

    // Disallow PATCHing an entity that has a pending revision.
    $updated_entity->set('moderation_state', 'draft');
    $updated_entity->setNewRevision();
    $updated_entity->save();
    $actual_response = $this->request('PATCH', $url, $request_options);
    $this->assertResourceErrorResponse(400, 'Updating a resource object that has a working copy is not yet supported. See https://www.drupal.org/project/drupal/issues/2795279.', $url, $actual_response);

    // Allow PATCHing an unpublished default revision.
    $updated_entity->set('moderation_state', 'archived');
    $updated_entity->setNewRevision();
    $updated_entity->save();
    $actual_response = $this->request('PATCH', $url, $request_options);
    $this->assertSame(200, $actual_response->getStatusCode());

    // Allow PATCHing an unpublished default revision. (An entity that
    // transitions from archived to draft remains an unpublished default
    // revision.)
    $updated_entity->set('moderation_state', 'draft');
    $updated_entity->setNewRevision();
    $updated_entity->save();
    $actual_response = $this->request('PATCH', $url, $request_options);
    $this->assertSame(200, $actual_response->getStatusCode());

    // Allow PATCHing a published default revision.
    $updated_entity->set('moderation_state', 'published');
    $updated_entity->setNewRevision();
    $updated_entity->save();
    $actual_response = $this->request('PATCH', $url, $request_options);
    $this->assertSame(200, $actual_response->getStatusCode());
  }

  /**
   * Tests DELETEing an individual resource, plus edge cases to ensure good DX.
   */
  protected function doTestDeleteIndividual(): void {
    // @todo Remove this in https://www.drupal.org/node/2300677.
    if ($this->entity instanceof ConfigEntityInterface) {
      $this->markTestSkipped('DELETEing config entities is not yet supported.');
    }

    // The URL and Guzzle request options that will be used in this test. The
    // request options will be modified/expanded throughout this test:
    // - to first test all mistakes a developer might make, and assert that the
    //   error responses provide a good DX
    // - to eventually result in a well-formed request that succeeds.
    // @todo Remove line below in favor of commented line in https://www.drupal.org/project/drupal/issues/2878463.
    $url = Url::fromRoute(sprintf('jsonapi.%s.individual', static::$resourceTypeName), ['entity' => $this->entity->uuid()]);
    // $url = $this->entity->toUrl('jsonapi');
    $request_options = [];
    $request_options[RequestOptions::HEADERS]['Accept'] = 'application/vnd.api+json';
    $request_options = NestedArray::mergeDeep($request_options, $this->getAuthenticationRequestOptions());

    // DX: 405 when read-only mode is enabled.
    $response = $this->request('DELETE', $url, $request_options);
    $this->assertResourceErrorResponse(405, sprintf("JSON:API is configured to accept only read operations. Site administrators can configure this at %s.", Url::fromUri('base:/admin/config/services/jsonapi')->setAbsolute()->toString(TRUE)->getGeneratedUrl()), $url, $response);
    $this->assertSame(['GET'], $response->getHeader('Allow'));

    $this->config('jsonapi.settings')->set('read_only', FALSE)->save(TRUE);

    // DX: 403 when unauthorized.
    $response = $this->request('DELETE', $url, $request_options);
    $reason = $this->getExpectedUnauthorizedAccessMessage('DELETE');
    $this->assertResourceErrorResponse(403, (string) $reason, $url, $response, FALSE);

    $this->setUpAuthorization('DELETE');

    // 204 for well-formed request.
    $response = $this->request('DELETE', $url, $request_options);
    $this->assertResourceResponse(204, NULL, $response);

    // DX: 404 when non-existent.
    $response = $this->request('DELETE', $url, $request_options);
    $this->assertSame(404, $response->getStatusCode());
  }

  /**
   * Tests individual and collection revisions.
   */
  public function testRevisions(): void {
    if (!$this->entity->getEntityType()->isRevisionable() || !$this->entity instanceof FieldableEntityInterface) {
      return;
    }
    assert($this->entity instanceof RevisionableInterface);

    // Add a field to modify in order to test revisions.
    FieldStorageConfig::create([
      'entity_type' => static::$entityTypeId,
      'field_name' => 'field_revisionable_number',
      'type' => 'integer',
    ])->setCardinality(1)->save();
    FieldConfig::create([
      'entity_type' => static::$entityTypeId,
      'field_name' => 'field_revisionable_number',
      'bundle' => $this->entity->bundle(),
    ])->setLabel('Revisionable text field')->setTranslatable(FALSE)->save();

    // Reload entity so that it has the new field.
    $entity = $this->entityLoadUnchanged($this->entity->id());

    // Set up test data.
    /** @var \Drupal\Core\Entity\FieldableEntityInterface $entity */
    $entity->set('field_revisionable_number', 42);
    $entity->save();
    $original_revision_id = (int) $entity->getRevisionId();

    $entity->set('field_revisionable_number', 99);
    $entity->setNewRevision();
    $entity->save();
    $latest_revision_id = (int) $entity->getRevisionId();

    // @todo Remove line below in favor of commented line in https://www.drupal.org/project/drupal/issues/2878463.
    $url = Url::fromRoute(sprintf('jsonapi.%s.individual', static::$resourceTypeName), ['entity' => $this->entity->uuid()])->setAbsolute();
    // $url = $this->entity->toUrl('jsonapi');
    $collection_url = Url::fromRoute(sprintf('jsonapi.%s.collection', static::$resourceTypeName))->setAbsolute();
    $relationship_url = Url::fromRoute(sprintf('jsonapi.%s.%s.relationship.get', static::$resourceTypeName, 'field_jsonapi_test_entity_ref'), ['entity' => $this->entity->uuid()])->setAbsolute();
    $related_url = Url::fromRoute(sprintf('jsonapi.%s.%s.related', static::$resourceTypeName, 'field_jsonapi_test_entity_ref'), ['entity' => $this->entity->uuid()])->setAbsolute();
    $original_revision_id_url = clone $url;
    $original_revision_id_url->setOption('query', ['resourceVersion' => "id:$original_revision_id"]);
    $original_revision_id_relationship_url = clone $relationship_url;
    $original_revision_id_relationship_url->setOption('query', ['resourceVersion' => "id:$original_revision_id"]);
    $original_revision_id_related_url = clone $related_url;
    $original_revision_id_related_url->setOption('query', ['resourceVersion' => "id:$original_revision_id"]);
    $latest_revision_id_url = clone $url;
    $latest_revision_id_url->setOption('query', ['resourceVersion' => "id:$latest_revision_id"]);
    $latest_revision_id_relationship_url = clone $relationship_url;
    $latest_revision_id_relationship_url->setOption('query', ['resourceVersion' => "id:$latest_revision_id"]);
    $latest_revision_id_related_url = clone $related_url;
    $latest_revision_id_related_url->setOption('query', ['resourceVersion' => "id:$latest_revision_id"]);
    $rel_latest_version_url = clone $url;
    $rel_latest_version_url->setOption('query', ['resourceVersion' => 'rel:latest-version']);
    $rel_latest_version_relationship_url = clone $relationship_url;
    $rel_latest_version_relationship_url->setOption('query', ['resourceVersion' => 'rel:latest-version']);
    $rel_latest_version_related_url = clone $related_url;
    $rel_latest_version_related_url->setOption('query', ['resourceVersion' => 'rel:latest-version']);
    $rel_latest_version_collection_url = clone $collection_url;
    $rel_latest_version_collection_url->setOption('query', ['resourceVersion' => 'rel:latest-version']);
    $rel_working_copy_url = clone $url;
    $rel_working_copy_url->setOption('query', ['resourceVersion' => 'rel:working-copy']);
    $rel_working_copy_relationship_url = clone $relationship_url;
    $rel_working_copy_relationship_url->setOption('query', ['resourceVersion' => 'rel:working-copy']);
    $rel_working_copy_related_url = clone $related_url;
    $rel_working_copy_related_url->setOption('query', ['resourceVersion' => 'rel:working-copy']);
    $rel_working_copy_collection_url = clone $collection_url;
    $rel_working_copy_collection_url->setOption('query', ['resourceVersion' => 'rel:working-copy']);
    $rel_invalid_collection_url = clone $collection_url;
    $rel_invalid_collection_url->setOption('query', ['resourceVersion' => 'rel:invalid']);
    $revision_id_key = 'drupal_internal__' . $this->entity->getEntityType()->getKey('revision');
    $published_key = $this->entity->getEntityType()->getKey('published');
    $revision_translation_affected_key = $this->entity->getEntityType()->getKey('revision_translation_affected');

    $amend_relationship_urls = function (array &$document, $revision_id) {
      if (!empty($document['data']['relationships'])) {
        foreach ($document['data']['relationships'] as &$relationship) {
          $pattern = '/resourceVersion=id%3A\d/';
          $replacement = 'resourceVersion=' . urlencode("id:$revision_id");
          $relationship['links']['self']['href'] = preg_replace($pattern, $replacement, $relationship['links']['self']['href']);
          $relationship['links']['related']['href'] = preg_replace($pattern, $replacement, $relationship['links']['related']['href']);
        }
      }
    };

    $request_options = [];
    $request_options[RequestOptions::HEADERS]['Accept'] = 'application/vnd.api+json';
    $request_options = NestedArray::mergeDeep($request_options, $this->getAuthenticationRequestOptions());

    // Ensure 403 forbidden on typical GET.
    $actual_response = $this->request('GET', $url, $request_options);
    $expected_cacheability = $this->getExpectedUnauthorizedAccessCacheability();
    $result = $entity->access('view', $this->account, TRUE);
    $detail = 'The current user is not allowed to GET the selected resource.';
    if ($result instanceof AccessResultReasonInterface && ($reason = $result->getReason()) && !empty($reason)) {
      $detail .= ' ' . $reason;
    }
    $this->assertResourceErrorResponse(403, $detail, $url, $actual_response, '/data', $expected_cacheability->getCacheTags(), $expected_cacheability->getCacheContexts(), NULL, TRUE);

    // Ensure that targeting a revision does not bypass access.
    $actual_response = $this->request('GET', $original_revision_id_url, $request_options);
    $expected_cacheability = $this->getExpectedUnauthorizedAccessCacheability();
    $detail = 'The current user is not allowed to GET the selected resource. The user does not have access to the requested version.';
    if ($result instanceof AccessResultReasonInterface && ($reason = $result->getReason()) && !empty($reason)) {
      $detail .= ' ' . $reason;
    }
    $this->assertResourceErrorResponse(403, $detail, $url, $actual_response, '/data', $expected_cacheability->getCacheTags(), $expected_cacheability->getCacheContexts(), NULL, TRUE);

    $this->setUpRevisionAuthorization('GET');

    // Ensure that the URL without a `resourceVersion` query parameter returns
    // the default revision. This is always the latest revision when
    // content_moderation is not installed.
    $actual_response = $this->request('GET', $url, $request_options);
    $expected_document = $this->alterExpectedDocumentForRevision($this->getExpectedDocument());

    // The resource object should always links to the specific revision it
    // represents.
    $expected_document['data']['links']['self']['href'] = $latest_revision_id_url->setAbsolute()->toString();
    $amend_relationship_urls($expected_document, $latest_revision_id);
    // Resource objects always link to their specific revision by revision ID.
    $expected_document['data']['attributes'][$revision_id_key] = $latest_revision_id;
    $expected_document['data']['attributes']['field_revisionable_number'] = 99;
    $expected_cache_tags = $this->getExpectedCacheTags();
    $expected_cache_contexts = $this->getExpectedCacheContexts();
    $this->assertResourceResponse(200, $expected_document, $actual_response, $expected_cache_tags, $expected_cache_contexts, NULL, TRUE);
    // Fetch the same revision using its revision ID.
    $actual_response = $this->request('GET', $latest_revision_id_url, $request_options);
    // The top-level document object's `self` link should always link to the
    // request URL.
    $expected_document['links']['self']['href'] = $latest_revision_id_url->setAbsolute()->toString();
    $this->assertResourceResponse(200, $expected_document, $actual_response, $expected_cache_tags, $expected_cache_contexts, NULL, TRUE);
    // Ensure dynamic cache HIT on second request when using a version
    // negotiator.
    $actual_response = $this->request('GET', $latest_revision_id_url, $request_options);
    $this->assertResourceResponse(200, $expected_document, $actual_response, $expected_cache_tags, $expected_cache_contexts, NULL, $this->generateDynamicPageCacheExpectedHeaderValue($expected_cache_contexts) === 'MISS' ? 'HIT' : 'UNCACHEABLE');
    // Fetch the same revision using the `latest-version` link relation type
    // negotiator. Without content_moderation, this is always the most recent
    // revision.
    $actual_response = $this->request('GET', $rel_latest_version_url, $request_options);
    $expected_document['links']['self']['href'] = $rel_latest_version_url->setAbsolute()->toString();
    $this->assertResourceResponse(200, $expected_document, $actual_response, $expected_cache_tags, $expected_cache_contexts, NULL, TRUE);
    // Fetch the same revision using the `working-copy` link relation type
    // negotiator. Without content_moderation, this is always the most recent
    // revision.
    $actual_response = $this->request('GET', $rel_working_copy_url, $request_options);
    $expected_document['links']['self']['href'] = $rel_working_copy_url->setAbsolute()->toString();
    $this->assertResourceResponse(200, $expected_document, $actual_response, $expected_cache_tags, $expected_cache_contexts, NULL, TRUE);

    // Fetch the prior revision.
    $actual_response = $this->request('GET', $original_revision_id_url, $request_options);
    $expected_document['data']['attributes'][$revision_id_key] = $original_revision_id;
    $expected_document['data']['attributes']['field_revisionable_number'] = 42;
    $expected_document['links']['self']['href'] = $original_revision_id_url->setAbsolute()->toString();
    // The resource object should always links to the specific revision it
    // represents.
    $expected_document['data']['links']['self']['href'] = $original_revision_id_url->setAbsolute()->toString();
    $amend_relationship_urls($expected_document, $original_revision_id);
    // When the resource object is not the latest version or the working copy,
    // a link should be provided that links to those versions. Therefore, the
    // presence or absence of these links communicates the state of the resource
    // object.
    $expected_document['data']['links']['latest-version']['href'] = $rel_latest_version_url->setAbsolute()->toString();
    $expected_document['data']['links']['working-copy']['href'] = $rel_working_copy_url->setAbsolute()->toString();
    $this->assertResourceResponse(200, $expected_document, $actual_response, Cache::mergeTags($expected_cache_tags, $this->getExtraRevisionCacheTags()), $expected_cache_contexts, NULL, TRUE);

    // Install content_moderation module.
    $this->assertTrue($this->container->get('module_installer')->install(['content_moderation'], TRUE), 'Installed modules.');

    if (!\Drupal::service('content_moderation.moderation_information')->canModerateEntitiesOfEntityType($this->entity->getEntityType())) {
      return;
    }

    // Set up an editorial workflow.
    $workflow = $this->createEditorialWorkflow();
    $workflow->getTypePlugin()->addEntityTypeAndBundle(static::$entityTypeId, $this->entity->bundle());
    $workflow->save();

    $this->grantPermissionsToTestedRole(['use editorial transition publish']);

    // Ensure the test entity has content_moderation fields attached to it.
    /** @var \Drupal\Core\Entity\FieldableEntityInterface|\Drupal\Core\Entity\TranslatableRevisionableInterface $entity */
    $entity = $this->entityStorage->load($entity->id());

    // Set the published moderation state on the test entity.
    $entity->set('moderation_state', 'published');
    $entity->setNewRevision();
    $entity->save();
    $default_revision_id = (int) $entity->getRevisionId();

    // Fetch the published revision by using the `rel` version negotiator and
    // the `latest-version` version argument. With content_moderation, this is
    // now the most recent revision where the moderation state was the 'default'
    // one.
    $actual_response = $this->request('GET', $rel_latest_version_url, $request_options);
    $expected_document['data']['attributes'][$revision_id_key] = $default_revision_id;
    $expected_document['data']['attributes']['moderation_state'] = 'published';
    $expected_document['data']['attributes'][$published_key] = TRUE;
    $expected_document['data']['attributes']['field_revisionable_number'] = 99;
    $expected_document['links']['self']['href'] = $rel_latest_version_url->toString();
    $expected_document['data']['attributes'][$revision_translation_affected_key] = $entity->isRevisionTranslationAffected();
    // The resource object now must link to the new revision.
    $default_revision_id_url = clone $url;
    $default_revision_id_url = $default_revision_id_url->setOption('query', ['resourceVersion' => "id:$default_revision_id"]);
    $expected_document['data']['links']['self']['href'] = $default_revision_id_url->setAbsolute()->toString();
    $amend_relationship_urls($expected_document, $default_revision_id);
    // Since the requested version is the latest version and working copy, there
    // should be no links.
    unset($expected_document['data']['links']['latest-version']);
    unset($expected_document['data']['links']['working-copy']);
    $expected_document = $this->alterExpectedDocumentForRevision($expected_document);
    $expected_cache_tags = array_unique([...$expected_cache_tags, ...$workflow->getCacheTags()]);
    $this->assertResourceResponse(200, $expected_document, $actual_response, $expected_cache_tags, $expected_cache_contexts, NULL, TRUE);
    // Fetch the collection URL using the `latest-version` version argument.
    $actual_response = $this->request('GET', $rel_latest_version_collection_url, $request_options);
    $expected_response = $this->getExpectedCollectionResponse([$entity], $rel_latest_version_collection_url->toString(), $request_options);
    $expected_collection_document = $expected_response->getResponseData();
    $expected_cacheability = $expected_response->getCacheableMetadata();
    $this->assertResourceResponse(200, $expected_collection_document, $actual_response, $expected_cacheability->getCacheTags(), $expected_cacheability->getCacheContexts(), NULL, TRUE);
    // Fetch the published revision by using the `working-copy` version
    // argument. With content_moderation, this is always the most recent
    // revision regardless of moderation state.
    $actual_response = $this->request('GET', $rel_working_copy_url, $request_options);
    $expected_document['links']['self']['href'] = $rel_working_copy_url->toString();
    $this->assertResourceResponse(200, $expected_document, $actual_response, $expected_cache_tags, $expected_cache_contexts, NULL, TRUE);
    // Fetch the collection URL using the `working-copy` version argument.
    $actual_response = $this->request('GET', $rel_working_copy_collection_url, $request_options);
    $expected_collection_document['links']['self']['href'] = $rel_working_copy_collection_url->toString();
    $this->assertResourceResponse(200, $expected_collection_document, $actual_response, $expected_cacheability->getCacheTags(), $expected_cacheability->getCacheContexts(), NULL, TRUE);
    // @todo Remove the next assertion when Drupal core supports entity query access control on revisions.
    $rel_working_copy_collection_url_filtered = clone $rel_working_copy_collection_url;
    $rel_working_copy_collection_url_filtered->setOption('query', ['filter[foo]' => 'bar'] + $rel_working_copy_collection_url->getOption('query'));
    $actual_response = $this->request('GET', $rel_working_copy_collection_url_filtered, $request_options);
    $filtered_collection_expected_cache_contexts = [
      'url.path',
      'url.query_args',
      'url.site',
    ];
    $this->assertResourceErrorResponse(501, 'JSON:API does not support filtering on revisions other than the latest version because a secure Drupal core API does not yet exist to do so.', $rel_working_copy_collection_url_filtered, $actual_response, FALSE, ['http_response'], $filtered_collection_expected_cache_contexts);
    // Fetch the collection URL using an invalid version identifier.
    $actual_response = $this->request('GET', $rel_invalid_collection_url, $request_options);
    $invalid_version_expected_cache_contexts = [
      'url.path',
      'url.query_args',
      'url.site',
    ];
    $this->assertResourceErrorResponse(400, 'Collection resources only support the following resource version identifiers: rel:latest-version, rel:working-copy', $rel_invalid_collection_url, $actual_response, FALSE, ['4xx-response', 'http_response'], $invalid_version_expected_cache_contexts);

    // Move the entity to its draft moderation state.
    $entity->set('field_revisionable_number', 42);
    // Change a relationship field so revisions can be tested on related and
    // relationship routes.
    $new_user = $this->createUser();
    $new_user->save();
    $entity->set('field_jsonapi_test_entity_ref', ['target_id' => $new_user->id()]);
    $entity->set('moderation_state', 'draft');
    $entity->setNewRevision();
    $entity->save();
    $forward_revision_id = (int) $entity->getRevisionId();

    // The `latest-version` link should *still* reference the same revision
    // since a draft is not a default revision.
    $actual_response = $this->request('GET', $rel_latest_version_url, $request_options);
    $expected_document['links']['self']['href'] = $rel_latest_version_url->toString();
    // Since the latest version is no longer also the working copy, a
    // `working-copy` link is required to indicate that there is a forward
    // revision available.
    $expected_document['data']['links']['working-copy']['href'] = $rel_working_copy_url->setAbsolute()->toString();
    $this->assertResourceResponse(200, $expected_document, $actual_response, $expected_cache_tags, $expected_cache_contexts, NULL, TRUE);
    // And the same should be true for collections.
    $actual_response = $this->request('GET', $rel_latest_version_collection_url, $request_options);
    $expected_collection_document['data'][0] = $expected_document['data'];
    $expected_collection_document['links']['self']['href'] = $rel_latest_version_collection_url->toString();
    $this->assertResourceResponse(200, $expected_collection_document, $actual_response, $expected_cacheability->getCacheTags(), $expected_cacheability->getCacheContexts(), NULL, TRUE);
    // Ensure that the `latest-version` response is same as the default link,
    // aside from the document's `self` link.
    $actual_response = $this->request('GET', $url, $request_options);
    $expected_document['links']['self']['href'] = $url->toString();
    $this->assertResourceResponse(200, $expected_document, $actual_response, $expected_cache_tags, $expected_cache_contexts, NULL, TRUE);
    // And the same should be true for collections.
    $actual_response = $this->request('GET', $collection_url, $request_options);
    $expected_collection_document['links']['self']['href'] = $collection_url->toString();
    $this->assertResourceResponse(200, $expected_collection_document, $actual_response, $expected_cacheability->getCacheTags(), $expected_cacheability->getCacheContexts(), NULL, TRUE);
    // Now, the `working-copy` link should reference the draft revision. This
    // is significant because without content_moderation, the two responses
    // would still been the same.
    //
    // Access is checked before any special permissions are granted. This
    // asserts a 403 forbidden if the user is not allowed to see unpublished
    // content.
    $result = $entity->access('view', $this->account, TRUE);
    if (!$result->isAllowed()) {
      $actual_response = $this->request('GET', $rel_working_copy_url, $request_options);
      $expected_cacheability = $this->getExpectedUnauthorizedAccessCacheability();
      $expected_cache_tags = Cache::mergeTags($expected_cacheability->getCacheTags(), $entity->getCacheTags());
      $expected_cache_contexts = $expected_cacheability->getCacheContexts();
      $detail = 'The current user is not allowed to GET the selected resource. The user does not have access to the requested version.';
      $message = $result instanceof AccessResultReasonInterface ? trim($detail . ' ' . $result->getReason()) : $detail;
      $this->assertResourceErrorResponse(403, $message, $url, $actual_response, '/data', $expected_cache_tags, $expected_cache_contexts, NULL, TRUE);
      // On the collection URL, we should expect to see the draft omitted from
      // the collection.
      $actual_response = $this->request('GET', $rel_working_copy_collection_url, $request_options);
      $expected_response = static::getExpectedCollectionResponse([$entity], $rel_working_copy_collection_url->toString(), $request_options);
      $expected_collection_document = $expected_response->getResponseData();
      $expected_collection_document['data'] = [];
      $expected_cacheability = $expected_response->getCacheableMetadata();
      $access_denied_response = static::getAccessDeniedResponse($entity, $result, $url, NULL, $detail)->getResponseData();
      static::addOmittedObject($expected_collection_document, static::errorsToOmittedObject($access_denied_response['errors']));
      $this->assertResourceResponse(200, $expected_collection_document, $actual_response, $expected_cacheability->getCacheTags(), $expected_cacheability->getCacheContexts(), NULL, TRUE);
    }

    // Since additional permissions are required to see 'draft' entities,
    // grant those permissions.
    $this->grantPermissionsToTestedRole($this->getEditorialPermissions());

    // Now, the `working-copy` link should be latest revision and be accessible.
    $actual_response = $this->request('GET', $rel_working_copy_url, $request_options);
    $expected_document['data']['attributes'][$revision_id_key] = $forward_revision_id;
    $expected_document['data']['attributes']['moderation_state'] = 'draft';
    $expected_document['data']['attributes'][$published_key] = FALSE;
    $expected_document['data']['attributes']['field_revisionable_number'] = 42;
    $expected_document['links']['self']['href'] = $rel_working_copy_url->setAbsolute()->toString();
    $expected_document['data']['attributes'][$revision_translation_affected_key] = $entity->isRevisionTranslationAffected();
    // The resource object now must link to the forward revision.
    $forward_revision_id_url = clone $url;
    $forward_revision_id_url = $forward_revision_id_url->setOption('query', ['resourceVersion' => "id:$forward_revision_id"]);
    $expected_document['data']['links']['self']['href'] = $forward_revision_id_url->setAbsolute()->toString();
    $amend_relationship_urls($expected_document, $forward_revision_id);
    // Since the working copy is not the default revision. A `latest-version`
    // link is required to indicate that the requested version is not the
    // default revision.
    unset($expected_document['data']['links']['working-copy']);
    $expected_document['data']['links']['latest-version']['href'] = $rel_latest_version_url->setAbsolute()->toString();
    $expected_cache_tags = $this->getExpectedCacheTags();
    $expected_cache_contexts = $this->getExpectedCacheContexts();
    $expected_cache_tags = array_unique([...$expected_cache_tags, ...$workflow->getCacheTags()]);
    $this->assertResourceResponse(200, $expected_document, $actual_response, Cache::mergeTags($expected_cache_tags, $this->getExtraRevisionCacheTags()), $expected_cache_contexts, NULL, TRUE);
    // And the collection response should also have the latest revision.
    $actual_response = $this->request('GET', $rel_working_copy_collection_url, $request_options);
    $expected_response = static::getExpectedCollectionResponse([$entity], $rel_working_copy_collection_url->toString(), $request_options);
    $expected_collection_document = $expected_response->getResponseData();
    $expected_collection_document['data'] = [$expected_document['data']];
    $expected_cacheability = $expected_response->getCacheableMetadata();
    $this->assertResourceResponse(200, $expected_collection_document, $actual_response, Cache::mergeTags($expected_cacheability->getCacheTags(), $this->getExtraRevisionCacheTags()), $expected_cacheability->getCacheContexts(), NULL, TRUE);

    // Test relationship responses.
    // Fetch the prior revision's relationship URL.
    $test_relationship_urls = [
      'canonical' => [
        NULL,
        $relationship_url,
        $related_url,
      ],
      'original' => [
        $original_revision_id,
        $original_revision_id_relationship_url,
        $original_revision_id_related_url,
      ],
      'latest' => [
        $latest_revision_id,
        $latest_revision_id_relationship_url,
        $latest_revision_id_related_url,
      ],
      'default' => [
        $default_revision_id,
        $rel_latest_version_relationship_url,
        $rel_latest_version_related_url,
      ],
      'forward' => [
        $forward_revision_id,
        $rel_working_copy_relationship_url,
        $rel_working_copy_related_url,
      ],
    ];
    $default_revision_types = ['canonical', 'default'];
    foreach ($test_relationship_urls as $relationship_type => $revision_case) {
      [$revision_id, $relationship_url, $related_url] = $revision_case;
      // Load the revision that will be requested.
      $this->entityStorage->resetCache([$entity->id()]);
      if ($revision_id === NULL) {
        $revision = $this->entityStorage->load($entity->id());
      }
      else {
        /** @var \Drupal\Core\Entity\RevisionableStorageInterface $storage */
        $storage = $this->entityStorage;
        $revision = $storage->loadRevision($revision_id);
      }
      // Request the relationship resource without access to the relationship
      // field.
      $actual_response = $this->request('GET', $relationship_url, $request_options);
      $expected_response = $this->getExpectedGetRelationshipResponse('field_jsonapi_test_entity_ref', $revision);
      $expected_document = $expected_response->getResponseData();
      $expected_cacheability = $expected_response->getCacheableMetadata();
      $expected_document['errors'][0]['links']['via']['href'] = $relationship_url->toString();
      // Only add node type check tags for non-default revisions.
      $expected_cache_tags = !in_array($relationship_type, $default_revision_types, TRUE) ? Cache::mergeTags($expected_cacheability->getCacheTags(), $this->getExtraRevisionCacheTags()) : $expected_cacheability->getCacheTags();
      // @todo Remove this in https://www.drupal.org/project/drupal/issues/3451483.
      $actual_response = $actual_response->withoutHeader('X-Drupal-Dynamic-Cache');
      $this->assertResourceResponse(403, $expected_document, $actual_response, $expected_cache_tags, $expected_cacheability->getCacheContexts());
      // Request the related route.
      $actual_response = $this->request('GET', $related_url, $request_options);
      // @todo Refactor self::getExpectedRelatedResponses() into a function which returns a single response.
      $expected_response = $this->getExpectedRelatedResponses(['field_jsonapi_test_entity_ref'], $request_options, $revision)['field_jsonapi_test_entity_ref'];
      $expected_document = $expected_response->getResponseData();
      $expected_cacheability = $expected_response->getCacheableMetadata();
      $expected_document['errors'][0]['links']['via']['href'] = $related_url->toString();
      // @todo Remove this in https://www.drupal.org/project/drupal/issues/3451483.
      $actual_response = $actual_response->withoutHeader('X-Drupal-Dynamic-Cache');
      $this->assertResourceResponse(403, $expected_document, $actual_response, $expected_cache_tags, $expected_cacheability->getCacheContexts());
    }
    $this->grantPermissionsToTestedRole(['field_jsonapi_test_entity_ref view access']);
    foreach ($test_relationship_urls as $relationship_type => $revision_case) {
      [$revision_id, $relationship_url, $related_url] = $revision_case;
      // Load the revision that will be requested.
      $this->entityStorage->resetCache([$entity->id()]);
      if ($revision_id === NULL) {
        $revision = $this->entityStorage->load($entity->id());
      }
      else {
        /** @var \Drupal\Core\Entity\RevisionableStorageInterface $storage */
        $storage = $this->entityStorage;
        $revision = $storage->loadRevision($revision_id);
      }
      // Request the relationship resource after granting access to the
      // relationship field.
      $actual_response = $this->request('GET', $relationship_url, $request_options);
      $expected_response = $this->getExpectedGetRelationshipResponse('field_jsonapi_test_entity_ref', $revision);
      $expected_document = $expected_response->getResponseData();
      $expected_document['links']['self']['href'] = $relationship_url->setAbsolute()->toString();
      $expected_cacheability = $expected_response->getCacheableMetadata();
      // Only add node type check tags for non-default revisions.
      $expected_cache_tags = !in_array($relationship_type, $default_revision_types, TRUE) ? Cache::mergeTags($expected_cacheability->getCacheTags(), $this->getExtraRevisionCacheTags()) : $expected_cacheability->getCacheTags();
      $this->assertResourceResponse(200, $expected_document, $actual_response, $expected_cache_tags, $expected_cacheability->getCacheContexts(), NULL, TRUE);
      // Request the related route.
      $actual_response = $this->request('GET', $related_url, $request_options);
      $expected_response = $this->getExpectedRelatedResponse('field_jsonapi_test_entity_ref', $request_options, $revision);
      $expected_document = $expected_response->getResponseData();
      $expected_cacheability = $expected_response->getCacheableMetadata();
      $expected_document['links']['self']['href'] = $related_url->toString();
      $expected_cache_tags = !in_array($relationship_type, $default_revision_types, TRUE) ? Cache::mergeTags($expected_cacheability->getCacheTags(), $this->getExtraRevisionCacheTags()) : $expected_cacheability->getCacheTags();
      $this->assertResourceResponse(200, $expected_document, $actual_response, $expected_cache_tags, $expected_cacheability->getCacheContexts(), NULL, TRUE);
    }

    $this->config('jsonapi.settings')->set('read_only', FALSE)->save(TRUE);

    // Ensures that PATCH and DELETE on individual resources with a
    // `resourceVersion` query parameter is not supported.
    $individual_urls = [
      $original_revision_id_url,
      $latest_revision_id_url,
      $rel_latest_version_url,
      $rel_working_copy_url,
    ];
    $request_options[RequestOptions::HEADERS]['Content-Type'] = 'application/vnd.api+json';
    foreach ($individual_urls as $url) {
      foreach (['PATCH', 'DELETE'] as $method) {
        $actual_response = $this->request($method, $url, $request_options);
        $this->assertResourceErrorResponse(400, sprintf('%s requests with a `%s` query parameter are not supported.', $method, 'resourceVersion'), $url, $actual_response);
      }
    }

    // Ensures that PATCH, POST and DELETE on relationship resources with a
    // `resourceVersion` query parameter is not supported.
    $relationship_urls = [
      $original_revision_id_relationship_url,
      $latest_revision_id_relationship_url,
      $rel_latest_version_relationship_url,
      $rel_working_copy_relationship_url,
    ];
    foreach ($relationship_urls as $url) {
      foreach (['PATCH', 'POST', 'DELETE'] as $method) {
        $actual_response = $this->request($method, $url, $request_options);
        $this->assertResourceErrorResponse(400, sprintf('%s requests with a `%s` query parameter are not supported.', $method, 'resourceVersion'), $url, $actual_response);
      }
    }

    // Ensures that POST on collection resources with a `resourceVersion` query
    // parameter is not supported.
    $collection_urls = [
      $rel_latest_version_collection_url,
      $rel_working_copy_collection_url,
    ];
    foreach ($collection_urls as $url) {
      foreach (['POST'] as $method) {
        $actual_response = $this->request($method, $url, $request_options);
        $this->assertResourceErrorResponse(400, sprintf('%s requests with a `%s` query parameter are not supported.', $method, 'resourceVersion'), $url, $actual_response);
      }
    }
  }

}
