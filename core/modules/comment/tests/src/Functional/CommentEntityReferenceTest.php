<?php

namespace Drupal\Tests\comment\Functional;

use Drupal\comment\Entity\Comment;
use Drupal\Tests\field\Traits\EntityReferenceTestTrait;

/**
 * Tests that comments behave correctly when added as entity references.
 *
 * @group comment
 */
class CommentEntityReferenceTest extends CommentTestBase {

  use EntityReferenceTestTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests that comments are correctly saved as entity references.
   */
  public function testCommentAsEntityReference() {
    $this->createEntityReferenceField(
      'node',
      'article',
      'entity_reference_comment',
      'Entity Reference Comment',
      'comment',
      'default',
      ['target_bundles' => ['comment']]
    );
    \Drupal::service('entity_display.repository')
      ->getFormDisplay('node', 'article')
      ->setComponent('entity_reference_comment', ['type' => 'entity_reference_autocomplete'])
      ->save();

    $administratorUser = $this->drupalCreateUser([
      'skip comment approval',
      'post comments',
      'access comments',
      'access content',
      'administer nodes',
      'administer comments',
      'bypass node access',
    ]);
    $this->drupalLogin($administratorUser);

    $comment = $this->postComment($this->node, $this->randomMachineName(), $this->randomMachineName());
    $this->assertInstanceOf(Comment::class, $comment);

    $node = $this->drupalCreateNode([
      'title' => $this->randomMachineName(),
      'type' => 'article',
    ]);

    // Load the node and save it.
    $edit = [
      'entity_reference_comment[0][target_id]' => $comment->label() . ' (' . $comment->id() . ')',
    ];
    $this->drupalGet('node/' . $node->id() . '/edit');
    $this->submitForm($edit, 'Save');
    $this->assertSession()->statusCodeEquals(200);
  }

}
