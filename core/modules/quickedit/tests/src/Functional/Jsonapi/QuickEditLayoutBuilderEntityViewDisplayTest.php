<?php

namespace Drupal\Tests\quickedit\Functional\Jsonapi;

use Drupal\Tests\layout_builder\Functional\Jsonapi\LayoutBuilderEntityViewDisplayTest;

/**
 * JSON:API integration test for the "EntityViewDisplay" config entity type.
 *
 * @group jsonapi
 * @group quickedit
 */
class QuickEditLayoutBuilderEntityViewDisplayTest extends LayoutBuilderEntityViewDisplayTest {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['quickedit'];

}
