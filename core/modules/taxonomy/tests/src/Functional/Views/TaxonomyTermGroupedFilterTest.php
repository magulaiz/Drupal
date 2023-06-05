<?php

namespace Drupal\Tests\taxonomy\Functional\Views;

/**
 * Test the taxonomy term with grouped exposed filter.
 *
 * @group taxonomy
 */
class TaxonomyTermGroupedFilterTest extends TaxonomyTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'taxonomy',
    'taxonomy_test_views',
    'text',
    'views',
    'views_ui',
    'node',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = ['test_taxonomy_exposed_grouped_filter'];

  /**
   * @var \Drupal\taxonomy\TermInterface[]
   */
  public $terms = [];

  /**
   * @var \Drupal\node\NodeInterface[]
   */
  public $nodes = [];

  /**
   * {@inheritdoc}
   */
  protected function setUp($import_test_views = TRUE): void {
    parent::setUp($import_test_views);
    // Create taxonomy and node content.
    for ($i = 0; $i < 5; $i++) {
      $this->terms[$i] = $this->createTerm();
      $node = [];
      $node['type'] = 'article';
      $node['field_views_testing_tags'][0]['target_id'] = $this->terms[$i]->id();
      $this->nodes[$i] = $this->drupalCreateNode($node);
    }
  }

  /**
   * Tests to ensure that grouped exposed filters works as expected.
   */
  public function testTaxonomyTermGroupedFilterTest() {
    // Login as root user and update the terms in exposed filter's group items.
    $this->drupalLogin($this->rootUser);
    $this->drupalGet('/admin/structure/views/nojs/handler/test_taxonomy_exposed_grouped_filter/page_1/filter/field_views_testing_tags_target_id');
    $edit = [
      'options[group_info][group_items][1][value][]' => [$this->terms[0]->id(), $this->terms[1]->id()],
      'options[group_info][group_items][2][value][]' => [$this->terms[2]->id(), $this->terms[3]->id()],
      'options[group_info][group_items][3][value][]' => [$this->terms[0]->id(), $this->terms[4]->id()],
    ];
    $this->submitForm($edit, 'Apply');
    $this->submitForm([], 'Save');

    // Visit the view's page url and validate the results.
    $this->drupalGet('/test-taxonomy-exposed-grouped-filter');
    $edit = [
      'field_views_testing_tags_target_id' => 1,
    ];
    $this->submitForm($edit, 'Apply');
    $this->assertSession()->pageTextContains($this->nodes[0]->getTitle());
    $this->assertSession()->pageTextContains($this->nodes[1]->getTitle());
    $this->assertSession()->pageTextNotContains($this->nodes[2]->getTitle());
    $this->assertSession()->pageTextNotContains($this->nodes[3]->getTitle());
    $this->assertSession()->pageTextNotContains($this->nodes[4]->getTitle());

    $edit = [
      'field_views_testing_tags_target_id' => 2,
    ];
    $this->submitForm($edit, 'Apply');
    $this->assertSession()->pageTextContains($this->nodes[2]->getTitle());
    $this->assertSession()->pageTextContains($this->nodes[3]->getTitle());
    $this->assertSession()->pageTextNotContains($this->nodes[0]->getTitle());
    $this->assertSession()->pageTextNotContains($this->nodes[1]->getTitle());
    $this->assertSession()->pageTextNotContains($this->nodes[4]->getTitle());

    $edit = [
      'field_views_testing_tags_target_id' => 3,
    ];
    $this->submitForm($edit, 'Apply');
    $this->assertSession()->pageTextContains($this->nodes[4]->getTitle());
    $this->assertSession()->pageTextContains($this->nodes[0]->getTitle());
    $this->assertSession()->pageTextNotContains($this->nodes[1]->getTitle());
    $this->assertSession()->pageTextNotContains($this->nodes[2]->getTitle());
    $this->assertSession()->pageTextNotContains($this->nodes[3]->getTitle());
  }

}
