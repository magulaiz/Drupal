<?php

namespace Drupal\Tests\hal\Functional\quickedit;

use Drupal\Tests\hal\Functional\layout_builder\LayoutBuilderEntityViewDisplayHalJsonAnonTest;

/**
 * @group quickedit
 * @group rest
 * @group legacy
 */
class QuickEditLayoutBuilderEntityViewDisplayHalJsonAnonTest extends LayoutBuilderEntityViewDisplayHalJsonAnonTest {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['quickedit'];

}
