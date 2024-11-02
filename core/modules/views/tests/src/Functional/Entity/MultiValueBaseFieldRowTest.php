<?php

namespace Drupal\Tests\views\Functional\Entity;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\Tests\views\Functional\ViewTestBase;

/**
 * Tests multi valued base field in a view.
 *
 * Tests with 'Display all values in the same row' option unchecked.
 *
 * @group views
 */
class MultiValueBaseFieldRowTest extends ViewTestBase {

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = ['test_multi_value_base_field_multiple_row'];

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'views_ui', 'views_test_config', 'entity_test', 'views_entity_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp($import_test_views = TRUE, $modules = ['views_test_config']): void {
    parent::setUp($import_test_views, $modules);

    EntityTest::create([
      'name' => 'a',
      'test_multi_value_base_field' => ['val_1', 'val_2'],
    ])->save();
    EntityTest::create(['name' => 'b', 'test_multi_value_base_field' => 'val_1'])->save();
    EntityTest::create([
      'name' => 'c',
      'test_multi_value_base_field' => ['val_1', 'val_2'],
    ])->save();
    $this->drupalLogin($this->drupalCreateUser(['access content']));
  }

  /**
   * Tests the view result.
   */
  public function testViewResultTableRows() : void {
    $this->drupalGet('test-multi-value-base-field-multiple-row');
    $rows = $this->getSession()->getPage()->findAll('css', 'table tbody tr');
    // Ensure there are exactly 5 rows.
    $this->assertCount(5, $rows);
    // Test if each value is displayed in separate rows.
    $this->assertSession()->elementTextEquals('css', 'table tbody tr:nth-child(1) .views-field-name', 'a');
    $this->assertSession()->elementTextEquals('css', 'table tbody tr:nth-child(1) .views-field-test-multi-value-base-field-value', 'val_1');
    $this->assertSession()->elementTextEquals('css', 'table tbody tr:nth-child(2) .views-field-name', 'a');
    $this->assertSession()->elementTextEquals('css', 'table tbody tr:nth-child(2) .views-field-test-multi-value-base-field-value', 'val_2');
    $this->assertSession()->elementTextEquals('css', 'table tbody tr:nth-child(3) .views-field-name', 'b');
    $this->assertSession()->elementTextEquals('css', 'table tbody tr:nth-child(3) .views-field-test-multi-value-base-field-value', 'val_1');
    $this->assertSession()->elementTextEquals('css', 'table tbody tr:nth-child(4) .views-field-name', 'c');
    $this->assertSession()->elementTextEquals('css', 'table tbody tr:nth-child(4) .views-field-test-multi-value-base-field-value', 'val_1');
    $this->assertSession()->elementTextEquals('css', 'table tbody tr:nth-child(5) .views-field-name', 'c');
    $this->assertSession()->elementTextEquals('css', 'table tbody tr:nth-child(5) .views-field-test-multi-value-base-field-value', 'val_2');
  }

}
