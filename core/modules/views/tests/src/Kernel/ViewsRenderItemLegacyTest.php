<?php

declare(strict_types=1);

namespace Drupal\Tests\views\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the legacy render_item() method for deprecation in views.
 *
 * @group views
 * @group legacy
 */
class ViewsRenderItemLegacyTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['views'];

  /**
   * Tests the render_item() method deprecation.
   */
  public function testRenderItemDeprecation() {
    // Variable to track if the specific deprecation was triggered.
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

      // Create a new instance of a field plugin.
      $fieldPlugin = $viewsHandlerManager->createInstance('field', []);

      // Call the deprecated render_item() method.
      $fieldPlugin->render_item(0, ['name' => 'Foo']);
    } finally {
      // Restore the original error handler.
      restore_error_handler();
    }

    // Assert that the deprecation was triggered.
    $this->assertTrue($deprecationTriggered, 'Deprecation notice for render_item() was not triggered.');
  }

}
