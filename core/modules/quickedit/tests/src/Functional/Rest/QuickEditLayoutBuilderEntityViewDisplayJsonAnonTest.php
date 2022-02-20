<?php

namespace Drupal\Tests\quickedit\Functional\Rest;

use Drupal\Tests\layout_builder\Functional\Rest\LayoutBuilderEntityViewDisplayJsonAnonTest;

/**
 * @group quickedit
 * @group rest
 */
class QuickEditLayoutBuilderEntityViewDisplayJsonAnonTest extends LayoutBuilderEntityViewDisplayJsonAnonTest {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['quickedit'];

}
