<?php

namespace Drupal\Tests\action\Functional\Rest;

use Drupal\Tests\rest\Functional\CookieResourceTestTrait;

/**
<<<<<<< HEAD:core/modules/aggregator/tests/src/Functional/Rest/ItemJsonCookieTest.php
 * @group rest
 * @group legacy
=======
 * @group action
>>>>>>> upstream/11.x:core/modules/action/tests/src/Functional/Rest/ActionJsonCookieTest.php
 */
class ActionJsonCookieTest extends ActionResourceTestBase {

  use CookieResourceTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $format = 'json';

  /**
   * {@inheritdoc}
   */
  protected static $mimeType = 'application/json';

  /**
   * {@inheritdoc}
   */
  protected static $auth = 'cookie';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

}
