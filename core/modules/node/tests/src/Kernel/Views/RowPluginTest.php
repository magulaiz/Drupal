<?php

declare(strict_types=1);

namespace Drupal\Tests\node\Kernel\Views;

use Drupal\node\Entity\NodeType;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\Tests\views\Kernel\ViewsKernelTestBase;
use Drupal\views\Tests\ViewTestData;
use Drupal\views\Views;

/**
 * Tests the node row plugin.
 *
 * @group node
 * @see \Drupal\node\Plugin\views\row\NodeRow
 */
class RowPluginTest extends ViewsKernelTestBase {

  use NodeCreationTrait;
  use UserCreationTrait;

  /**
   * Views used by this test.
   *
   * @var string[]
   */
  public static $testViews = ['test_node_row_plugin'];

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'field',
    'filter',
    'node',
    'node_test_views',
    'text',
    'user',
  ];

  /**
   * Contains all nodes used by this test.
   *
   * @var \Drupal\node\Entity\Node[]
   */
  protected array $nodes;

  /**
   * {@inheritdoc}
   */
  protected function setUp($import_test_views = TRUE): void {
    parent::setUp($import_test_views);

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installSchema('node', ['node_access']);
    $this->installConfig(['field', 'filter', 'node']);
    ViewTestData::createTestViews(static::class, ['node_test_views']);

    \Drupal::currentUser()->setAccount($this->createUser(['access content']));

    $node_type = NodeType::create([
      'type' => 'article',
      'name' => 'Article',
    ]);
    $node_type->save();
    // Ensure the body field storage exists.
    $field_storage = FieldStorageConfig::loadByName('node', 'body');
    if (!$field_storage) {
      $field_storage = FieldStorageConfig::create([
        'field_name' => 'body',
        'entity_type' => 'node',
        'type' => 'text_long',
      ]);
      $field_storage->save();
    }

    // Ensure the body field exists for this content type.
    $field = FieldConfig::loadByName('node', $node_type->id(), 'body');
    if (!$field) {
      $field = FieldConfig::create([
        'field_storage' => $field_storage,
        'bundle' => $node_type->id(),
        'label' => 'Body',
        'settings' => [
          'display_summary' => TRUE,
          'allowed_formats' => [],
        ],
      ]);
      $field->save();
    }

    /** @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface $display_repository */
    $display_repository = \Drupal::service('entity_display.repository');

    // Assign widget settings for the default form mode.
    $display_repository->getFormDisplay('node', $node_type->id())
      ->setComponent('body', [
        'type' => 'text_textarea',
      ])
      ->save();

    // Assign display settings for the 'default' and 'teaser' view modes.
    $display_repository->getViewDisplay('node', $node_type->id())
      ->setComponent('body', [
        'label' => 'hidden',
        'type' => 'text_default',
      ])
      ->save();

    // Ensure teaser view mode exists before setting display settings.
    $view_modes = $display_repository->getViewModes('node');
    if (isset($view_modes['teaser'])) {
      $display_repository->getViewDisplay('node', $node_type->id(), 'teaser')
        ->setComponent('body', [
          'label' => 'hidden',
          'type' => 'text_summary_or_trimmed',
        ])
        ->save();
    }

    // Create two nodes.
    for ($i = 0; $i < 2; $i++) {
      $this->nodes[] = $this->createNode(
        [
          'type' => 'article',
          'body' => [
            [
              'value' => $this->randomMachineName(42),
              'format' => filter_default_format(),
              'summary' => $this->randomMachineName(),
            ],
          ],
        ]
      );
    }
  }

  /**
   * Tests the node row plugin.
   */
  public function testRowPlugin(): void {
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = $this->container->get('renderer');
    $view = Views::getView('test_node_row_plugin');
    $view->initDisplay();
    $view->setDisplay('page_1');
    $view->initStyle();
    $view->rowPlugin->options['view_mode'] = 'full';

    // Test with view_mode full.
    $output = $view->preview();
    $output = (string) $renderer->renderRoot($output);
    foreach ($this->nodes as $node) {
      $this->assertStringNotContainsString($node->body->summary, $output, 'Make sure the teaser appears in the output of the view.');
      $this->assertStringContainsString($node->body->value, $output, 'Make sure the full text appears in the output of the view.');
    }

    // Test with teasers.
    $view->rowPlugin->options['view_mode'] = 'teaser';
    $output = $view->preview();
    $output = (string) $renderer->renderRoot($output);
    foreach ($this->nodes as $node) {
      $this->assertStringContainsString($node->body->summary, $output, 'Make sure the teaser appears in the output of the view.');
      $this->assertStringNotContainsString($node->body->value, $output);
    }
  }

}
