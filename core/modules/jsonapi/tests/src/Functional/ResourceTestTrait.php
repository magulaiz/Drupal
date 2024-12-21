<?php

declare(strict_types=1);

namespace Drupal\Tests\jsonapi\Functional;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\Random;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultReasonInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\ContentEntityNullStorage;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\BooleanItem;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TypedData\DataReferenceTargetDefinition;
use Drupal\Core\TypedData\TypedDataInternalPropertiesHelper;
use Drupal\Core\Url;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\jsonapi\CacheableResourceResponse;
use Drupal\jsonapi\JsonApiResource\JsonApiDocumentTopLevel;
use Drupal\jsonapi\JsonApiResource\Link;
use Drupal\jsonapi\JsonApiResource\LinkCollection;
use Drupal\jsonapi\JsonApiResource\NullIncludedData;
use Drupal\jsonapi\JsonApiResource\ResourceObject;
use Drupal\jsonapi\JsonApiResource\ResourceObjectData;
use Drupal\jsonapi\Normalizer\HttpExceptionNormalizer;
use Drupal\path\Plugin\Field\FieldType\PathItem;
use Drupal\Tests\content_moderation\Traits\ContentModerationTestTrait;
use Drupal\user\Entity\Role;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\RoleInterface;
use Drupal\user\UserInterface;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Subclass this for every JSON:API resource type.
 */
trait ResourceTestTrait {

  use ResourceResponseTestTrait;
  use ContentModerationTestTrait;
  use JsonApiRequestTestTrait;

  /**
   * The JSON:API resource type for the tested entity type plus bundle.
   *
   * Necessary for looking up public (alias) or internal (actual) field names.
   *
   * @var \Drupal\jsonapi\ResourceType\ResourceType
   */
  protected $resourceType;

  /**
   * The fields that are protected against modification during PATCH requests.
   *
   * @var string[]
   */
  protected static $patchProtectedFieldNames;

  /**
   * Fields that need unique values.
   *
   * @var string[]
   *
   * @see ::testPostIndividual()
   * @see ::getModifiedEntityForPostTesting()
   */
  protected static $uniqueFieldNames = [];

  /**
   * The entity ID for the first created entity in testPost().
   *
   * The default value of 2 should work for most content entities.
   *
   * @var string|int
   *
   * @see ::testPostIndividual()
   */
  protected static $firstCreatedEntityId = 2;

  /**
   * The entity ID for the second created entity in testPost().
   *
   * The default value of 3 should work for most content entities.
   *
   * @var string|int
   *
   * @see ::testPostIndividual()
   */
  protected static $secondCreatedEntityId = 3;

  /**
   * Specify which field is the 'label' field for testing a POST edge case.
   *
   * @var string|null
   *
   * @see ::testPostIndividual()
   */
  protected static $labelFieldName = NULL;

  /**
   * Whether new revisions of updated entities should be created by default.
   *
   * @var bool
   */
  protected static $newRevisionsShouldBeAutomatic = FALSE;

  /**
   * Whether anonymous users can view labels of this resource type.
   *
   * @var bool
   */
  protected static $anonymousUsersCanViewLabels = FALSE;

  /**
   * The standard `jsonapi` top-level document member.
   *
   * @var array
   */
  protected static $jsonApiMember = [
    'version' => '1.0',
    'meta' => [
      'links' => ['self' => ['href' => 'http://jsonapi.org/format/1.0/']],
    ],
  ];

  /**
   * The entity being tested.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * Another entity of the same type used for testing.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $anotherEntity;

  /**
   * The account to use for authentication.
   *
   * @var null|\Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityStorage;

  /**
   * The UUID key.
   *
   * @var string
   */
  protected $uuidKey;

  /**
   * The serializer service.
   *
   * @var \Symfony\Component\Serializer\Serializer
   */
  protected $serializer;

  /**
   * Performs various tasks for ::setUp().
   */
  protected function doSetUp(): void {

    $this->serializer = $this->container->get('jsonapi.serializer');

    $this->config('system.logging')->set('error_level', ERROR_REPORTING_HIDE)->save();

    // Ensure the anonymous user role has no permissions at all.
    $user_role = Role::load(RoleInterface::ANONYMOUS_ID);
    foreach ($user_role->getPermissions() as $permission) {
      $user_role->revokePermission($permission);
    }
    $user_role->save();
    assert([] === $user_role->getPermissions(), 'The anonymous user role has no permissions at all.');

    // Ensure the authenticated user role has no permissions at all.
    $user_role = Role::load(RoleInterface::AUTHENTICATED_ID);
    foreach ($user_role->getPermissions() as $permission) {
      $user_role->revokePermission($permission);
    }
    $user_role->save();
    assert([] === $user_role->getPermissions(), 'The authenticated user role has no permissions at all.');

    // Create an account, which tests will use. Also ensure the @current_user
    // service this account, to ensure certain access check logic in tests works
    // as expected.
    $this->account = $this->createUser();
    $this->container->get('current_user')->setAccount($this->account);

    // Create an entity.
    $entity_type_manager = $this->container->get('entity_type.manager');
    $this->entityStorage = $entity_type_manager->getStorage(static::$entityTypeId);
    $this->uuidKey = $entity_type_manager->getDefinition(static::$entityTypeId)
      ->getKey('uuid');
    $this->entity = $this->setUpFields($this->createEntity(), $this->account);

    $this->resourceType = $this->container->get('jsonapi.resource_type.repository')->getByTypeName(static::$resourceTypeName);
  }

  /**
   * Sets up additional fields for testing.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The primary test entity.
   * @param \Drupal\user\UserInterface $account
   *   The primary test user account.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The reloaded entity with the new fields attached.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function setUpFields(EntityInterface $entity, UserInterface $account): EntityInterface {
    if (!$entity instanceof FieldableEntityInterface) {
      return $entity;
    }

    $entity_bundle = $entity->bundle();

    // Add access-protected field.
    FieldStorageConfig::create([
      'entity_type' => static::$entityTypeId,
      'field_name' => 'field_rest_test',
      'type' => 'text',
    ])
      ->setCardinality(1)
      ->save();
    FieldConfig::create([
      'entity_type' => static::$entityTypeId,
      'field_name' => 'field_rest_test',
      'bundle' => $entity_bundle,
    ])
      ->setLabel('Test field')
      ->setTranslatable(FALSE)
      ->save();

    FieldStorageConfig::create([
      'entity_type' => static::$entityTypeId,
      'field_name' => 'field_jsonapi_test_entity_ref',
      'type' => 'entity_reference',
    ])
      ->setSetting('target_type', 'user')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->save();

    FieldConfig::create([
      'entity_type' => static::$entityTypeId,
      'field_name' => 'field_jsonapi_test_entity_ref',
      'bundle' => $entity_bundle,
    ])
      ->setTranslatable(FALSE)
      ->setSetting('handler', 'default')
      ->setSetting('handler_settings', [
        'target_bundles' => NULL,
      ])
      ->save();

    // Add multi-value field.
    FieldStorageConfig::create([
      'entity_type' => static::$entityTypeId,
      'field_name' => 'field_rest_test_multivalue',
      'type' => 'string',
    ])
      ->setCardinality(3)
      ->save();
    FieldConfig::create([
      'entity_type' => static::$entityTypeId,
      'field_name' => 'field_rest_test_multivalue',
      'bundle' => $entity_bundle,
    ])
      ->setLabel('Test field: multi-value')
      ->setTranslatable(FALSE)
      ->save();

    \Drupal::service('router.builder')->rebuildIfNeeded();

    return $this->resaveEntity($entity, $account);
  }

  protected function resaveEntity(EntityInterface $entity, AccountInterface $account): EntityInterface {
    // Reload entity so that it has the new field.
    $reloaded_entity = $this->entityLoadUnchanged($entity->id());
    // Some entity types are not stored, hence they cannot be reloaded.
    if ($reloaded_entity !== NULL) {
      $entity = $reloaded_entity;
      // Set a default value on the fields.
      $entity->set('field_rest_test', ['value' => 'All the faith he had had had had no effect on the outcome of his life.']);
      $entity->set('field_jsonapi_test_entity_ref', ['user' => $account->id()]);
      $entity->set('field_rest_test_multivalue', [['value' => 'One'], ['value' => 'Two']]);
      $entity->save();
    }

    return $entity;
  }

  /**
   * Sets up a collection of entities of the same type for testing.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   The collection of entities to test.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function getData() {
    if ($this->entityStorage->getQuery()->accessCheck(FALSE)->count()->execute() < 2) {
      $this->createAnotherEntity('two');
    }
    $query = $this->entityStorage
      ->getQuery()
      ->accessCheck(FALSE)
      ->sort($this->entity->getEntityType()->getKey('id'));
    return $this->entityStorage->loadMultiple($query->execute());
  }

  /**
   * Generates a JSON:API normalization for the given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to generate a JSON:API normalization for.
   * @param \Drupal\Core\Url $url
   *   The URL to use as the "self" link.
   *
   * @return array
   *   The JSON:API normalization for the given entity.
   */
  protected function normalize(EntityInterface $entity, Url $url) {
    // Don't use cached normalizations in tests.
    $this->container->get('cache.jsonapi_normalizations')->deleteAll();

    $self_link = new Link(new CacheableMetadata(), $url, 'self');
    $resource_type = $this->container->get('jsonapi.resource_type.repository')->getByTypeName(static::$resourceTypeName);
    $doc = new JsonApiDocumentTopLevel(new ResourceObjectData([ResourceObject::createFromEntity($resource_type, $entity)], 1), new NullIncludedData(), new LinkCollection(['self' => $self_link]));
    return $this->serializer->normalize($doc, 'api_json', [
      'resource_type' => $resource_type,
      'account' => $this->account,
    ])->getNormalization();
  }

  /**
   * Creates the entity to be tested.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The entity to be tested.
   */
  abstract protected function createEntity();

