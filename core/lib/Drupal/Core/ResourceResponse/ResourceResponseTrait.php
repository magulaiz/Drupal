<?php

namespace Drupal\Core\ResourceResponse;

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
