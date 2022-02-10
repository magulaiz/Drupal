<?php

namespace Drupal\Tests\hal\Functional\LayoutBuilder;

use Drupal\FunctionalTests\Hal\EntityViewDisplayHalJsonAnonTest;
use Drupal\Tests\rest\Functional\BasicAuthResourceTestTrait;

/**
 * @group layout_builder
 * @group rest
 */
class LayoutBuilderEntityViewDisplayHalJsonBasicAuthTest extends EntityViewDisplayHalJsonAnonTest {

  use BasicAuthResourceTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['basic_auth'];

  /**
   * {@inheritdoc}
   */
  protected static $auth = 'basic_auth';

}