  /**
   * Creates another entity to be tested.
   *
   * @param mixed $key
   *   A unique key to be used for the ID and/or label of the duplicated entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Another entity based on $this->entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createAnotherEntity($key) {
    $duplicate = $this->getEntityDuplicate($this->entity, $key);
    // Some entity types are not stored, hence they cannot be reloaded.
    if (get_class($this->entityStorage) !== ContentEntityNullStorage::class) {
      $duplicate->set('field_rest_test', 'Second collection entity');
    }
    $duplicate->save();
    return $duplicate;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityDuplicate(EntityInterface $original, $key): EntityInterface {
    $duplicate = $original->createDuplicate();
    if ($label_key = $original->getEntityType()->getKey('label')) {
      $duplicate->set($label_key, $original->label() . '_' . $key);
    }

    $id_key = $duplicate->getEntityType()->getKey('id');
    $needs_manual_id = $duplicate instanceof ConfigEntityInterface && $id_key;

    if ($duplicate instanceof FieldableEntityInterface && $id_key) {
      $id_field = $duplicate->getFieldDefinition($id_key);
      if ($id_field->getType() !== 'integer') {
        $needs_manual_id = TRUE;
      }
    }

    if ($needs_manual_id) {
      $duplicate->set($id_key, $original->id() . '_' . $key);
    }
    return $duplicate;
  }

  /**
   * Returns the expected JSON:API document for the entity.
   *
   * @see ::createEntity()
   *
   * @return array
   *   A JSON:API response document.
   */
  abstract protected function getExpectedDocument();

  /**
   * Returns the JSON:API POST document.
   *
   * @see ::testPostIndividual()
   *
   * @return array
   *   A JSON:API request document.
   */
  abstract protected function getPostDocument();

  /**
   * Returns the JSON:API PATCH document.
   *
   * By default, reuses ::getPostDocument(), which works fine for most entity
   * types. A counter example: the 'comment' entity type.
   *
   * @see ::testPatchIndividual()
   *
   * @return array
   *   A JSON:API request document.
   */
  protected function getPatchDocument() {
    return NestedArray::mergeDeep(['data' => ['id' => $this->entity->uuid()]], $this->getPostDocument());
  }

  /**
   * Returns the expected cacheability for an unauthorized response.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   The expected cacheability.
   */
  protected function getExpectedUnauthorizedAccessCacheability() {
    return (new CacheableMetadata())
      ->setCacheTags(['4xx-response', 'http_response'])
      ->setCacheContexts(['url.query_args', 'url.site', 'user.permissions'])
      ->addCacheContexts($this->entity->getEntityType()->isRevisionable()
        ? ['url.query_args:resourceVersion']
        : []
      );
  }

  /**
   * The expected cache tags for the GET/HEAD response of the test entity.
   *
   * @param array|null $sparse_fieldset
   *   If a sparse fieldset is being requested, limit the expected cache tags
   *   for this entity's fields to just these fields.
   *
   * @return string[]
   *   A set of cache tags.
   *
   * @see ::testGetIndividual()
   */
  protected function getExpectedCacheTags(?array $sparse_fieldset = NULL) {
    $expected_cache_tags = [
      'http_response',
    ];
    return Cache::mergeTags($expected_cache_tags, $this->entity->getCacheTags());
  }

  /**
   * The expected cache tags when checking revision responses.
   *
   * @return string[]
   *   A set of cache tags.
   */
  protected function getExtraRevisionCacheTags() {
    return [];
  }

  /**
   * The expected cache contexts for the GET/HEAD response of the test entity.
   *
   * @param array|null $sparse_fieldset
   *   If a sparse fieldset is being requested, limit the expected cache
   *   contexts for this entity's fields to just these fields.
   *
   * @return string[]
   *   A set of cache contexts.
   *
   * @see ::testGetIndividual()
   */
  protected function getExpectedCacheContexts(?array $sparse_fieldset = NULL) {
    $cache_contexts = [
      // Cache contexts for JSON:API URL query parameters.
      'url.query_args',
      // Drupal defaults.
      'url.site',
      'user.permissions',
    ];
    $entity_type = $this->entity->getEntityType();
    return Cache::mergeContexts($cache_contexts, $entity_type->isRevisionable() ? ['url.query_args:resourceVersion'] : []);
  }

  /**
   * Computes the cacheability for a given entity collection.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   An account for which cacheability should be computed (cacheability is
   *   dependent on access).
   * @param \Drupal\Core\Entity\EntityInterface[] $collection
   *   The entities for which cacheability should be computed.
   * @param array $sparse_fieldset
   *   (optional) If a sparse fieldset is being requested, limit the expected
   *   cacheability for the collection entities' fields to just those in the
   *   fieldset. NULL means all fields.
   * @param bool $filtered
   *   Whether the collection is filtered or not.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   The expected cacheability for the given entity collection.
   */
  protected static function getExpectedCollectionCacheability(AccountInterface $account, array $collection, ?array $sparse_fieldset = NULL, $filtered = FALSE) {
    $cacheability = array_reduce($collection, function (CacheableMetadata $cacheability, EntityInterface $entity) use ($sparse_fieldset, $account) {
      $access_result = static::entityAccess($entity, 'view', $account);
      if (!$access_result->isAllowed()) {
        $access_result = static::entityAccess($entity, 'view label', $account)->addCacheableDependency($access_result);
      }
      $cacheability->addCacheableDependency($access_result);
      if ($access_result->isAllowed()) {
        $cacheability->addCacheableDependency($entity);
        if ($entity instanceof FieldableEntityInterface) {
          foreach ($entity as $field_name => $field_item_list) {
            /** @var \Drupal\Core\Field\FieldItemListInterface $field_item_list */
            if (is_null($sparse_fieldset) || in_array($field_name, $sparse_fieldset)) {
              $field_access = static::entityFieldAccess($entity, $field_name, 'view', $account);
              $cacheability->addCacheableDependency($field_access);
              if ($field_access->isAllowed()) {
                foreach ($field_item_list as $field_item) {
                  /** @var \Drupal\Core\Field\FieldItemInterface $field_item */
                  foreach (TypedDataInternalPropertiesHelper::getNonInternalProperties($field_item) as $property) {
                    $cacheability->addCacheableDependency(CacheableMetadata::createFromObject($property));
                  }
                }
              }
            }
          }
        }
      }
      return $cacheability;
    }, new CacheableMetadata());
    $entity_type = reset($collection)->getEntityType();
    $cacheability->addCacheTags(['http_response']);
    $cacheability->addCacheTags($entity_type->getListCacheTags());
    $cache_contexts = [
      // Cache contexts for JSON:API URL query parameters.
      'url.query_args',
      // Drupal defaults.
      'url.site',
    ];
    // If the entity type is revisionable, add a resource version cache context.
    $cache_contexts = Cache::mergeContexts($cache_contexts, $entity_type->isRevisionable() ? ['url.query_args:resourceVersion'] : []);
    $cacheability->addCacheContexts($cache_contexts);
    return $cacheability;
  }

  /**
   * Sets up the necessary authorization.
   *
   * Because of the $method parameter, it's possible to first set up
   * authorization for only GET, then add POST, et cetera. This then also
   * allows for verifying a 403 in case of missing authorization.
   *
   * @param string $method
   *   The HTTP method for which to set up authorization.
   *
   * @see ::grantPermissionsToTestedRole()
   */
  abstract protected function setUpAuthorization($method);

  /**
   * Sets up the necessary authorization for handling revisions.
   *
   * @param string $method
   *   The HTTP method for which to set up authentication.
   *
   * @see ::testRevisions()
   */
  protected function setUpRevisionAuthorization($method): void {
    assert($method === 'GET', 'Only read operations on revisions are supported.');
    $this->setUpAuthorization($method);
  }

  /**
   * Return the expected error message.
   *
   * @param string $method
   *   The HTTP method (GET, POST, PATCH, DELETE).
   *
   * @return string
   *   The error string.
   */
  protected function getExpectedUnauthorizedAccessMessage($method) {
    $permission = $this->entity->getEntityType()->getAdminPermission();
    if ($permission !== FALSE) {
      return "The '{$permission}' permission is required.";
    }

    return NULL;
  }

  /**
   * Grants permissions to the authenticated role.
   *
   * @param string[] $permissions
   *   Permissions to grant.
   */
  protected function grantPermissionsToTestedRole(array $permissions): void {
    $this->grantPermissions(Role::load(RoleInterface::AUTHENTICATED_ID), $permissions);
  }

  /**
   * Revokes permissions from the authenticated role.
   *
   * @param string[] $permissions
   *   Permissions to revoke.
   */
  protected function revokePermissionsFromTestedRole(array $permissions): void {
    $role = Role::load(RoleInterface::AUTHENTICATED_ID);
    foreach ($permissions as $permission) {
      $role->revokePermission($permission);
    }
    $role->trustData()->save();
  }

  /**
   * Ensure the anonymous and authenticated roles have no permissions at all.
   */
  protected function revokePermissions(): void {
    $user_role = Role::load(RoleInterface::ANONYMOUS_ID);
    foreach ($user_role->getPermissions() as $permission) {
      $user_role->revokePermission($permission);
    }
    $user_role->save();
    assert([] === $user_role->getPermissions(), 'The anonymous user role has no permissions at all.');

    $user_role = Role::load(RoleInterface::AUTHENTICATED_ID);
    foreach ($user_role->getPermissions() as $permission) {
      $user_role->revokePermission($permission);
    }
    $user_role->save();
    assert([] === $user_role->getPermissions(), 'The authenticated user role has no permissions at all.');
  }

