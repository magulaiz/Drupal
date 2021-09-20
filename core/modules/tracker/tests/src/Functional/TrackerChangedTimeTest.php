<?php

namespace Drupal\Tests\tracker\Functional;

use Drupal\comment\CommentInterface;
use Drupal\comment\Entity\Comment;
use Drupal\comment\Entity\CommentType;
use Drupal\comment\Plugin\Field\FieldType\CommentItemInterface;
use Drupal\comment\Tests\CommentTestTrait;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests most recent time calculation on node.
 *
 * @group tracker
 */
class TrackerChangedTimeTest extends BrowserTestBase {

  use CommentTestTrait;

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = [
    'comment',
    'node',
    'tracker',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  protected $contentType;
  protected $commentType;

  protected function setUp(): void {
    parent::setUp();
    // Create a page content type.
    $this->contentType = $this->drupalCreateContentType(['type' => 'page', 'name' => t('Basic page')]);

    // Allow comments on page.
    $this->commentType = CommentType::create([
      'id' => 'node_comment',
      'label' => 'Node comment',
      'description' => '',
      'target_entity_type_id' => 'node',
    ]);
    $this->commentType->save();

    $this->addDefaultCommentField(
      'node',
       $this->contentType->id(),
      'field_comment',
      CommentItemInterface::OPEN,
      $this->commentType->id()
    );
  }

  /**
   * Tests the changed time calculated on node.
   */
  public function testCalculateChangedTime() {
    $node = $this->createNode(['changed' => 979534800]);
    $changed_time = $node->getChangedTime();

    $this->assertEquals($changed_time, _tracker_calculate_changed($node));

    // Add comment.
    $comment = Comment::create([
      'entity_id' => $node->id(),
      'entity_type' => 'node',
      'field_name' => 'field_comment',
      'status' => CommentInterface::PUBLISHED,
      'subject' => $this->randomMachineName(),
      'language' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
      'comment_body' => [LanguageInterface::LANGCODE_NOT_SPECIFIED => [$this->randomMachineName()]],
    ]);
    $comment->save();

    $comment_changed_time = $comment->getCreatedTime();
    $this->assertEquals($comment_changed_time, _tracker_calculate_changed($node));
  }

}
