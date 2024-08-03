<?php

declare(strict_types=1);

namespace Drupal\Tests\rest\Functional;

use Drupal\Tests\ApiRequestTrait;

/**
 * Boilerplate for Rest Functional tests' HTTP requests.
 *
 * @internal
 */
trait RestRequestTestTrait {
  use ApiRequestTrait {
    makeApiRequest as request;
  }

}
