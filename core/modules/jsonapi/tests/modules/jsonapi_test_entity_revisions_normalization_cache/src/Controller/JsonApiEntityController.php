<?php

namespace Drupal\jsonapi_test_entity_revisions_normalization_cache\Controller;

use Drupal\Component\Assertion\Inspector;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\RevisionableStorageInterface;
use Drupal\jsonapi\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Test controller.
 */
class JsonApiEntityController extends ControllerBase {

  /**
   * The `jsonapi_test_entity_revisions_normalization_cache.entity_resource`.
   *
   * @var \Drupal\jsonapi_test_entity_revisions_normalization_cache\Controller\EntityResource
   */
  protected $entityResource;

  /**
   * JsonApiEntityController constructor.
   *
   * @param \Drupal\jsonapi_test_entity_revisions_normalization_cache\Controller\EntityResource $entity_resource
   *   The `jsonapi_test_entity_revisions_normalization_cache.entity_resource`.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The `entity_type.manager`.
   */
  public function __construct(EntityResource $entity_resource, EntityTypeManagerInterface $entity_type_manager) {
    $this->entityResource = $entity_resource;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('jsonapi_test_entity_revisions_normalization_cache.entity_resource'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * Returns the JSON:API formatted response for entities collection.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The inbound HTTP request.
   * @param string $entity_type_id
   *   The entity type ID.
   * @param string $entity_ids
   *   The list of entity/revision IDs glued by `-`.
   * @param string $type
   *   The request type, either `revisions` or `default`.
   *
   * @return \Drupal\jsonapi\ResourceResponse
   *   The JSON:API formatted response for entities collection.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function test(Request $request, string $entity_type_id, string $entity_ids, string $type): ResourceResponse {
    $storage = $this->entityTypeManager->getStorage($entity_type_id);
    $ids = explode('-', $entity_ids);
    assert(Inspector::assertAllNumeric($ids));

    switch ($type) {
      case 'revisions':
        assert($storage instanceof RevisionableStorageInterface);
        $entities = $storage->loadMultipleRevisions($ids);
        break;

      case 'default':
        $entities = $storage->loadMultiple($ids);
        break;

      default:
        throw new \InvalidArgumentException(sprintf('The "%s" is not supported.', $type));
    }

    return $this->entityResource->getCollectionResponse($request, $entities, count($entities), 1, 50);
  }

}
