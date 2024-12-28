<?php

declare(strict_types=1);

namespace Drupal\Tests\comment\Functional;

use Drupal\comment\CommentManagerInterface;
use Drupal\Component\Render\MarkupInterface;
use Drupal\Tests\TestFileCreationTrait;

/**
 * Tests comment preview.
 *
 * @group comment
 */
class CommentPreviewTest extends CommentTestBase {

  use TestFileCreationTrait {
    getTestFiles as drupalGetTestFiles;
  }

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['olivero_test', 'test_user_config'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'olivero';

  /**
   * Tests comment preview.
   */
  public function testCommentPreview(): void {
    $this->setCommentPreview(DRUPAL_OPTIONAL);
    $this->setCommentForm(TRUE);
    $this->setCommentSubject(TRUE);
    $this->setCommentSettings('default_mode', CommentManagerInterface::COMMENT_MODE_THREADED, 'Comment paging changed.');

    // Log in as web user.
    $this->drupalLogin($this->webUser);

    // Test escaping of the username on the preview form.
    \Drupal::service('module_installer')->install(['user_hooks_test']);
    \Drupal::keyValue('user_hooks_test')->set('user_format_name_alter', TRUE);
    $edit = [];
    $edit['subject[0][value]'] = $this->randomMachineName(8);
    $edit['comment_body[0][value]'] = $this->randomMachineName(16);
    $this->drupalGet('node/' . $this->node->id());
    $this->submitForm($edit, 'Preview');
    $this->assertSession()->assertEscaped('<em>' . $this->webUser->id() . '</em>');

    \Drupal::keyValue('user_hooks_test')->set('user_format_name_alter_safe', TRUE);
    $this->drupalGet('node/' . $this->node->id());
    $this->submitForm($edit, 'Preview');
    $this->assertInstanceOf(MarkupInterface::class, $this->webUser->getDisplayName());
    $this->assertSession()->assertNoEscaped('<em>' . $this->webUser->id() . '</em>');
    $this->assertSession()->responseContains('<em>' . $this->webUser->id() . '</em>');

    // Add a user picture.
    $image = current($this->drupalGetTestFiles('image'));
    $user_edit['files[user_picture_0]'] = \Drupal::service('file_system')->realpath($image->uri);
    $this->drupalGet('user/' . $this->webUser->id() . '/edit');
    $this->submitForm($user_edit, 'Save');

    // As the web user, fill in the comment form and preview the comment.
    $this->drupalGet('node/' . $this->node->id());
    $this->submitForm($edit, 'Preview');

    // Check that the preview is displaying the title and body.
    $this->assertSession()->titleEquals('Preview comment | Drupal');
    $this->assertSession()->pageTextContains($edit['subject[0][value]']);
    $this->assertSession()->pageTextContains($edit['comment_body[0][value]']);

    // Check that the title and body fields are displayed with the correct values.
    $this->assertSession()->fieldValueEquals('subject[0][value]', $edit['subject[0][value]']);
    $this->assertSession()->fieldValueEquals('comment_body[0][value]', $edit['comment_body[0][value]']);

    // Check that the user picture is displayed.
    $this->assertSession()->elementExists('xpath', "//article[contains(@class, 'preview')]//div[contains(@class, 'user-picture')]//img");

    // Ensure that preview node is displayed after the submit buttons of the form.
    $xpath = $this->assertSession()->buildXPathQuery('//div[@id=:id]/following-sibling::article', [':id' => 'edit-actions']);
    $this->assertSession()->elementExists('xpath', $xpath);
  }

}
