<?php

namespace Drupal\Tests\hal\Functional\quickedit;

use Drupal\Tests\hal\Functional\layout_builder\LayoutBuilderEntityViewDisplayHalJsonCookieTest;

/**
 * @group quickedit
 * @group rest
 * @group legacy
 */
class QuickEditLayoutBuilderEntityViewDisplayHalJsonCookieTest extends LayoutBuilderEntityViewDisplayHalJsonCookieTest {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['quickedit'];

}
