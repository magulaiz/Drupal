<?php

namespace Drupal\Tests\action\Functional\Rest;

use Drupal\Tests\rest\Functional\AnonResourceTestTrait;

/**
<<<<<<< HEAD:core/modules/aggregator/tests/src/Functional/Rest/ItemJsonAnonTest.php
 * @group rest
 * @group legacy
=======
 * @group action
>>>>>>> upstream/11.x:core/modules/action/tests/src/Functional/Rest/ActionJsonAnonTest.php
 */
class ActionJsonAnonTest extends ActionResourceTestBase {

  use AnonResourceTestTrait;

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
  protected $defaultTheme = 'stark';

}