  /**
   * Asserts that a resource response has the given status code and body.
   *
   * Cache max-age is not yet considered when expected header is calculated.
   *
   * @param int $expected_status_code
   *   The expected response status.
   * @param array|null|false $expected_document
   *   The expected document or NULL if there should not be a response body.
   *   FALSE in case this should not be asserted.
   * @param \Psr\Http\Message\ResponseInterface $response
   *   The response to assert.
   * @param string[]|false $expected_cache_tags
   *   (optional) The expected cache tags in the X-Drupal-Cache-Tags response
   *   header, or FALSE if that header should be absent. Defaults to FALSE.
   * @param string[]|false $expected_cache_contexts
   *   (optional) The expected cache contexts in the X-Drupal-Cache-Contexts
   *   response header, or FALSE if that header should be absent. Defaults to
   *   FALSE.
   * @param 'MISS','HIT','UNCACHEABLE (request policy)','UNCACHEABLE (response policy)'|NULL $expected_page_cache_header_value
   *   (optional) The expected X-Drupal-Cache response header value, or NULL
   *   in case of no opinion on that. For possible string values, see the
   *   parameter type hint. Defaults to NULL.
   * @param 'HIT','MISS','UNCACHEABLE (poor cacheability)','UNCACHEABLE (no cacheability)','UNCACHEABLE (request policy)','UNCACHEABLE (response policy)'|bool $expected_dynamic_page_cache_header_value
   *   (optional) The expected X-Drupal-Dynamic-Cache response header value
   *   - for possible string values, see the parameter type hint - or TRUE when
   *   the value should be autogenerated from expected cache contexts, or FALSE
   *   if that header should be absent. Defaults to FALSE.
   */
  protected function assertResourceResponse($expected_status_code, $expected_document, ResponseInterface $response, $expected_cache_tags = FALSE, $expected_cache_contexts = FALSE, $expected_page_cache_header_value = NULL, $expected_dynamic_page_cache_header_value = FALSE): void {
    $this->assertSame($expected_status_code, $response->getStatusCode(), var_export(Json::decode((string) $response->getBody()), TRUE));
    if ($expected_status_code === 204) {
      // DELETE responses should not include a Content-Type header. But Apache
      // sets it to 'text/html' by default. We also cannot detect the presence
      // of Apache either here in the CLI. For now having this documented here
      // is all we can do.
      /* $this->assertFalse($response->hasHeader('Content-Type')); */
      $this->assertSame('', (string) $response->getBody());
    }
    else {
      $this->assertSame(['application/vnd.api+json'], $response->getHeader('Content-Type'));
      if ($expected_document !== FALSE) {
        $response_document = $this->getDocumentFromResponse($response, FALSE);
        if ($expected_document === NULL) {
          $this->assertNull($response_document);
        }
        else {
          $this->assertSameDocument($expected_document, $response_document);
        }
      }
    }

    // Expected cache tags: X-Drupal-Cache-Tags header.
    $this->assertSame($expected_cache_tags !== FALSE, $response->hasHeader('X-Drupal-Cache-Tags'));
    if (is_array($expected_cache_tags)) {
      $actual_cache_tags = explode(' ', $response->getHeader('X-Drupal-Cache-Tags')[0]);

      $tag = 'config:system.logging';
      if (!in_array($tag, $expected_cache_tags) && in_array($tag, $actual_cache_tags)) {
        $expected_cache_tags[] = $tag;
      }

      $this->assertEqualsCanonicalizing($expected_cache_tags, $actual_cache_tags);
    }

    // Expected cache contexts: X-Drupal-Cache-Contexts header.
    $this->assertSame($expected_cache_contexts !== FALSE, $response->hasHeader('X-Drupal-Cache-Contexts'));
    if (is_array($expected_cache_contexts)) {
      $optimized_expected_cache_contexts = \Drupal::service('cache_contexts_manager')->optimizeTokens($expected_cache_contexts);
      $this->assertEqualsCanonicalizing($optimized_expected_cache_contexts, explode(' ', $response->getHeader('X-Drupal-Cache-Contexts')[0]));
    }

    // Expected Page Cache header value: X-Drupal-Cache header.
    if ($expected_page_cache_header_value !== NULL) {
      $this->assertTrue($response->hasHeader('X-Drupal-Cache'));
      $this->assertSame($expected_page_cache_header_value, $response->getHeader('X-Drupal-Cache')[0]);
    }

    // Expected Dynamic Page Cache header value: X-Drupal-Dynamic-Cache header.
    if ($expected_dynamic_page_cache_header_value === FALSE) {
      $this->assertFalse($response->hasHeader('X-Drupal-Dynamic-Cache'));
    }
    else {
      $this->assertTrue($response->hasHeader('X-Drupal-Dynamic-Cache'));
      $this->assertSame($expected_dynamic_page_cache_header_value === TRUE ? $this->generateDynamicPageCacheExpectedHeaderValue($expected_cache_contexts) : $expected_dynamic_page_cache_header_value, $response->getHeader('X-Drupal-Dynamic-Cache')[0]);
    }
  }

  /**
   * Asserts that an expected document matches the response body.
   *
   * @param array $expected_document
   *   The expected JSON:API document.
   * @param array $actual_document
   *   The actual response document to assert.
   */
  protected function assertSameDocument(array $expected_document, array $actual_document): void {
    static::recursiveKsort($expected_document);
    static::recursiveKsort($actual_document);

    if (!empty($expected_document['included'])) {
      static::sortResourceCollection($expected_document['included']);
      static::sortResourceCollection($actual_document['included']);
    }

    if (isset($actual_document['meta']['omitted']) && isset($expected_document['meta']['omitted'])) {
      $actual_omitted =& $actual_document['meta']['omitted'];
      $expected_omitted =& $expected_document['meta']['omitted'];
      static::sortOmittedLinks($actual_omitted);
      static::sortOmittedLinks($expected_omitted);
      static::resetOmittedLinkKeys($actual_omitted);
      static::resetOmittedLinkKeys($expected_omitted);
    }

    $expected_keys = array_keys($expected_document);
    $actual_keys = array_keys($actual_document);
    $missing_member_names = array_diff($expected_keys, $actual_keys);
    $extra_member_names = array_diff($actual_keys, $expected_keys);
    if (!empty($missing_member_names) || !empty($extra_member_names)) {
      $message_format = "The document members did not match the expected values. Missing: [ %s ]. Unexpected: [ %s ]";
      $message = sprintf($message_format, implode(', ', $missing_member_names), implode(', ', $extra_member_names));
      $this->assertEquals($expected_document, $actual_document, $message);
    }
    foreach ($expected_document as $member_name => $expected_member) {
      $actual_member = $actual_document[$member_name];
      $this->assertEqualsCanonicalizing($expected_member, $actual_member, "The '$member_name' member was not as expected.");
    }
  }

  /**
   * Asserts that a resource error response has the given message.
   *
   * Cache max-age is not yet considered when expected header is calculated.
   *
   * @param int $expected_status_code
   *   The expected response status.
   * @param string $expected_message
   *   The expected error message.
   * @param \Drupal\Core\Url|null $via_link
   *   The source URL for the errors of the response. NULL if the error occurs
   *   for example during entity creation.
   * @param \Psr\Http\Message\ResponseInterface $response
   *   The error response to assert.
   * @param string|false $pointer
   *   The expected JSON Pointer to the associated entity in the request
   *   document. See http://jsonapi.org/format/#error-objects.
   * @param string[]|false $expected_cache_tags
   *   (optional) The expected cache tags in the X-Drupal-Cache-Tags response
   *   header, or FALSE if that header should be absent. Defaults to FALSE.
   * @param string[]|false $expected_cache_contexts
   *   (optional) The expected cache contexts in the X-Drupal-Cache-Contexts
   *   response header, or FALSE if that header should be absent. Defaults to
   *   FALSE.
   * @param 'MISS','HIT','UNCACHEABLE (request policy)','UNCACHEABLE (response policy)'|NULL $expected_page_cache_header_value
   *   (optional) The expected X-Drupal-Cache response header value, or NULL
   *   in case of no opinion on that. For possible string values, see the
   *   parameter type hint. Defaults to NULL.
   * @param 'HIT','MISS','UNCACHEABLE (poor cacheability)','UNCACHEABLE (no cacheability)','UNCACHEABLE (request policy)','UNCACHEABLE (response policy)'|bool $expected_dynamic_page_cache_header_value
   *   (optional) The expected X-Drupal-Dynamic-Cache response header value
   *   - for possible string values, see the parameter type hint - or TRUE when
   *   the value should be autogenerated from expected cache contexts, or FALSE
   *   if that header should be absent. Defaults to FALSE.
   */
  protected function assertResourceErrorResponse($expected_status_code, $expected_message, $via_link, ResponseInterface $response, $pointer = FALSE, $expected_cache_tags = FALSE, $expected_cache_contexts = FALSE, $expected_page_cache_header_value = NULL, $expected_dynamic_page_cache_header_value = FALSE): void {
    assert(is_null($via_link) || $via_link instanceof Url);
    $expected_error = [];
    if (!empty(Response::$statusTexts[$expected_status_code])) {
      $expected_error['title'] = Response::$statusTexts[$expected_status_code];
    }
    $expected_error['status'] = (string) $expected_status_code;
    $expected_error['detail'] = $expected_message;
    if ($via_link) {
      $expected_error['links']['via']['href'] = $via_link->setAbsolute()->toString();
    }
    if ($info_url = HttpExceptionNormalizer::getInfoUrl($expected_status_code)) {
      $expected_error['links']['info']['href'] = $info_url;
    }
    if ($pointer !== FALSE) {
      $expected_error['source']['pointer'] = $pointer;
    }

    $expected_document = [
      'jsonapi' => static::$jsonApiMember,
      'errors' => [
        0 => $expected_error,
      ],
    ];
    $this->assertResourceResponse($expected_status_code, $expected_document, $response, $expected_cache_tags, $expected_cache_contexts, $expected_page_cache_header_value, $expected_dynamic_page_cache_header_value);
  }

  /**
   * Makes the JSON:API document violate the spec by omitting the resource type.
   *
   * @param array $document
   *   A JSON:API document.
   *
   * @return array
   *   The same JSON:API document, without its resource type.
   */
  protected function removeResourceTypeFromDocument(array $document) {
    unset($document['data']['type']);
    return $document;
  }

  /**
   * Makes the given JSON:API document invalid.
   *
   * @param array $document
   *   A JSON:API document.
   * @param string $entity_key
   *   The entity key whose normalization to make invalid.
   *
   * @return array
   *   The updated JSON:API document, now invalid.
   */
  protected function makeNormalizationInvalid(array $document, $entity_key) {
    $entity_type = $this->entity->getEntityType();
    switch ($entity_key) {
      case 'label':
        // Add a second label to this entity to make it invalid.
        $label_field = $entity_type->hasKey('label') ? $entity_type->getKey('label') : static::$labelFieldName;
        $document['data']['attributes'][$label_field] = [
          0 => $document['data']['attributes'][$label_field],
          1 => 'Second Title',
        ];
        break;

      case 'id':
        $document['data']['attributes'][$entity_type->getKey('id')] = $this->anotherEntity->id();
        break;

      case 'uuid':
        $document['data']['id'] = $this->anotherEntity->uuid();
        break;
    }

    return $document;
  }

