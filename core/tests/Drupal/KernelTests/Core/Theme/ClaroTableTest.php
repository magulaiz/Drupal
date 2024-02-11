<?php

namespace Drupal\KernelTests\Core\Theme;

use Drupal\KernelTests\KernelTestBase;
use Drupal\claro\ClaroPreRender;

/**
 * Tests Claro specific table functionality.
 *
 * @group Theme
 */
class ClaroTableTest extends KernelTestBase
{

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system'];

  /**
   * Confirm that Claro tables override use of the `sticky-enabled` class.
   */
  public function testThemeTableStickyHeaders() {
    // Enable the Claro theme.
    \Drupal::service('theme_installer')->install(['claro']);
    $this->config('system.theme')->set('default', 'claro')->save();
    $header = ['one', 'two', 'three'];
    $rows = [[1, 2, 3], [4, 5, 6], [7, 8, 9]];
    $table = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#sticky' => TRUE,
    ];
    $this->render($table);

    // Confirm that position-sticky is used instead of sticky-enabled.
    $this->assertNoRaw('sticky-enabled');
    $this->assertRaw('position-sticky');
  }

  /**
   * Confirm that Claro prerender table callback is not executed for non-array class attribute.
   */
  public function testThemeTablePositionStickyPreRender() {
    // Enable the Claro theme.
    \Drupal::service('theme_installer')->install(['claro']);
    $this->config('system.theme')->set('default', 'claro')->save();
    $header = ['one', 'two', 'three'];
    $rows = [[1, 2, 3], [4, 5, 6], [7, 8, 9]];
    $table = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#sticky' => TRUE,
      '#attributes' => [
        'class' => 'class'
      ],
      '#pre_render' => [
        [ClaroPreRender::class, 'tablePositionSticky'],
      ],
    ];

    try {
      $renderedTable = \Drupal::service('renderer')->renderRoot($table);
      // Confirm that table is rendered.
      $this->assertStringContainsString('class="class"', $renderedTable);
    } catch (\Error $e) {
      $this->fail('PHP Error was thrown.');
    }
  }
}
