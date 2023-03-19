<?php

namespace Drupal\Tests\book\Unit\Menu;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Tests\Core\Menu\LocalTaskIntegrationTestBase;

/**
 * Tests existence of book local tasks.
 *
 * @group book
 */
class BookLocalTasksTest extends LocalTaskIntegrationTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->directoryList = [
      'book' => 'core/modules/book',
      'node' => 'core/modules/node',
      'system' => 'core/modules/system',
    ];
    parent::setUp();

    // Setup theme handler for ThemeLocalTask.
    $theme_handler = $this->createMock(ThemeHandlerInterface::class);
    $theme_handler->expects($this->any())
      ->method('listInfo')
      ->willReturn([]);

    $entity_type = $this->createMock('Drupal\Core\Entity\EntityTypeInterface');
    $entity_type->expects($this->any())
      ->method('hasLinkTemplate')
      ->with('version-history')
      ->willReturn(TRUE);
    $entity_type_manager = $this->createMock(EntityTypeManagerInterface::class);
    $entity_type_manager->expects($this->any())
      ->method('getDefinitions')
      ->willReturn([
        'node' => $entity_type,
      ]);
    \Drupal::getContainer()->set('theme_handler', $theme_handler);
    \Drupal::getContainer()->set('entity_type.manager', $entity_type_manager);
  }

  /**
   * Tests local task existence.
   *
   * @dataProvider getBookAdminRoutes
   */
  public function testBookAdminLocalTasks($route) {

    $this->assertLocalTasks($route, [
      0 => ['book.admin', 'book.settings'],
    ]);
  }

  /**
   * Provides a list of routes to test.
   */
  public function getBookAdminRoutes() {
    return [
      ['book.admin'],
      ['book.settings'],
    ];
  }

  /**
   * Tests local task existence.
   *
   * @dataProvider getBookNodeRoutes
   */
  public function testBookNodeLocalTasks($route) {
    $this->assertLocalTasks($route, [
      [
        'entity.node.book_outline_form',
        'entity.node.canonical',
        'entity.node.edit_form',
        'entity.node.delete_form',
        'entity.version_history:node.version_history',
      ],
    ]);
  }

  /**
   * Provides a list of routes to test.
   */
  public function getBookNodeRoutes() {
    return [
      ['entity.node.canonical'],
      ['entity.node.book_outline_form'],
    ];
  }

}