  /**
   * Returns a JSON:API collection document for the expected entities.
   *
   * @param \Drupal\Core\Entity\EntityInterface[] $collection
   *   The entities for the collection.
   * @param string $self_link
   *   The self link for the collection response document.
   * @param array $request_options
   *   Request options to apply.
   * @param array|null $included_paths
   *   (optional) Any include paths that should be appended to the expected
   *   response.
   * @param bool $filtered
   *   Whether the collection is filtered or not.
   *
   * @return \Drupal\jsonapi\ResourceResponse
   *   A ResourceResponse for the expected entity collection.
   *
   * @see \GuzzleHttp\ClientInterface::request()
   */
  protected function getExpectedCollectionResponse(array $collection, $self_link, array $request_options, ?array $included_paths = NULL, $filtered = FALSE) {
    $resource_identifiers = array_map([static::class, 'toResourceIdentifier'], $collection);
    $individual_responses = static::toResourceResponses($this->getResponses(static::getResourceLinks($resource_identifiers), $request_options));
    $merged_response = static::toCollectionResourceResponse($individual_responses, $self_link, TRUE);

    $merged_document = $merged_response->getResponseData();
    if (!isset($merged_document['data'])) {
      $merged_document['data'] = [];
    }

    $cacheability = static::getExpectedCollectionCacheability($this->account, $collection, NULL, $filtered);
    $cacheability->setCacheMaxAge($merged_response->getCacheableMetadata()->getCacheMaxAge());

    $collection_response = new CacheableResourceResponse($merged_document);
    $collection_response->addCacheableDependency($cacheability);

    if (is_null($included_paths)) {
      return $collection_response;
    }

    $related_responses = array_reduce($collection, function ($related_responses, EntityInterface $entity) use ($included_paths, $request_options, $self_link) {
      if (!$entity->access('view', $this->account) && !$entity->access('view label', $this->account)) {
        return $related_responses;
      }
      $expected_related_responses = $this->getExpectedRelatedResponses($included_paths, $request_options, $entity);
      if (empty($related_responses)) {
        return $expected_related_responses;
      }
      foreach ($included_paths as $included_path) {
        $both_responses = [$related_responses[$included_path], $expected_related_responses[$included_path]];
        $related_responses[$included_path] = static::toCollectionResourceResponse($both_responses, $self_link, TRUE);
      }
      return $related_responses;
    }, []);

    return static::decorateExpectedResponseForIncludedFields($collection_response, $related_responses);
  }

  /**
   * Performs one round of related route testing.
   *
   * By putting this behavior in its own method, authorization and other
   * variations can be done in the calling method around assertions. For
   * example, it can be run once with an authorized user and again without one.
   *
   * @param array $request_options
   *   Request options to apply.
   *
   * @see \GuzzleHttp\ClientInterface::request()
   */
  protected function doTestRelated(array $request_options): void {
    $relationship_field_names = $this->getRelationshipFieldNames($this->entity);
    // If there are no relationship fields, we can't test related routes.
    if (empty($relationship_field_names)) {
      return;
    }
    // Builds an array of expected responses, keyed by relationship field name.
    $expected_relationship_responses = $this->getExpectedRelatedResponses($relationship_field_names, $request_options);
    // Fetches actual responses as an array keyed by relationship field name.
    $related_responses = $this->getRelatedResponses($relationship_field_names, $request_options);
    foreach ($relationship_field_names as $relationship_field_name) {
      /** @var \Drupal\jsonapi\ResourceResponse $expected_resource_response */
      $expected_resource_response = $expected_relationship_responses[$relationship_field_name];
      /** @var \Psr\Http\Message\ResponseInterface $actual_response */
      $actual_response = $related_responses[$relationship_field_name];
      // Dynamic Page Cache miss because cache should vary based on the
      // 'include' query param.
      $expected_cacheability = $expected_resource_response->getCacheableMetadata();
      $expected_dynamic_page_cache_header_value = $this->generateDynamicPageCacheExpectedHeaderValue($expected_cacheability->getCacheContexts(), $expected_cacheability->getCacheMaxAge());
      $this->assertResourceResponse(
        $expected_resource_response->getStatusCode(),
        $expected_resource_response->getResponseData(),
        $actual_response,
        $expected_cacheability->getCacheTags(),
        $expected_cacheability->getCacheContexts(),
        NULL,
        $actual_response->getStatusCode() === 200
          ? $expected_dynamic_page_cache_header_value
          : ($expected_dynamic_page_cache_header_value === 'MISS' ? FALSE : $expected_dynamic_page_cache_header_value)
      );
    }
  }

  /**
   * Performs one round of relationship route testing.
   *
   * @param array $request_options
   *   Request options to apply.
   *
   * @see \GuzzleHttp\ClientInterface::request()
   * @see ::testRelationships
   */
  protected function doTestRelationshipGet(array $request_options): void {
    $relationship_field_names = $this->getRelationshipFieldNames($this->entity);
    // If there are no relationship fields, we can't test relationship routes.
    if (empty($relationship_field_names)) {
      return;
    }

    // Test GET.
    $related_responses = $this->getRelationshipResponses($relationship_field_names, $request_options);
    foreach ($relationship_field_names as $relationship_field_name) {
      $expected_resource_response = $this->getExpectedGetRelationshipResponse($relationship_field_name);
      $expected_document = $expected_resource_response->getResponseData();
      $expected_cacheability = $expected_resource_response->getCacheableMetadata();
      $actual_response = $related_responses[$relationship_field_name];
      $expected_dynamic_page_cache_header_value = $this->generateDynamicPageCacheExpectedHeaderValue($expected_cacheability->getCacheContexts(), $expected_cacheability->getCacheMaxAge());
      $this->assertResourceResponse(
        $expected_resource_response->getStatusCode(),
        $expected_document,
        $actual_response,
        $expected_cacheability->getCacheTags(),
        $expected_cacheability->getCacheContexts(),
        NULL,
        $expected_dynamic_page_cache_header_value === 'MISS' && !$expected_resource_response->isSuccessful() ? FALSE : $expected_dynamic_page_cache_header_value
      );
    }
  }

