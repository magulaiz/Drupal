<?php

namespace Drupal\rest;

@trigger_error('The ' . __NAMESPACE__ . '\ResourceResponse is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use the \Drupal\Core\ResourceResponse\ResourceResponse class instead.', E_USER_DEPRECATED);

use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\Cache\CacheableResponseTrait;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contains data for serialization before sending the response.
 *
 * We do not want to abuse the $content property on the Response class to store
 * our response data. $content implies that the provided data must either be a
 * string or an object with a __toString() method, which is not a requirement
 * for data used here.
 *
 * Routes that return this response must specify the '_format' requirement.
 *
 * @deprecated in drupal:10.1.0 and is removed from drupal:11.0.0.
 *   Use the \Drupal\Core\ResourceResponse\ResourceResponse class instead.
 *
 * @see https://www.drupal.org/node/3306206
 * @see \Drupal\rest\ModifiedResourceResponse
 */
class ResourceResponse extends Response implements CacheableResponseInterface, ResourceResponseInterface {

  use CacheableResponseTrait;
  use ResourceResponseTrait;

  /**
   * Constructor for ResourceResponse objects.
   *
   * @param mixed $data
   *   Response data that should be serialized.
   * @param int $status
   *   The response status code.
   * @param array $headers
   *   An array of response headers.
   */
  public function __construct($data = NULL, $status = 200, $headers = []) {
    $this->responseData = $data;
    parent::__construct('', $status, $headers);
  }

}
