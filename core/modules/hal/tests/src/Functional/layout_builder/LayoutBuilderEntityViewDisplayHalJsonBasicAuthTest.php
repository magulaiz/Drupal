<?php

namespace Drupal\Tests\hal\Functional\layout_builder;

use Drupal\Tests\layout_builder\Functional\Rest\LayoutBuilderEntityViewDisplayResourceTestBase;
use Drupal\Tests\rest\Functional\BasicAuthResourceTestTrait;

/**
 * Layout Builder Entity View Display Class.
 *
 * @group hal
 * @group legacy
 */
class LayoutBuilderEntityViewDisplayHalJsonBasicAuthTest extends LayoutBuilderEntityViewDisplayResourceTestBase {

  use BasicAuthResourceTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['basic_auth'];

  /**
   * {@inheritdoc}
   */
  protected static $auth = 'basic_auth';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

}