  /**
   * Performs one round of relationship POST, PATCH and DELETE route testing.
   *
   * @param array $request_options
   *   Request options to apply.
   *
   * @see \GuzzleHttp\ClientInterface::request()
   * @see ::testRelationships
   */
  protected function doTestRelationshipMutation(array $request_options): void {
    /** @var \Drupal\Core\Entity\FieldableEntityInterface $resource */
    $resource = $this->createAnotherEntity('dupe');
    $resource->set('field_jsonapi_test_entity_ref', NULL);
    $violations = $resource->validate();
    assert($violations->count() === 0, (string) $violations);
    $resource->save();
    $target_resource = $this->createUser();
    $violations = $target_resource->validate();
    assert($violations->count() === 0, (string) $violations);
    $target_resource->save();
    $target_identifier = static::toResourceIdentifier($target_resource);
    $resource_identifier = static::toResourceIdentifier($resource);
    $relationship_field_name = 'field_jsonapi_test_entity_ref';
    /** @var \Drupal\Core\Access\AccessResultReasonInterface $update_access */
    $update_access = static::entityAccess($resource, 'update', $this->account)
      ->andIf(static::entityFieldAccess($resource, $relationship_field_name, 'edit', $this->account));
    $url = Url::fromRoute(sprintf("jsonapi.{$resource_identifier['type']}.{$relationship_field_name}.relationship.patch"), [
      'entity' => $resource->uuid(),
    ]);

    // Test POST: missing content-type.
    $response = $this->request('POST', $url, $request_options);
    $this->assertSame(415, $response->getStatusCode());

    // Set the JSON:API media type header for all subsequent requests.
    $request_options[RequestOptions::HEADERS]['Content-Type'] = 'application/vnd.api+json';

    if ($update_access->isAllowed()) {
      // Test POST: empty body.
      $response = $this->request('POST', $url, $request_options);
      $this->assertResourceErrorResponse(400, 'Empty request body.', $url, $response, FALSE);
      // Test PATCH: empty body.
      $response = $this->request('PATCH', $url, $request_options);
      $this->assertResourceErrorResponse(400, 'Empty request body.', $url, $response, FALSE);

      // Test POST: empty data.
      $request_options[RequestOptions::BODY] = Json::encode(['data' => []]);
      $response = $this->request('POST', $url, $request_options);
      $this->assertResourceResponse(204, NULL, $response);
      // Test PATCH: empty data.
      $request_options[RequestOptions::BODY] = Json::encode(['data' => []]);
      $response = $this->request('PATCH', $url, $request_options);
      $this->assertResourceResponse(204, NULL, $response);

      // Test POST: data as resource identifier, not array of identifiers.
      $request_options[RequestOptions::BODY] = Json::encode(['data' => $target_identifier]);
      $response = $this->request('POST', $url, $request_options);
      $this->assertResourceErrorResponse(400, 'Invalid body payload for the relationship.', $url, $response, FALSE);
      // Test PATCH: data as resource identifier, not array of identifiers.
      $request_options[RequestOptions::BODY] = Json::encode(['data' => $target_identifier]);
      $response = $this->request('PATCH', $url, $request_options);
      $this->assertResourceErrorResponse(400, 'Invalid body payload for the relationship.', $url, $response, FALSE);

      // Test POST: missing the 'type' field.
      $request_options[RequestOptions::BODY] = Json::encode(['data' => array_intersect_key($target_identifier, ['id' => 'id'])]);
      $response = $this->request('POST', $url, $request_options);
      $this->assertResourceErrorResponse(400, 'Invalid body payload for the relationship.', $url, $response, FALSE);
      // Test PATCH: missing the 'type' field.
      $request_options[RequestOptions::BODY] = Json::encode(['data' => array_intersect_key($target_identifier, ['id' => 'id'])]);
      $response = $this->request('PATCH', $url, $request_options);
      $this->assertResourceErrorResponse(400, 'Invalid body payload for the relationship.', $url, $response, FALSE);

      // If the base resource type is the same as that of the target's (as it
      // will be for `user--user`), then the validity error will not be
      // triggered, needlessly failing this assertion.
      if (static::$resourceTypeName !== $target_identifier['type']) {
        // Test POST: invalid target.
        $request_options[RequestOptions::BODY] = Json::encode(['data' => [$resource_identifier]]);
        $response = $this->request('POST', $url, $request_options);
        $this->assertResourceErrorResponse(400, sprintf('The provided type (%s) does not match the destination resource types (%s).', $resource_identifier['type'], $target_identifier['type']), $url, $response, FALSE);
        // Test PATCH: invalid target.
        $request_options[RequestOptions::BODY] = Json::encode(['data' => [$resource_identifier]]);
        $response = $this->request('POST', $url, $request_options);
        $this->assertResourceErrorResponse(400, sprintf('The provided type (%s) does not match the destination resource types (%s).', $resource_identifier['type'], $target_identifier['type']), $url, $response, FALSE);
      }

      // Test POST: duplicate targets, no arity.
      $request_options[RequestOptions::BODY] = Json::encode(['data' => [$target_identifier, $target_identifier]]);
      $response = $this->request('POST', $url, $request_options);
      $this->assertResourceErrorResponse(400, 'Duplicate relationships are not permitted. Use `meta.arity` to distinguish resource identifiers with matching `type` and `id` values.', $url, $response, FALSE);

      // Test PATCH: duplicate targets, no arity.
      $request_options[RequestOptions::BODY] = Json::encode(['data' => [$target_identifier, $target_identifier]]);
      $response = $this->request('PATCH', $url, $request_options);
      $this->assertResourceErrorResponse(400, 'Duplicate relationships are not permitted. Use `meta.arity` to distinguish resource identifiers with matching `type` and `id` values.', $url, $response, FALSE);

      // Test POST: success.
      $request_options[RequestOptions::BODY] = Json::encode(['data' => [$target_identifier]]);
      $response = $this->request('POST', $url, $request_options);
      $this->assertResourceResponse(204, NULL, $response);

      // Test POST: success, relationship already exists, no arity.
      $response = $this->request('POST', $url, $request_options);
      $this->assertResourceResponse(204, NULL, $response);

      // Test POST: success, relationship already exists, new arity.
      $request_options[RequestOptions::BODY] = Json::encode(['data' => [$target_identifier + ['meta' => ['arity' => 1]]]]);
      $response = $this->request('POST', $url, $request_options);
      $resource->set($relationship_field_name, [$target_resource, $target_resource]);
      $expected_document = $this->getExpectedGetRelationshipDocument($relationship_field_name, $resource);
      $expected_document['data'][0] += ['meta' => ['arity' => 0]];
      $expected_document['data'][1] += ['meta' => ['arity' => 1]];
      $this->assertResourceResponse(200, $expected_document, $response);

      // Test PATCH: success, new value is the same as given value.
      $request_options[RequestOptions::BODY] = Json::encode([
        'data' => [
          $target_identifier + ['meta' => ['arity' => 0]],
          $target_identifier + ['meta' => ['arity' => 1]],
        ],
      ]);
      $response = $this->request('PATCH', $url, $request_options);
      $this->assertResourceResponse(204, NULL, $response);

      // Test POST: success, relationship already exists, new arity.
      $request_options[RequestOptions::BODY] = Json::encode([
        'data' => [
          $target_identifier + ['meta' => ['arity' => 2]],
        ],
      ]);
      $response = $this->request('POST', $url, $request_options);
      $resource->set($relationship_field_name, [
        $target_resource,
        $target_resource,
        $target_resource,
      ]);
      $expected_document = $this->getExpectedGetRelationshipDocument($relationship_field_name, $resource);
      $expected_document['data'][0] += ['meta' => ['arity' => 0]];
      $expected_document['data'][1] += ['meta' => ['arity' => 1]];
      $expected_document['data'][2] += ['meta' => ['arity' => 2]];
      // 200 with response body because the request did not include the
      // existing relationship resource identifier object.
      $this->assertResourceResponse(200, $expected_document, $response);

      // Test POST: success.
      $request_options[RequestOptions::BODY] = Json::encode([
        'data' => [
          $target_identifier + ['meta' => ['arity' => 0]],
          $target_identifier + ['meta' => ['arity' => 1]],
        ],
      ]);
      $response = $this->request('POST', $url, $request_options);
      // 200 with response body because the request did not include the
      // resource identifier with arity 2.
      $this->assertResourceResponse(200, $expected_document, $response);

      // Test PATCH: success.
      $request_options[RequestOptions::BODY] = Json::encode([
        'data' => [
          $target_identifier + ['meta' => ['arity' => 0]],
          $target_identifier + ['meta' => ['arity' => 1]],
          $target_identifier + ['meta' => ['arity' => 2]],
        ],
      ]);
      $response = $this->request('PATCH', $url, $request_options);
      // 204 no content. PATCH data matches existing data.
      $this->assertResourceResponse(204, NULL, $response);

      // Test DELETE: three existing relationships, two removed.
      $request_options[RequestOptions::BODY] = Json::encode([
        'data' => [
          $target_identifier + ['meta' => ['arity' => 0]],
          $target_identifier + ['meta' => ['arity' => 2]],
        ],
      ]);
      $response = $this->request('DELETE', $url, $request_options);
      $this->assertResourceResponse(204, NULL, $response);
      // Subsequent GET should return only one resource identifier, with no
      // arity.
      $resource->set($relationship_field_name, [$target_resource]);
      $expected_document = $this->getExpectedGetRelationshipDocument($relationship_field_name, $resource);
      $response = $this->request('GET', $url, $request_options);
      $document = $this->getDocumentFromResponse($response);
      $this->assertSameDocument($expected_document, $document);

      // Test DELETE: one existing relationship, removed.
      $request_options[RequestOptions::BODY] = Json::encode(['data' => [$target_identifier]]);
      $response = $this->request('DELETE', $url, $request_options);
      $resource->set($relationship_field_name, []);
      $this->assertResourceResponse(204, NULL, $response);
      $expected_document = $this->getExpectedGetRelationshipDocument($relationship_field_name, $resource);
      $response = $this->request('GET', $url, $request_options);
      $document = $this->getDocumentFromResponse($response);
      $this->assertSameDocument($expected_document, $document);

      // Test DELETE: no existing relationships, no op, success.
      $request_options[RequestOptions::BODY] = Json::encode(['data' => [$target_identifier]]);
      $response = $this->request('DELETE', $url, $request_options);
      $this->assertResourceResponse(204, NULL, $response);
      $expected_document = $this->getExpectedGetRelationshipDocument($relationship_field_name, $resource);
      $response = $this->request('GET', $url, $request_options);
      $document = $this->getDocumentFromResponse($response);
      $this->assertSameDocument($expected_document, $document);

      // Test PATCH: success, new value is different than existing value.
      $request_options[RequestOptions::BODY] = Json::encode([
        'data' => [
          $target_identifier + ['meta' => ['arity' => 2]],
          $target_identifier + ['meta' => ['arity' => 3]],
        ],
      ]);
      $response = $this->request('PATCH', $url, $request_options);
      $resource->set($relationship_field_name, [$target_resource, $target_resource]);
      $expected_document = $this->getExpectedGetRelationshipDocument($relationship_field_name, $resource);
      $expected_document['data'][0] += ['meta' => ['arity' => 0]];
      $expected_document['data'][1] += ['meta' => ['arity' => 1]];
      // 200 with response body because arity values are computed; that means
      // that the PATCH arity values 2 + 3 will become 0 + 1 if there are not
      // already resource identifiers with those arity values.
      $this->assertResourceResponse(200, $expected_document, $response);

      // Test DELETE: two existing relationships, both removed because no arity
      // was specified.
      $request_options[RequestOptions::BODY] = Json::encode(['data' => [$target_identifier]]);
      $response = $this->request('DELETE', $url, $request_options);
      $resource->set($relationship_field_name, []);
      $this->assertResourceResponse(204, NULL, $response);
      $resource->set($relationship_field_name, []);
      $expected_document = $this->getExpectedGetRelationshipDocument($relationship_field_name, $resource);
      $response = $this->request('GET', $url, $request_options);
      $document = $this->getDocumentFromResponse($response);
      $this->assertSameDocument($expected_document, $document);
    }
    else {
      $request_options[RequestOptions::BODY] = Json::encode(['data' => [$target_identifier]]);
      $response = $this->request('POST', $url, $request_options);
      $message = 'The current user is not allowed to edit this relationship.';
      $message .= ($reason = $update_access->getReason()) ? ' ' . $reason : '';
      $this->assertResourceErrorResponse(403, $message, $url, $response, FALSE);
      $response = $this->request('PATCH', $url, $request_options);
      $this->assertResourceErrorResponse(403, $message, $url, $response, FALSE);
      $response = $this->request('DELETE', $url, $request_options);
      $this->assertResourceErrorResponse(403, $message, $url, $response, FALSE);
    }

    // Remove the test entities that were created.
    $resource->delete();
    $target_resource->delete();
  }

