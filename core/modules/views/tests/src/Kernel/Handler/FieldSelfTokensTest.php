<?php

namespace Drupal\Tests\views\Kernel\Handler;

use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Core\Render\RenderContext;
use Drupal\Tests\views\Kernel\ViewsKernelTestBase;
use Drupal\views\Views;

/**
 * Tests Drupal\views\Plugin\views\field\EntityField handler token escaping.
 *
 * @group views
 */
class FieldSelfTokensTest extends ViewsKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['node', 'user'];

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = ['test_field_self_tokens'];

  /**
   * {@inheritdoc}
   */
  protected function setUp($import_test_views = TRUE): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');

    NodeType::create(['type' => 'article', 'name' => 'Article'])->save();

    Node::create([
      'title' => 'Questions & Answers',
      'type' => 'article',
    ])->save();
  }

  /**
   * {@inheritdoc}
   */
  public function testSelfTokenEscaping() {
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = \Drupal::service('renderer');

    $view = Views::getView('test_field_self_tokens');
    $view->initHandlers();
    $this->executeView($view);
    $row = $view->result[0];
    $title_field = $view->field['title'];
    $title_field->options['alter']['text'] = '<p>{{ title__value }}</p>';
    $title_field->options['alter']['alter_text'] = TRUE;
    $output = $renderer->executeInRenderContext(new RenderContext(), function () use ($title_field, $row) {
      return $title_field->theme($row);
    });
    $this->assertSame('<p>Questions &amp; Answers</p>', (string) $output);
  }

}
