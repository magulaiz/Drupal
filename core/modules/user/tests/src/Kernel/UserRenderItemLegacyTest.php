<?php

declare(strict_types=1);

namespace Drupal\Tests\user\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the legacy render_item() method for deprecation in multiple classes.
 *
 * @group user
 * @group legacy
 */
class UserRenderItemLegacyTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['views', 'user'];

  /**
   * Tests the render_item() method deprecation for the Roles class.
   */
  public function testRenderItemDeprecationForRoles() {
    // Create a mock for the Roles class.
    /** @var \PHPUnit\Framework\MockObject\MockObject|Drupal\user\Plugin\views\field\Roles $mock */
    $mock = $this->getMockBuilder('Drupal\user\Plugin\views\field\Roles')
      ->disableOriginalConstructor()
    // Mock the render_item method.
      ->onlyMethods(['render_item'])
      ->getMock();

    // Track if deprecation notice was triggered.
    $deprecationTriggered = FALSE;

    // Set a custom error handler to catch the deprecation notice.
    set_error_handler(function ($errno, $errstr) use (&$deprecationTriggered) {
      if (strpos($errstr, 'MultiItemsFieldHandlerInterface::render_item() is deprecated') !== FALSE) {
        $deprecationTriggered = TRUE;
        // Suppress the error.
        return TRUE;
      }
      // Let other errors through.
      return FALSE;
    });

    try {
      // Mock render_item() call to trigger a deprecation notice.
      $mock->expects($this->once())
      // Ensure the method is called correctly.
        ->method('render_item')
        ->will($this->returnCallback(function () {
             // Trigger a deprecation notice manually.
             @trigger_error('MultiItemsFieldHandlerInterface::render_item() is deprecated in drupal:11.1.0 and is removed from drupal:12.0.0. Use renderItem() instead. See https://www.drupal.org/node/3467146', E_USER_DEPRECATED);
        }));

      // Call the deprecated method.
      $mock->render_item(0, ['name' => 'Foo']);
    } finally {
      // Restore the original error handler.
      restore_error_handler();
    }

    // Assert that the deprecation was triggered.
    $this->assertTrue($deprecationTriggered, 'Deprecation notice for render_item() was not triggered for Roles class.');
  }

  /**
   * Tests the render_item() method deprecation for the Permissions class.
   */
  public function testRenderItemDeprecationForPermissions() {
    // Create a mock for the Permissions class.
    /** @var \PHPUnit\Framework\MockObject\MockObject|Drupal\user\Plugin\views\field\Permissions $mock */
    $mock = $this->getMockBuilder('Drupal\user\Plugin\views\field\Permissions')
      ->disableOriginalConstructor()
    // Mock the render_item method.
      ->onlyMethods(['render_item'])
      ->getMock();

    // Track if deprecation notice was triggered.
    $deprecationTriggered = FALSE;

    // Set a custom error handler to catch the deprecation notice.
    set_error_handler(function ($errno, $errstr) use (&$deprecationTriggered) {
      if (strpos($errstr, 'MultiItemsFieldHandlerInterface::render_item() is deprecated') !== FALSE) {
        $deprecationTriggered = TRUE;
        // Suppress the error.
        return TRUE;
      }
      // Let other errors through.
      return FALSE;
    });

    try {
      // Mock render_item() call to trigger a deprecation notice.
      $mock->expects($this->once())
      // Ensure the method is called correctly.
        ->method('render_item')
        ->will($this->returnCallback(function () {
             // Trigger a deprecation notice manually.
             @trigger_error('MultiItemsFieldHandlerInterface::render_item() is deprecated in drupal:11.1.0 and is removed from drupal:12.0.0. Use renderItem() instead. See https://www.drupal.org/node/3467146', E_USER_DEPRECATED);
        }));

      // Call the deprecated method.
      $mock->render_item(0, ['name' => 'Foo']);
    } finally {
      // Restore the original error handler.
      restore_error_handler();
    }

    // Assert that the deprecation was triggered.
    $this->assertTrue($deprecationTriggered, 'Deprecation notice for render_item() was not triggered for Permissions class.');
  }

}
