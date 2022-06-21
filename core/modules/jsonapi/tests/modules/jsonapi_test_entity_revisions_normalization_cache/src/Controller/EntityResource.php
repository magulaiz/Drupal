<?php

namespace Drupal\jsonapi_test_entity_revisions_normalization_cache\Controller;

use Drupal\jsonapi\Controller\EntityResource as EntityResourceBase;
use Drupal\jsonapi\JsonApiResource\NullIncludedData;
use Drupal\jsonapi\JsonApiResource\ResourceObjectData;
use Drupal\jsonapi\Query\OffsetPage;
use Drupal\jsonapi\ResourceResponse;
use Drupal\jsonapi\Routing\Routes;
use Symfony\Component\HttpFoundation\Request;

/**
 * Test entity resource.
 */
class EntityResource extends EntityResourceBase {

  /**
   * Returns the JSON:API formatted response for entities collection.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The inbound HTTP request.
   * @param \Drupal\Core\Entity\EntityInterface[] $entities
   *   The list of entities to build a response for. Note that
   *   the ordering matters and will be preserved.
   * @param int $total
   *   The total number of entities. This value is used to build
   *   pagination links.
   * @param int $page
   *   The number of a page the list of entities is displayed at.
   * @param int $per_page
   *   The number of entities per page. Note that the count of
   *   entities must not be greater than this value.
   *
   * @return \Drupal\jsonapi\ResourceResponse
   *   The JSON:API formatted response for entities collection.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getCollectionResponse(Request $request, array $entities, int $total, int $page, int $per_page): ResourceResponse {
    // Clone current request for further modifications.
    $request = clone $request;
    // Mark the request in order to allow JSON:API to recognize it.
    /* @see \Drupal\jsonapi\Routing\Routes::getResourceTypeNameFromParameters() */
    $request->attributes->set(Routes::JSON_API_ROUTE_FLAG_KEY, TRUE);

    if (empty($entities)) {
      $response = $this->buildWrappedResponse(new ResourceObjectData([]), $request, new NullIncludedData());
    }
    else {
      $resources = [];

      foreach ($entities as $entity) {
        $resources[] = $this->entityAccessChecker->getAccessCheckedResourceObject($entity);
      }

      // Use the last entity that is definitely defined because the
      // entities list isn't empty.
      assert(isset($entity));
      $resource = $this->resourceTypeRepository->get($entity->getEntityTypeId(), $entity->bundle());
      $offset = $page * $per_page;
      $data = new ResourceObjectData($resources);

      $data->setTotalCount($total);
      $data->setHasNextPage($offset < $total);

      $request->attributes->set(Routes::RESOURCE_TYPE_KEY, $resource);

      $response = $this->respondWithCollection($data, $this->getIncludes($request, $data), $request, $resource, new OffsetPage($offset, $per_page));
    }

    return $response;
  }

}
