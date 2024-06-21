<?php

declare(strict_types=1);

namespace Drupal\Tests\views\Kernel\Handler;

use Drupal\Tests\views\Kernel\ViewsKernelTestBase;
use Drupal\views\Views;

/**
 * Tests the core Drupal\views\Plugin\views\field\Counter handler.
 *
 * @group views
 */
class FieldCounterTokenTest extends ViewsKernelTestBase {

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = ['test_view'];

  /**
   * {@inheritdoc}
   */
  public function viewsData() {
    $data = parent::viewsData();
    $data['views_test_data']['name']['field']['id'] = 'custom';
    return $data;
  }

  /**
   * Ensure that counter field as token work into link path.
   */
  public function testFieldCounterToken(): void {
    $view = Views::getView('test_view');
    $view->setDisplay();

    // Enable checkbox 'Output this field as a custom link',
    // Add counter field token into link path.
    $view->displayHandlers->get('default')->overrideOption('fields', [
      'counter' => [
        'id' => 'counter',
        'table' => 'views',
        'field' => 'counter',
        'relationship' => 'none',
        'counter_start' => 0,
        'exclude' => TRUE,
      ],
      'name' => [
        'id' => 'name',
        'table' => 'views_test_data',
        'field' => 'name',
        'relationship' => 'none',
        'alter' => [
          'alter_text' => TRUE,
          'text' => 'Counter: {{ counter }}',
          'make_link' => TRUE,
          'path' => '/counter/{{ counter }}',
        ],
      ],
    ]);

    // Execute the view.
    $this->executeView($view);

    $desired_output = '<a href="/counter/1">Counter: 1</a>';
    $this->assertSame($desired_output, (string) $view->style_plugin->getField(1, 'name'));

    $desired_output = '<a href="/counter/2">Counter: 2</a>';
    $this->assertSame($desired_output, (string) $view->style_plugin->getField(2, 'name'));

  }

}
