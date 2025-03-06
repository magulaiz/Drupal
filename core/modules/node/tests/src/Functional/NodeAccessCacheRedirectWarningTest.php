<?php

declare(strict_types=1);

namespace Drupal\Tests\node\Functional;

/**
 * Tests the node access grants cache context service.
 *
 * @group node
 * @group Cache
 */
class NodeAccessCacheRedirectWarningTest extends NodeTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['block', 'node_access_test_empty'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    node_access_rebuild();
  }

  /**
   * Quick demonstration of the differences in cache contexts.
   *
   * The intent here was to visit the nodes to view the error but for whatever
   * reason I can't seem to trigger the redirect warning this way. Needs work.
   */
  public function testNodeAccessCacheRedirectWarning(): void {
    $this->drupalPlaceBlock('local_tasks_block');

    $this->assertTrue(\Drupal::moduleHandler()->hasImplementations('node_grants'));

    $author = $this->drupalCreateUser([
      'create page content',
      'edit any page content',
      'view own unpublished content',
    ]);
    $this->drupalLogin($author);

    $node = $this->drupalCreateNode(['uid' => $author->id(), 'status' => 0]);

    $this->drupalGet($node->toUrl());

    $node->setPublished();
    $node->save();

    $this->drupalGet($node->toUrl());

    $node->setUnpublished();
    $node->save();

    $this->drupalGet($node->toUrl());

    $node->setPublished();
    $node->save();

    $this->drupalGet($node->toUrl());
  }

}
