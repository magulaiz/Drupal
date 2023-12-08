<?php

declare(strict_types=1);

namespace Drupal\Core\StreamWrapper;

use Drupal\Core\Url;

/**
 * Provides an interface for StreamWrappers supporting getUrl.
 */
interface StreamWrapperGetUrlInterface {

  /**
   * Returns a web accessible URL for the resource.
   *
   * This function returns a URL object that can be turned into a string that
   * can be embedded in a webpage and accessed from a browser. For example a
   * stream wrapper for the URI "youtube://xIpLd0WQKCY" might return a URL
   * object for "http://www.youtube.com/watch?v=xIpLd0WQKCY".
   *
   * @return \Drupal\Core\Url
   *   A web accessible URL for the resource.
   */
  public function getUrl() : Url;

}
