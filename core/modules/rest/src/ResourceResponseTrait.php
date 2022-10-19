<?php

namespace Drupal\rest;

/**
 * Provides a trait for serialization of data.
 *
 * @see \Drupal\rest\ResourceResponseInterface
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

  /**
   * Sets the response data content that should be serialized.
   *
   * @param mixed $data
   *   Response data that should be serialized.
   *
   * @return Drupal\rest\ResourceResponseInterface
   *   Interface for resource responses.
   */
  public function setResponseData($data): ResourceResponseInterface {
    $this->responseData = $data;
    return $this;
  }

}
