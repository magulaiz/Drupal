<?php

namespace Drupal\Tests\comment\Functional\Update;

use Drupal\comment\Entity\CommentType;
use Drupal\FunctionalTests\Update\UpdatePathTestBase;

/**
 * Ensures that update hook is run properly for deleting obsolete Hal settings.
 *
 * @group update
 */
class CommentTypeButtonLabelsUpdateTest extends UpdatePathTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../../../../system/tests/fixtures/update/drupal-8.8.0.filled.standard.php.gz',
    ];
  }

  /**
   * Ensures that comment_post_update_add_button_labels() runs correctly.
   */
  public function testUpdate() {
    $commentTypeIds = ['comment', 'comment_forum', 'test_comment_type'];
    foreach ($commentTypeIds as $commentTypeId) {
      $message = "Comment type ID: $commentTypeId";
      $commentTypeConfig = $this->config("comment.type.$commentTypeId");
      $this->assertFalse($commentTypeConfig->isNew());
      $this->assertNull($commentTypeConfig->get('button_labels'), $message);
      $this->assertNull($commentTypeConfig->get('button_labels.submit_comment'), $message);
      $this->assertNull($commentTypeConfig->get('button_labels.submit_reply'), $message);
    }

    $this->runUpdates();

    $commentTypes = CommentType::loadMultiple();
    $this->assertEquals($commentTypeIds, array_keys($commentTypes));
    $buttonLabels = ['submit_comment' => 'Save', 'submit_reply' => 'Save'];
    foreach ($commentTypeIds as $commentTypeId) {
      $message = "Comment type ID: $commentTypeId";
      $commentTypeConfig = $this->config("comment.type.$commentTypeId");
      $this->assertEquals($buttonLabels, $commentTypeConfig->get('button_labels'), $message);
      $this->assertEquals($buttonLabels['submit_comment'], $commentTypeConfig->get('button_labels.submit_comment'), $message);
      $this->assertEquals($buttonLabels['submit_comment'], $commentTypeConfig->get('button_labels.submit_reply'), $message);
    }
  }

}
