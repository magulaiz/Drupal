<?php

namespace Drupal\rest;

@trigger_error('The ' . __NAMESPACE__ . '\ResourceResponseTrait is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use the \Drupal\Core\ResourceResponse\ResourceResponseTrait class instead.', E_USER_DEPRECATED);

/**
 * @deprecated in drupal:10.1.0 and is removed from drupal:11.0.0.
 *   Use the \Drupal\Core\ResourceResponse\ResourceResponseTrait class instead.
 *
 * @see https://www.drupal.org/node/3306206
 */
trait ResourceResponseTrait {

  /**
   * Response data that should be serialized.
   *
   * @var mixed
   */
  protected $responseData;

  /**
   * Returns response data that should be serialized.
   *
   * @return mixed
   *   Response data that should be serialized.
   */
  public function getResponseData() {
    return $this->responseData;
  }

}
