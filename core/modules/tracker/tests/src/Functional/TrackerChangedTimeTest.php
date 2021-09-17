<?php

namespace Drupal\Tests\tracker\Functional;

use Drupal\comment\Entity\Comment;
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

  /**
   * Tests the changed time calculated on node.
   */
  public function testCalculateChangedTime() {
    // Create comment field on a page.
    $this->drupalCreateContentType(['type' => 'page', 'name' => t('Basic page')]);
    $this->addDefaultCommentField('node', 'page');

    $node = $this->createNode(['changed' => 979534800]);
    $changed_time = $node->getChangedTime();

    $this->assertEquals($changed_time, _tracker_calculate_changed($node));
    // Add comment.
    $comment = Comment::create([
      'entity_id' => $node->id(),
      'entity_type' => 'node',
      'field_name' => 'comment',
      'subject' => $this->randomMachineName(),
      'language' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
      'comment_body' => [LanguageInterface::LANGCODE_NOT_SPECIFIED => [$this->randomMachineName()]],
    ]);
    $comment->save();

    $comment_changed_time = $comment->getCreatedTime();
    $this->assertEquals($comment_changed_time, _tracker_calculate_changed($node));
  }

}