  /**
   * Gets an expected ResourceResponse for the given relationship.
   *
   * @param string $relationship_field_name
   *   The relationship for which to get an expected response.
   * @param \Drupal\Core\Entity\EntityInterface|null $entity
   *   (optional) The entity for which to get expected relationship response.
   *
   * @return \Drupal\jsonapi\CacheableResourceResponse
   *   The expected ResourceResponse.
   */
  protected function getExpectedGetRelationshipResponse($relationship_field_name, ?EntityInterface $entity = NULL): CacheableResourceResponse {
    $entity = $entity ?: $this->entity;
    $access = AccessResult::neutral()->addCacheContexts($entity->getEntityType()->isRevisionable() ? ['url.query_args'] : []);
    $access = $access->orIf(static::entityFieldAccess($entity, $this->resourceType->getInternalName($relationship_field_name), 'view', $this->account));
    if (!$access->isAllowed()) {
      $via_link = Url::fromRoute(
        sprintf('jsonapi.%s.%s.relationship.get', static::$resourceTypeName, $relationship_field_name),
        ['entity' => $entity->uuid()]
      );
      return static::getAccessDeniedResponse($this->entity, $access, $via_link, $relationship_field_name, 'The current user is not allowed to view this relationship.', FALSE);
    }
    $expected_document = $this->getExpectedGetRelationshipDocument($relationship_field_name, $entity);
    $expected_cacheability = (new CacheableMetadata())
      ->addCacheTags(['http_response'])
      ->addCacheContexts([
        'url.site',
        'url.query_args',
      ])
      ->addCacheableDependency($entity)
      ->addCacheableDependency($access);
    $status_code = $expected_document['errors'][0]['status'] ?? 200;
    $resource_response = new CacheableResourceResponse($expected_document, $status_code);
    $resource_response->addCacheableDependency($expected_cacheability);
    return $resource_response;
  }

  /**
   * Gets an expected document for the given relationship.
   *
   * @param string $relationship_field_name
   *   The relationship for which to get an expected response.
   * @param \Drupal\Core\Entity\EntityInterface|null $entity
   *   (optional) The entity for which to get expected relationship document.
   *
   * @return array
   *   The expected document array.
   */
  protected function getExpectedGetRelationshipDocument($relationship_field_name, ?EntityInterface $entity = NULL) {
    $entity = $entity ?: $this->entity;
    $entity_type_id = $entity->getEntityTypeId();
    $bundle = $entity->bundle();
    $id = $entity->uuid();
    $self_link = Url::fromUri("base:/jsonapi/$entity_type_id/$bundle/$id/relationships/$relationship_field_name")->setAbsolute();
    $related_link = Url::fromUri("base:/jsonapi/$entity_type_id/$bundle/$id/$relationship_field_name")->setAbsolute();
    if (static::$resourceTypeIsVersionable) {
      assert($entity instanceof RevisionableInterface);
      $version_query = ['resourceVersion' => 'id:' . $entity->getRevisionId()];
      $related_link->setOption('query', $version_query);
    }
    $data = $this->getExpectedGetRelationshipDocumentData($relationship_field_name, $entity);
    return [
      'data' => $data,
      'jsonapi' => static::$jsonApiMember,
      'links' => [
        'self' => ['href' => $self_link->toString(TRUE)->getGeneratedUrl()],
        'related' => ['href' => $related_link->toString(TRUE)->getGeneratedUrl()],
      ],
    ];
  }

  /**
   * Gets the expected document data for the given relationship.
   *
   * @param string $relationship_field_name
   *   The relationship for which to get an expected response.
   * @param \Drupal\Core\Entity\EntityInterface|null $entity
   *   (optional) The entity for which to get expected relationship data.
   *
   * @return ?array
   *   The expected document data.
   */
  protected function getExpectedGetRelationshipDocumentData($relationship_field_name, ?EntityInterface $entity = NULL): ?array {
    $entity = $entity ?: $this->entity;
    $internal_field_name = $this->resourceType->getInternalName($relationship_field_name);
    /** @var \Drupal\Core\Field\FieldItemListInterface $field */
    $field = $entity->{$internal_field_name};
    $is_multiple = $field->getFieldDefinition()->getFieldStorageDefinition()->getCardinality() !== 1;
    if ($field->isEmpty()) {
      return $is_multiple ? [] : NULL;
    }
    if (!$is_multiple) {
      $target_entity = $field->entity;
      if (is_null($target_entity)) {
        return NULL;
      }

      $resource_identifier = static::toResourceIdentifier($target_entity);
      $resource_identifier = static::decorateResourceIdentifierWithDrupalInternalTargetId($field, $resource_identifier);
      return $resource_identifier;
    }
    else {
      $arity_counter = [];
      $relation_list = array_filter(array_map(function ($item) use (&$arity_counter) {
        $target_entity = $item->entity;

        if (is_null($target_entity)) {
          return NULL;
        }

        $resource_identifier = static::toResourceIdentifier($target_entity);
        $resource_identifier = static::decorateResourceIdentifierWithDrupalInternalTargetId($item, $resource_identifier);
        $type = $resource_identifier['type'];
        $id = $resource_identifier['id'];
        // Start the count of identifiers sharing a single type and ID at 1.
        if (!isset($arity_counter[$type][$id])) {
          $arity_counter[$type][$id] = 1;
        }
        else {
          $arity_counter[$type][$id] += 1;
        }
        return $resource_identifier;
      }, iterator_to_array($field)));

      $arity_map = [];
      $relation_list = array_map(function ($identifier) use ($arity_counter, &$arity_map) {
        $type = $identifier['type'];
        $id = $identifier['id'];
        // Only add an arity value if there are two or more resource identifiers
        // with the same type and ID.
        if (($arity_counter[$type][$id] ?? 0) > 1) {
          // Arity is indexed from 0. If the array key isn't set, 1 + (-1) = 0.
          if (!isset($arity_map[$type][$id])) {
            $arity_map[$type][$id] = 0;
          }
          else {
            $arity_map[$type][$id] += 1;
          }
          $identifier['meta']['arity'] = $arity_map[$type][$id];
        }
        return $identifier;
      }, $relation_list);

      return $relation_list;
    }
  }

  /**
   * Adds drupal_internal__target_id to the meta of a resource identifier.
   *
   * @param \Drupal\Core\Field\FieldItemInterface|\Drupal\Core\Field\FieldItemListInterface $field
   *   The field containing the entity that is described by the
   *   resource_identifier.
   * @param array $resource_identifier
   *   A resource identifier for an entity.
   *
   * @return array
   *   A resource identifier for an entity with drupal_internal__target_id set
   *   if appropriate.
   */
  protected static function decorateResourceIdentifierWithDrupalInternalTargetId($field, array $resource_identifier): array {
    $property_definitions = $field->getFieldDefinition()->getFieldStorageDefinition()->getPropertyDefinitions();

    if (!isset($property_definitions['target_id'])) {
      return $resource_identifier;
    }

    $is_data_reference_definition = $property_definitions['target_id'] instanceof DataReferenceTargetDefinition;
    if ($is_data_reference_definition) {
      // Numeric target IDs usually reference content entities, which use an
      // auto-incrementing integer ID. Non-numeric target IDs usually reference
      // config entities, which use a machine-name as an ID.
      $resource_identifier['meta']['drupal_internal__target_id'] = is_numeric($field->target_id)
        ? (int) $field->target_id
        : $field->target_id;
    }
    return $resource_identifier;
  }

  /**
   * Builds an array of expected related ResourceResponses, keyed by field name.
   *
   * @param array $relationship_field_names
   *   The relationship field names for which to build expected
   *   ResourceResponses.
   * @param array $request_options
   *   Request options to apply.
   * @param \Drupal\Core\Entity\EntityInterface|null $entity
   *   (optional) The entity for which to get expected related resources.
   *
   * @return \Drupal\jsonapi\ResourceResponse[]
   *   An array of expected ResourceResponses, keyed by their relationship field
   *   name.
   *
   * @see \GuzzleHttp\ClientInterface::request()
   */
  protected function getExpectedRelatedResponses(array $relationship_field_names, array $request_options, ?EntityInterface $entity = NULL) {
    $entity = $entity ?: $this->entity;
    return array_map(function ($relationship_field_name) use ($entity, $request_options) {
      return $this->getExpectedRelatedResponse($relationship_field_name, $request_options, $entity);
    }, array_combine($relationship_field_names, $relationship_field_names));
  }

  /**
   * Builds an expected related ResourceResponse for the given field.
   *
   * @param string $relationship_field_name
   *   The relationship field name for which to build an expected
   *   ResourceResponse.
   * @param array $request_options
   *   Request options to apply.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity for which to get expected related resources.
   *
   * @return \Drupal\jsonapi\ResourceResponse
   *   An expected ResourceResponse.
   *
   * @see \GuzzleHttp\ClientInterface::request()
   */
  protected function getExpectedRelatedResponse($relationship_field_name, array $request_options, EntityInterface $entity) {
    // Get the relationships responses which contain resource identifiers for
    // every related resource.
    $base_resource_identifier = static::toResourceIdentifier($entity);
    $internal_name = $this->resourceType->getInternalName($relationship_field_name);
    $access = AccessResult::neutral()->addCacheContexts($entity->getEntityType()->isRevisionable() ? ['url.query_args'] : []);
    $access = $access->orIf(static::entityFieldAccess($entity, $internal_name, 'view', $this->account));
    if (!$access->isAllowed()) {
      $detail = 'The current user is not allowed to view this relationship.';
      if (!$entity->access('view') && $entity->access('view label') && $access instanceof AccessResultReasonInterface && empty($access->getReason())) {
        $access->setReason("The user only has authorization for the 'view label' operation.");
      }
      $via_link = Url::fromRoute(
        sprintf('jsonapi.%s.%s.related', $base_resource_identifier['type'], $relationship_field_name),
        ['entity' => $base_resource_identifier['id']]
      );
      $related_response = static::getAccessDeniedResponse($entity, $access, $via_link, $relationship_field_name, $detail, FALSE);
    }
    else {
      $self_link = static::getRelatedLink($base_resource_identifier, $relationship_field_name);
      $relationship_response = $this->getExpectedGetRelationshipResponse($relationship_field_name, $entity);
      $relationship_document = $relationship_response->getResponseData();
      // The relationships may be empty, in which case we shouldn't attempt to
      // fetch the individual identified resources.
      if (empty($relationship_document['data'])) {
        $cache_contexts = Cache::mergeContexts([
          // Cache contexts for JSON:API URL query parameters.
          'url.query_args',
          // Drupal defaults.
          'url.site',
        ], $this->entity->getEntityType()->isRevisionable() ? ['url.query_args:resourceVersion'] : []);
        $cacheability = (new CacheableMetadata())->addCacheContexts($cache_contexts)->addCacheTags(['http_response']);
        if (isset($relationship_document['errors'])) {
          $related_response = $relationship_response;
        }
        else {
          $cardinality = is_null($relationship_document['data']) ? 1 : -1;
          $related_response = (new CacheableResourceResponse(static::getEmptyCollectionResponse($cardinality, $self_link)->getResponseData()))->addCacheableDependency($cacheability);
        }
      }
      else {
        $is_to_one_relationship = static::isResourceIdentifier($relationship_document['data']);
        $resource_identifiers = $is_to_one_relationship
          ? [$relationship_document['data']]
          : $relationship_document['data'];
        // Remove any relationships to 'virtual' resources.
        $resource_identifiers = array_filter($resource_identifiers, function ($resource_identifier) {
          return $resource_identifier['id'] !== 'virtual';
        });
        if (!empty($resource_identifiers)) {
          $individual_responses = static::toResourceResponses($this->getResponses(static::getResourceLinks($resource_identifiers), $request_options));
          $related_response = static::toCollectionResourceResponse($individual_responses, $self_link, !$is_to_one_relationship);
        }
        else {
          $cardinality = $is_to_one_relationship ? 1 : -1;
          $related_response = static::getEmptyCollectionResponse($cardinality, $self_link);
        }
      }
      $related_response->addCacheableDependency($relationship_response->getCacheableMetadata());
    }
    return $related_response;
  }

