<?php

namespace Drupal\Tests\node\Functional\Views;

use Drupal\Tests\views\Functional\ViewTestBase;

/**
 * Base class for all node Views tests.
 */
abstract class NodeTestBase extends ViewTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['node_test_views'];

  /**
   * {@inheritdoc}
   */
<<<<<<< HEAD
  protected function setUp($import_test_views = TRUE, $modules = ['node_test_views']) {
=======
  protected function setUp($import_test_views = TRUE, $modules = ['node_test_views']): void {
>>>>>>> upstream/11.x
    parent::setUp($import_test_views, $modules);
  }

}
