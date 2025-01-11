<?php

namespace Drupal\file\Controller;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Defines a controller to respond to file widget AJAX requests.
 */
class FileWidgetAjaxController {
  use StringTranslationTrait;

  /**
   * Returns the progress status for a file upload process.
   *
   * @param string $key
   *   The unique key for this upload process.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JsonResponse object.
   */
  public function progress($key) {
    return new JsonResponse(TRUE);
  }

}
