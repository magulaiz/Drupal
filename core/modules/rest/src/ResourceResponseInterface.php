<?php

namespace Drupal\rest;

@trigger_error('The ' . __NAMESPACE__ . '\ResourceResponseInterface is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use the \Drupal\Core\ResourceResponse\ResourceResponseInterface class instead.', E_USER_DEPRECATED);

/**
 * Defines a common interface for resource responses.
 *
 * @deprecated in drupal:10.1.0 and is removed from drupal:11.0.0.
 *   Use the \Drupal\Core\ResourceResponse\ResourceResponseInterface class instead.
 *
 * @see https://www.drupal.org/node/3306206
 */
interface ResourceResponseInterface {

  /**
   * Returns response data that should be serialized.
   *
   * @return mixed
   *   Response data that should be serialized.
   */
  public function getResponseData();

}
