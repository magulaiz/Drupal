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
    $this->drupalLogin($this->webUser);
    $comment = $this->postComment($this->node, $this->randomMachineName(), $this->randomMachineName());
    $this->assertInstanceOf(Comment::class, $comment);

    $this->createEntityReferenceField(
      'node',
      'article',
      'entity_reference_comment',
      'Entity Reference Comment',
      'comment'
    );

    $node = $this->drupalCreateNode([
      'title' => 'Baloney',
      'type' => 'article',
    ]);
    $node->set('entity_reference_comment', $comment->id())->save();
    $this->assertNotEmpty($node->get('entity_reference_comment'), 'Reference field is saved.');
    $this->assertEquals($node->get('entity_reference_comment')->getValue()[0]['target_id'], $comment->id(), 'Reference field is saved.');
  }

}
