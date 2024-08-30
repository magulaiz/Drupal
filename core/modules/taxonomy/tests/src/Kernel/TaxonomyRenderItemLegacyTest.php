<?php

declare(strict_types=1);

namespace Drupal\Tests\taxonomy\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the legacy render_item() method for deprecation.
 *
 * @group taxonomy
 * @group legacy
 */
class TaxonomyRenderItemLegacyTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['taxonomy', 'views'];

  /**
   * Tests the render_item() method deprecation.
   */
  public function testRenderItemDeprecation() {
    // Variable to track if deprecation was triggered.
    $deprecationTriggered = FALSE;

    // Set a custom error handler to catch the deprecation notice.
    set_error_handler(function ($errno, $errstr) use (&$deprecationTriggered) {
      if (strpos($errstr, 'MultiItemsFieldHandlerInterface::render_item() is deprecated') !== FALSE) {
        $deprecationTriggered = TRUE;
      }
      else {
        // Re-throw the error if it's not the deprecation we're expecting.
        return FALSE;
      }
    });

    try {
      // Get the necessary services.
      /** @var \Drupal\views\Plugin\ViewsHandlerManager $viewsHandlerManager */
      $viewsHandlerManager = $this->container->get('plugin.manager.views.field');

      // Create a new instance of the TaxonomyIndexTid plugin.
      $taxonomyIndexTid = $viewsHandlerManager->createInstance('taxonomy_index_tid', []);

      // Call the deprecated render_item() method.
      $taxonomyIndexTid->render_item(0, ['name' => 'Foo']);
    } finally {
      // Restore the original error handler.
      restore_error_handler();
    }

    // Assert that the deprecation was triggered.
    $this->assertTrue($deprecationTriggered, 'Deprecation notice was not triggered.');
  }

}
