<?php

namespace Drupal\views\Plugin\views\style;

use Symfony\Component\HttpFoundation\Response;

/**
 * Defines a style compatible with a ResponseDisplayPluginInterface.
 */
interface ResponseStylePluginInterface {

  /**
   * Alter the response for the display.
   *
   * @param \Symfony\Component\HttpFoundation\Response $response
   *   The built response.
   */
  public static function alterResponse(Response $response);

}
