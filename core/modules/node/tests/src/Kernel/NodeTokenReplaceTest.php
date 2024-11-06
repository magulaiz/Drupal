<?php

declare(strict_types=1);

namespace Drupal\Tests\node\Kernel;

use Drupal\Component\Utility\Html;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\node\NodeInterface;
use Drupal\Tests\system\Kernel\Token\TokenReplaceKernelTestBase;

/**
 * Tests node token replacement.
 *
 * @group node
 */
class NodeTokenReplaceTest extends TokenReplaceKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['node', 'filter'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig(['filter', 'node']);

    $node_type = NodeType::create(['type' => 'article', 'name' => 'Article']);
    $node_type->save();
    node_add_body_field($node_type);
  }

  /**
   * Creates a node, then tests the tokens generated from it.
   */
  public function testNodeTokenReplacement(): void {
    $url_options = [
      'absolute' => TRUE,
      'language' => $this->interfaceLanguage,
    ];

    // Create a user and a node.
    $account = $this->createUser();
    /** @var \Drupal\node\NodeInterface $node */
    $node = Node::create([
      'type' => 'article',
      'uid' => $account->id(),
      'title' => '<blink>Blinking Text</blink>',
      'body' => [['value' => 'Regular NODE body for the test.', 'summary' => 'Fancy NODE summary.', 'format' => 'plain_text']],
    ]);
    $node->save();

    // Generate and test tokens.
    $tests = [];
    $tests['[node:nid]'] = $node->id();
    $tests['[node:uuid]'] = $node->uuid();
    $tests['[node:vid]'] = $node->getRevisionId();
    $tests['[node:type]'] = 'article';
    $tests['[node:type-name]'] = 'Article';
    $tests['[node:title]'] = Html::escape($node->getTitle());
    $tests['[node:body]'] = $node->body->processed;
    $tests['[node:summary]'] = $node->body->summary_processed;
    $tests['[node:langcode]'] = $node->language()->getId();
    $tests['[node:published_status]'] = 'Published';
    $tests['[node:url]'] = $node->toUrl('canonical', $url_options)->toString();
    $tests['[node:edit-url]'] = $node->toUrl('edit-form', $url_options)->toString();
    $tests['[node:author]'] = $account->getAccountName();
    $tests['[node:author:uid]'] = $node->getOwnerId();
    $tests['[node:author:name]'] = $account->getAccountName();
    /** @var \Drupal\Core\Datetime\DateFormatterInterface $date_formatter */
    $date_formatter = $this->container->get('date.formatter');
    $tests['[node:created:since]'] = $date_formatter->formatTimeDiffSince($node->getCreatedTime(), ['langcode' => $this->interfaceLanguage->getId()]);
    $tests['[node:changed:since]'] = $date_formatter->formatTimeDiffSince($node->getChangedTime(), ['langcode' => $this->interfaceLanguage->getId()]);

    $base_bubbleable_metadata = BubbleableMetadata::createFromObject($node);

    $metadata_tests = [];
    $metadata_tests['[node:nid]'] = $base_bubbleable_metadata;
    $metadata_tests['[node:uuid]'] = $base_bubbleable_metadata;
    $metadata_tests['[node:vid]'] = $base_bubbleable_metadata;
    $metadata_tests['[node:type]'] = $base_bubbleable_metadata;
    $metadata_tests['[node:type-name]'] = $base_bubbleable_metadata;
    $metadata_tests['[node:title]'] = $base_bubbleable_metadata;
    $metadata_tests['[node:body]'] = $base_bubbleable_metadata;
    $metadata_tests['[node:summary]'] = $base_bubbleable_metadata;
    $metadata_tests['[node:langcode]'] = $base_bubbleable_metadata;
    $metadata_tests['[node:published_status]'] = $base_bubbleable_metadata;
    $metadata_tests['[node:url]'] = $base_bubbleable_metadata;
    $metadata_tests['[node:edit-url]'] = $base_bubbleable_metadata;
    $bubbleable_metadata = clone $base_bubbleable_metadata;
    $metadata_tests['[node:author]'] = $bubbleable_metadata->addCacheTags(['user:1']);
    $metadata_tests['[node:author:uid]'] = $bubbleable_metadata;
    $metadata_tests['[node:author:name]'] = $bubbleable_metadata;
    $bubbleable_metadata = clone $base_bubbleable_metadata;
    $metadata_tests['[node:created:since]'] = $bubbleable_metadata->setCacheMaxAge(0);
    $metadata_tests['[node:changed:since]'] = $bubbleable_metadata;

    // Test to make sure that we generated something for each token.
    $this->assertNotContains(0, array_map('strlen', $tests), 'No empty tokens generated.');

    foreach ($tests as $input => $expected) {
      $bubbleable_metadata = new BubbleableMetadata();
      $output = $this->tokenService->replace($input, ['node' => $node], ['langcode' => $this->interfaceLanguage->getId()], $bubbleable_metadata);
      $this->assertSame((string) $expected, (string) $output, "Failed test case: {$input}");
      $this->assertEquals($metadata_tests[$input], $bubbleable_metadata);
    }

    // Repeat for an unpublished node.
    $node = Node::create([
      'type' => 'article',
      'uid' => $account->id(),
      'title' => '<blink>Blinking Text</blink>',
    ]);
    $node->setUnpublished();
    $node->save();

    // Generate and test tokens.
    $tests = [];
    $tests['[node:published_status]'] = 'Unpublished';

    // Test to make sure that we generated something for each token.
    $this->assertFalse(in_array(0, array_map('strlen', $tests)), 'No empty tokens generated for unpublished node.');

    foreach ($tests as $input => $expected) {
      $output = $this->tokenService->replace($input, ['node' => $node], ['language' => $this->interfaceLanguage]);
      $this->assertEquals($output, $expected, "Node token $input replaced for unpublished node.");
    }

    // Repeat for a node without a summary.
    // Length of this text is ~660 characters and it's longer than a default
    // trim length setting (600).
    $text = 'The Drupal coding standards apply to code within '
      . 'Drupal and its contributed modules. These standards are version-independent '
      . 'and "always-current". '
      . 'All new code should follow the current standards, regardless of (core) version. '
      . 'Existing code in older versions may be updated. For large code-bases (like Drupal core), '
      . 'updating the code of a previous version for the current standards may be too huge of a task. '
      . 'Comments and names should use US English spelling. '
      . 'Coding standard fixes are done by rule not individual files. '
      . 'The video tutorial, Understanding the Drupal Coding Standards, '
      . 'explains the standards, why they are important, and how to use them.';

    // Create a node.
    $node = Node::create([
      'type' => 'article',
      'uid' => $account->id(),
      'title' => '<blink>Blinking Text</blink>',
      'body' => [['value' => $text, 'format' => 'plain_text']],
    ]);
    $node->save();

    // Get teaser node view display.
    /** @var \Drupal\Core\ $view_display_storage */
    $view_display_storage = \Drupal::entityTypeManager()
      ->getStorage('entity_view_display');
    $view_display = $view_display_storage->load('node.article.teaser') ?? $view_display_storage->create([
      'targetEntityType' => 'node',
      'bundle' => 'article',
      'mode' => 'teaser',
      'status' => TRUE,
    ]);

    // Token [node:summary] should use trim settings of "Summary or trimmed"
    // formatter of teaser view mode.
    $view_display->setComponent('body', [
      'type' => 'text_summary_or_trimmed',
      'settings' => ['trim_length' => 160],
    ]);
    $view_display->save();
    $expected_trimmed_to_160 = Html::escape('The Drupal coding standards apply to code within '
      . 'Drupal and its contributed modules. These standards are version-independent '
      . 'and "always-current".');
    $this->assertNodeSummaryTokenReplacement($node, $expected_trimmed_to_160);

    // Token [node:summary] should use trim settings of "Trimmed"
    // formatter of teaser view mode.
    $view_display->setComponent('body', [
      'type' => 'text_trimmed',
      'settings' => ['trim_length' => 90],
    ]);
    $view_display->save();
    $expected_trimmed_to_80 = Html::escape('The Drupal coding standards apply to code within '
    . 'Drupal and its contributed modules.');
    $this->assertNodeSummaryTokenReplacement($node, $expected_trimmed_to_80);

    // Token [node:summary] should not pickup trim length setting of any other
    // formatter rather than "Summary or trimmed" or "Trimmed", even if setting
    // name matches.
    // Fallbacks to default trim length setting (600).
    $view_display->setComponent('body', [
      'type' => 'text_default',
      'settings' => ['trim_length' => 42],
    ]);
    $view_display->save();
    $expected_trimmed_to_600 = Html::escape('The Drupal coding standards apply to code within '
    . 'Drupal and its contributed modules. These standards are version-independent '
    . 'and "always-current". '
    . 'All new code should follow the current standards, regardless of (core) version. '
    . 'Existing code in older versions may be updated. For large code-bases (like Drupal core), '
    . 'updating the code of a previous version for the current standards may be too huge of a task. '
    . 'Comments and names should use US English spelling. '
    . 'Coding standard fixes are done by rule not individual files.');
    $this->assertNodeSummaryTokenReplacement($node, $expected_trimmed_to_600);

    // Token [node:summary] should not pickup trim length setting if teaser
    // view mode does not exist.
    // Fallbacks to "Summary or trimmed" default trim length setting (600).
    $view_display->delete();
    $this->assertNodeSummaryTokenReplacement($node, $expected_trimmed_to_600);
  }

  /**
   * Asserts that [node:summary] token works as expected.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node to assert against.
   * @param string $expected
   *   Expected token replacement output.
   */
  protected function assertNodeSummaryTokenReplacement(NodeInterface $node, $expected) {
    $output = $this->tokenService->replace('[node:summary]', ['node' => $node], ['langcode' => $this->interfaceLanguage->getId()]);
    $this->assertEquals($expected, $output);
  }

}