  /**
   * Recursively sorts an array by key.
   *
   * @param array $array
   *   An array to sort.
   */
  protected static function recursiveKsort(array &$array): void {
    // First, sort the main array.
    ksort($array);

    // Then check for child arrays.
    foreach ($array as $key => &$value) {
      if (is_array($value)) {
        static::recursiveKsort($value);
      }
    }
  }

  /**
   * Returns Guzzle request options for authentication.
   *
   * @return array
   *   Guzzle request options to use for authentication.
   *
   * @see \GuzzleHttp\ClientInterface::request()
   */
  protected function getAuthenticationRequestOptions() {
    return [
      'headers' => [
        'Authorization' => 'Basic ' . base64_encode($this->account->name->value . ':' . $this->account->passRaw),
      ],
    ];
  }

  /**
   * Clones the given entity and modifies all PATCH-protected fields.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity being tested and to modify.
   *
   * @return array
   *   Contains two items:
   *   1. The modified entity object.
   *   2. The original field values, keyed by field name.
   *
   * @internal
   */
  protected static function getModifiedEntityForPatchTesting(EntityInterface $entity) {
    $modified_entity = clone $entity;
    $original_values = [];
    foreach (array_keys(static::$patchProtectedFieldNames) as $field_name) {
      $field = $modified_entity->get($field_name);
      $original_values[$field_name] = $field->getValue();
      switch ($field->getItemDefinition()->getClass()) {
        case BooleanItem::class:
          // BooleanItem::generateSampleValue() picks either 0 or 1. So a 50%
          // chance of not picking a different value.
          $field->value = ((int) $field->value) === 1 ? '0' : '1';
          break;

        case PathItem::class:
          // PathItem::generateSampleValue() doesn't set a PID, which causes
          // PathItem::postSave() to fail. Keep the PID (and other properties),
          // just modify the alias.
          $field->alias = str_replace(' ', '-', strtolower((new Random())->sentences(3)));
          break;

        default:
          $original_field = clone $field;
          while ($field->equals($original_field)) {
            $field->generateSampleItems();
          }
          break;
      }
    }

    return [$modified_entity, $original_values];
  }

  /**
   * Gets the normalized POST entity with random values for its unique fields.
   *
   * @see ::testPostIndividual
   * @see ::getPostDocument
   *
   * @return array
   *   An array structure as returned by ::getNormalizedPostEntity().
   */
  protected function getModifiedEntityForPostTesting() {
    $document = $this->getPostDocument();

    // Ensure that all the unique fields of the entity type get a new random
    // value.
    foreach (static::$uniqueFieldNames as $field_name) {
      $field_definition = $this->entity->getFieldDefinition($field_name);
      $field_type_class = $field_definition->getItemDefinition()->getClass();
      $document['data']['attributes'][$field_name] = $field_type_class::generateSampleValue($field_definition);
    }

    return $document;
  }

  /**
   * Tests sparse field sets.
   *
   * @param \Drupal\Core\Url $url
   *   The base URL with which to test includes.
   * @param array $request_options
   *   Request options to apply.
   *
   * @see \GuzzleHttp\ClientInterface::request()
   */
  protected function doTestSparseFieldSets(Url $url, array $request_options): void {
    $field_sets = $this->getSparseFieldSets();
    $expected_cacheability = new CacheableMetadata();
    foreach ($field_sets as $type => $field_set) {
      if ($type === 'all') {
        assert($this->getExpectedCacheTags($field_set) === $this->getExpectedCacheTags());
        assert($this->getExpectedCacheContexts($field_set) === $this->getExpectedCacheContexts());
      }
      $query = ['fields[' . static::$resourceTypeName . ']' => implode(',', $field_set)];
      $expected_document = $this->getExpectedDocument();
      $expected_cacheability->setCacheTags($this->getExpectedCacheTags($field_set));
      $expected_cacheability->setCacheContexts($this->getExpectedCacheContexts($field_set));
      // This tests sparse field sets on included entities.
      if (str_starts_with($type, 'nested')) {
        $this->grantPermissionsToTestedRole(['access user profiles']);
        $query['fields[user--user]'] = implode(',', $field_set);
        $query['include'] = 'uid';
        $owner = $this->entity->getOwner();
        $owner_resource = static::toResourceIdentifier($owner);
        foreach ($field_set as $field_name) {
          $owner_resource['attributes'][$field_name] = $this->serializer->normalize($owner->get($field_name)[0]->get('value'), 'api_json');
        }
        $owner_resource['links']['self']['href'] = static::getResourceLink($owner_resource);
        $expected_document['included'] = [$owner_resource];
        $expected_cacheability->addCacheableDependency($owner);
        $expected_cacheability->addCacheableDependency(static::entityAccess($owner, 'view', $this->account));
      }
      // Remove fields not in the sparse field set.
      foreach (['attributes', 'relationships'] as $member) {
        if (!empty($expected_document['data'][$member])) {
          $remaining = array_intersect_key(
            $expected_document['data'][$member],
            array_flip($field_set)
          );
          if (empty($remaining)) {
            unset($expected_document['data'][$member]);
          }
          else {
            $expected_document['data'][$member] = $remaining;
          }
        }
      }
      $url->setOption('query', $query);
      // 'self' link should include the 'fields' query param.
      $expected_document['links']['self']['href'] = $url->setAbsolute()->toString();

      $response = $this->request('GET', $url, $request_options);
      $this->assertResourceResponse(
        200,
        $expected_document,
        $response,
        $expected_cacheability->getCacheTags(),
        $expected_cacheability->getCacheContexts(),
        'UNCACHEABLE (request policy)',
       TRUE,
      );
    }
    // Test Dynamic Page Cache HIT for a query with the same field set (unless
    // expensive cache context is present).
    $response = $this->request('GET', $url, $request_options);
    $this->assertResourceResponse(200, FALSE, $response, $expected_cacheability->getCacheTags(), $expected_cacheability->getCacheContexts(), 'UNCACHEABLE (request policy)', $this->generateDynamicPageCacheExpectedHeaderValue($expected_cacheability->getCacheContexts()) === 'MISS' ? 'HIT' : 'UNCACHEABLE (poor cacheability)');
  }

  /**
   * Tests included resources.
   *
   * @param \Drupal\Core\Url $url
   *   The base URL with which to test includes.
   * @param array $request_options
   *   Request options to apply.
   *
   * @see \GuzzleHttp\ClientInterface::request()
   */
  protected function doTestIncluded(Url $url, array $request_options): void {
    $relationship_field_names = $this->getRelationshipFieldNames($this->entity);
    // If there are no relationship fields, we can't include anything.
    if (empty($relationship_field_names)) {
      return;
    }

    $field_sets = [
      'empty' => [],
      'all' => $relationship_field_names,
    ];
    if (count($relationship_field_names) > 1) {
      $about_half_the_fields = (int) floor(count($relationship_field_names) / 2);
      $field_sets['some'] = array_slice($relationship_field_names, $about_half_the_fields);

      $nested_includes = $this->getNestedIncludePaths();
      if (!empty($nested_includes) && !in_array($nested_includes, $field_sets)) {
        $field_sets['nested'] = $nested_includes;
      }
    }

    foreach ($field_sets as $type => $included_paths) {
      $this->grantIncludedPermissions($included_paths);
      $query = ['include' => implode(',', $included_paths)];
      $url->setOption('query', $query);
      $actual_response = $this->request('GET', $url, $request_options);
      $expected_response = $this->getExpectedIncludedResourceResponse($included_paths, $request_options);
      $expected_document = $expected_response->getResponseData();
      // Dynamic Page Cache miss because cache should vary based on the
      // 'include' query param.
      $expected_cacheability = $expected_response->getCacheableMetadata();
      $this->assertResourceResponse(
        200,
        $expected_document,
        $actual_response,
        $expected_cacheability->getCacheTags(),
        $expected_cacheability->getCacheContexts(),
        NULL,
        $this->generateDynamicPageCacheExpectedHeaderValue($this->getExpectedCacheContexts(), $expected_cacheability->getCacheMaxAge())
      );
    }
  }

