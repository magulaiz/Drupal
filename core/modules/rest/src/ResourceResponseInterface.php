<?php

namespace Drupal\rest;

/**
 * Defines a common interface for resource responses.
 *
 * @see \Drupal\rest\ResourceResponseTrait
 */
interface ResourceResponseInterface {

  /**
   * Returns response data that should be serialized.
   *
   * @return mixed
   *   Response data that should be serialized.
   */
  public function getResponseData();

  /**
   * Sets the response content.
   *
   * @param mixed $data
   *   Response data that should be serialized.
   *
   * @return Drupal\rest\ResourceResponseInterface
   *   Interface for resource responses.
   */
  public function setResponseData($data): ResourceResponseInterface;

}
