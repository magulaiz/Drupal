<?php

declare(strict_types=1);

namespace Drupal\Tests\node\Functional\Views;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * Tests replacement of Views tokens supplied by the Node module.
 *
 * @group node
 * @see \Drupal\node\Tests\NodeTokenReplaceTest
 */
class NodeFieldTokensTest extends NodeTestBase {

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = ['test_node_tokens'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests token replacement for Views tokens supplied by the Node module.
   */
  public function testViewsTokenReplacement(): void {
    // Create the Article content type with a standard body field.
    /** @var \Drupal\node\NodeTypeInterface $node_type */
    $node_type = NodeType::create(['type' => 'article', 'name' => 'Article']);
    $node_type->save();
    // Ensure the 'body' field storage exists.
    $field_storage = FieldStorageConfig::loadByName('node', 'body');
    if (!$field_storage) {
      $field_storage = FieldStorageConfig::create([
        'field_name' => 'body',
        'entity_type' => 'node',
        'type' => 'text_long',
      ]);
      $field_storage->save();
    }

    // Ensure the 'body' field exists for the 'article' content type.
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

    // Create a user and a node.
    $account = $this->createUser();
    $body = $this->randomMachineName(32);
    $summary = $this->randomMachineName(16);

    /** @var \Drupal\node\NodeInterface $node */
    $node = Node::create([
      'type' => 'article',
      'uid' => $account->id(),
      'title' => 'Testing Views tokens',
      'body' => [['value' => $body, 'summary' => $summary, 'format' => 'plain_text']],
    ]);
    $node->save();

    $this->drupalGet('test_node_tokens');

    // Body: {{ body }}<br />
    $this->assertSession()->responseContains("Body: <p>$body</p>");

    // Raw value: {{ body__value }}<br />
    $this->assertSession()->responseContains("Raw value: $body");

    // Raw summary: {{ body__summary }}<br />
    $this->assertSession()->responseContains("Raw summary: $summary");

    // Raw format: {{ body__format }}<br />
    $this->assertSession()->responseContains("Raw format: plain_text");
  }

}