  /**
   * Decorates the expected response with included data and cache metadata.
   *
   * This adds the expected includes to the expected document and also builds
   * the expected cacheability for those includes. It does so based of responses
   * from the related routes for individual relationships.
   *
   * @param \Drupal\jsonapi\CacheableResourceResponse $expected_response
   *   The expected ResourceResponse.
   * @param \Drupal\jsonapi\ResourceResponse[] $related_responses
   *   The related ResourceResponses, keyed by relationship field names.
   *
   * @return \Drupal\jsonapi\CacheableResourceResponse
   *   The decorated ResourceResponse.
   */
  protected static function decorateExpectedResponseForIncludedFields(CacheableResourceResponse $expected_response, array $related_responses) {
    $expected_document = $expected_response->getResponseData();
    $expected_cacheability = $expected_response->getCacheableMetadata();
    foreach ($related_responses as $related_response) {
      $related_document = $related_response->getResponseData();
      $expected_cacheability->addCacheableDependency($related_response->getCacheableMetadata());
      $expected_cacheability->setCacheTags(array_values(array_diff($expected_cacheability->getCacheTags(), ['4xx-response'])));
      // If any of the related response documents had omitted items or errors,
      // we should later expect the document to have omitted items as well.
      if (!empty($related_document['errors'])) {
        static::addOmittedObject($expected_document, static::errorsToOmittedObject($related_document['errors']));
      }
      if (!empty($related_document['meta']['omitted'])) {
        static::addOmittedObject($expected_document, $related_document['meta']['omitted']);
      }
      if (isset($related_document['data'])) {
        $related_data = $related_document['data'];
        $related_resources = (static::isResourceIdentifier($related_data))
          ? [$related_data]
          : $related_data;
        foreach ($related_resources as $related_resource) {
          if (empty($expected_document['included']) || !static::collectionHasResourceIdentifier($related_resource, $expected_document['included'])) {
            $expected_document['included'][] = $related_resource;
          }
        }
      }
    }
    return (new CacheableResourceResponse($expected_document))->addCacheableDependency($expected_cacheability);
  }

  /**
   * Gets the expected individual ResourceResponse for GET.
   *
   * @return \Drupal\jsonapi\CacheableResourceResponse
   *   The expected individual ResourceResponse.
   */
  protected function getExpectedGetIndividualResourceResponse($status_code = 200) {
    $resource_response = new CacheableResourceResponse($this->getExpectedDocument(), $status_code);
    $cacheability = new CacheableMetadata();
    $cacheability->setCacheContexts($this->getExpectedCacheContexts());
    $cacheability->setCacheTags($this->getExpectedCacheTags());
    return $resource_response->addCacheableDependency($cacheability);
  }

  /**
   * Returns an array of sparse fields sets to test.
   *
   * @return array
   *   An array of sparse field sets (an array of field names), keyed by a label
   *   for the field set.
   */
  protected function getSparseFieldSets() {
    $field_names = array_keys($this->entity->toArray());
    $field_sets = [
      'empty' => [],
      'some' => array_slice($field_names, (int) floor(count($field_names) / 2)),
      'all' => $field_names,
    ];
    if ($this->entity instanceof EntityOwnerInterface) {
      $field_sets['nested_empty_fieldset'] = $field_sets['empty'];
      $field_sets['nested_fieldset_with_owner_fieldset'] = ['name', 'created'];
    }
    return $field_sets;
  }

  /**
   * Gets a list of public relationship names for the resource type under test.
   *
   * @param \Drupal\Core\Entity\EntityInterface|null $entity
   *   (optional) The entity for which to get relationship field names.
   *
   * @return array
   *   An array of relationship field names.
   */
  protected function getRelationshipFieldNames(?EntityInterface $entity = NULL) {
    $entity = $entity ?: $this->entity;
    // Only content entity types can have relationships.
    $fields = $entity instanceof ContentEntityInterface
      ? iterator_to_array($entity)
      : [];
    return array_reduce($fields, function ($field_names, $field) {
      /** @var \Drupal\Core\Field\FieldItemListInterface $field */
      if (static::isReferenceFieldDefinition($field->getFieldDefinition())) {
        $field_names[] = $this->resourceType->getPublicName($field->getName());
      }
      return $field_names;
    }, []);
  }

  /**
   * Authorize the user under test with additional permissions to view includes.
   *
   * @return array
   *   An array of special permissions to be granted for certain relationship
   *   paths where the keys are relationships paths and values are an array of
   *   permissions.
   */
  protected static function getIncludePermissions() {
    return [];
  }

  /**
   * Gets an array of permissions required to view and update any tested entity.
   *
   * @return string[]
   *   An array of permission names.
   */
  protected function getEditorialPermissions() {
    return ['view latest version', "view any unpublished content"];
  }

  /**
   * Checks access for the given operation on the given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity for which to check field access.
   * @param string $operation
   *   The operation for which to check access.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account for which to check access.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The AccessResult.
   */
  protected static function entityAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    // The default entity access control handler assumes that permissions do not
    // change during the lifetime of a request and caches access results.
    // However, we're changing permissions during a test run and need fresh
    // results, so reset the cache.
    \Drupal::entityTypeManager()->getAccessControlHandler($entity->getEntityTypeId())->resetCache();
    return $entity->access($operation, $account, TRUE);
  }

  /**
   * Checks access for the given field operation on the given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity for which to check field access.
   * @param string $field_name
   *   The field for which to check access.
   * @param string $operation
   *   The operation for which to check access.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account for which to check access.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The AccessResult.
   */
  protected static function entityFieldAccess(EntityInterface $entity, $field_name, $operation, AccountInterface $account) {
    $entity_access = static::entityAccess($entity, $operation === 'edit' ? 'update' : 'view', $account);
    $field_access = $entity->{$field_name}->access($operation, $account, TRUE);
    return $entity_access->andIf($field_access);
  }

  /**
   * Gets an array of all nested include paths to be tested.
   *
   * @param int $depth
   *   (optional) The maximum depth to which included paths should be nested.
   *
   * @return array
   *   An array of nested include paths.
   */
  protected function getNestedIncludePaths($depth = 3) {
    $get_nested_relationship_field_names = function (EntityInterface $entity, $depth, $path = "") use (&$get_nested_relationship_field_names) {
      $relationship_field_names = $this->getRelationshipFieldNames($entity);
      if ($depth > 0) {
        $paths = [];
        foreach ($relationship_field_names as $field_name) {
          $next = ($path) ? "$path.$field_name" : $field_name;
          $internal_field_name = $this->resourceType->getInternalName($field_name);
          if ($target_entity = $entity->{$internal_field_name}->entity) {
            $deep = $get_nested_relationship_field_names($target_entity, $depth - 1, $next);
            $paths = array_merge($paths, $deep);
          }
          else {
            $paths[] = $next;
          }
        }
        return $paths;
      }
      return array_map(function ($target_name) use ($path) {
        return "$path.$target_name";
      }, $relationship_field_names);
    };
    return $get_nested_relationship_field_names($this->entity, $depth);
  }

  /**
   * Determines if a given field definition is a reference field.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition to inspect.
   *
   * @return bool
   *   TRUE if the field definition is found to be a reference field. FALSE
   *   otherwise.
   */
  protected static function isReferenceFieldDefinition(FieldDefinitionInterface $field_definition): bool {
    /** @var \Drupal\Core\Field\TypedData\FieldItemDataDefinition $item_definition */
    $item_definition = $field_definition->getItemDefinition();
    $main_property = $item_definition->getMainPropertyName();
    $property_definition = $item_definition->getPropertyDefinition($main_property);
    return $property_definition instanceof DataReferenceTargetDefinition;
  }

  /**
   * Grants authorization to view includes.
   *
   * @param string[] $include_paths
   *   An array of include paths for which to grant access.
   */
  protected function grantIncludedPermissions(array $include_paths = []): void {
    $applicable_permissions = array_intersect_key(static::getIncludePermissions(), array_flip($include_paths));
    $flattened_permissions = array_unique(array_reduce($applicable_permissions, 'array_merge', []));
    // Always grant access to 'view' the test entity reference field.
    $flattened_permissions[] = 'field_jsonapi_test_entity_ref view access';
    $this->grantPermissionsToTestedRole($flattened_permissions);
  }

  /**
   * Loads an entity in the test container, ignoring the static cache.
   *
   * @param int $id
   *   The entity ID.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The loaded entity.
   *
   * @todo Remove this after https://www.drupal.org/project/drupal/issues/3038706 lands.
   */
  protected function entityLoadUnchanged($id) {
    $this->entityStorage->resetCache();
    return $this->entityStorage->loadUnchanged($id);
  }

  /**
   * Alters the expected JSON:API document for revisions.
   *
   * Default revision tests assume a non-privileged user is performing the GET
   * request and as such the expected document may not include the revision log
   * or other fields that require elevated permissions. This method is an
   * extension point where child classes can modify the expected document to
   * take into account these changes.
   *
   * @param array $expected_document
   *   Expected document for the default revision.
   *
   * @return array[]
   *   Modified document for a revision or user with access to edit a revision
   *   AND/OR view revision information.
   */
  protected function alterExpectedDocumentForRevision(array $expected_document): array {
    $entity_type = $this->entity->getEntityType();
    if ($entity_type instanceof ContentEntityTypeInterface &&
      ($field_name = $entity_type->getRevisionMetadataKey('revision_log_message'))) {
      // The default entity access control handler assumes that permissions do not
      // change during the lifetime of a request and caches access results.
      // However, we're changing permissions during a test run and need fresh
      // results, so reset the cache.
      \Drupal::entityTypeManager()->getAccessControlHandler($this->entity->getEntityTypeId())->resetCache();
      $revisionLogAccess = $this->entity->access('view revision', $this->account, TRUE)
        ->orIf($this->entity->access('update', $this->account, TRUE));

      if ($revisionLogAccess->isAllowed()) {
        $expected_document['data']['attributes'][$field_name] = NULL;
        return $expected_document;
      }
      unset($expected_document['data']['attributes'][$field_name]);
    }
    return $expected_document;
  }

  /**
   * Generates an X-Drupal-Dynamic-Cache header value based on cacheability.
   *
   * @param array $cache_context
   *   Cache context.
   * @param int|null $cache_max_age
   *   (optional) Cache max age.
   *
   * @return 'UNCACHEABLE (poor cacheability)'|'MISS'
   *   The X-Drupal-Dynamic-Cache header value.
   */
  protected function generateDynamicPageCacheExpectedHeaderValue(array $cache_context, ?int $cache_max_age = NULL): string {
    // MISS or UNCACHEABLE (poor cacheability) depends on data.
    // It must not be HIT.
    return $cache_max_age === 0 || !empty(array_intersect(['user', 'session'], $cache_context)) ? 'UNCACHEABLE (poor cacheability)' : 'MISS';
  }

}
