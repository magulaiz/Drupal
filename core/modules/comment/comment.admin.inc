<?php

/**
 * @file
 * Content administration and module settings user interface.
 */

use Drupal\Core\Batch\BatchBuilder;
use Drupal\comment\CommentInterface;

/**
 * Updates all comments in the passed-in array with the passed-in field values.
 *
 * IMPORTANT NOTE: This function is intended to work when called from a form
 * submission handler. Calling it outside of the form submission process may not
 * work correctly.
 *
 * @param array $comments
 *   Array of comment cids or comments to update.
 * @param array $updates
 *   Array of key/value pairs with comment field names and the
 *   value to update that field to.
 * @param string $langcode
 *   (optional) The language updates should be applied to. If none is specified
 *   all available languages are processed.
 * @param bool $load
 *   (optional) TRUE if $comments contains an array of comment IDs to be loaded,
 *   FALSE if it contains fully loaded comments. Defaults to FALSE.
 */
function comment_mass_update(array $comments, array $updates, $langcode = NULL, $load = FALSE) {
  // We use batch processing to prevent timeout when updating a large number
  // of comments.
  if (count($comments) > 10) {
    $batch_builder = (new BatchBuilder())
      // The operations do not live in the .module file, so we need to
      // tell the batch engine which file to load before calling them.
      ->setFile(\Drupal::service('extension.list.module')->getPath('comment') . '/comment.admin.inc')
      ->addOperation('_comment_mass_update_batch_process', [$comments, $updates, $langcode, $load])
      ->setFinishCallback('_comment_mass_update_batch_finished')
      ->setTitle(t('Processing'))
      ->setErrorMessage(t('The update has encountered an error.'))
      // We use a single multi-pass operation, so the default
      // 'Remaining x of y operations' message will be confusing here.
      ->setProgressMessage('');
    batch_set($batch_builder->toArray());

    batch_set($batch_builder->toArray());
  }
  else {
    $storage = \Drupal::entityTypeManager()->getStorage('comment');
    if ($load) {
      $comments = $storage->loadMultiple($comments);
    }
    foreach ($comments as $comment) {
      _comment_mass_update_helper($comment, $updates, $langcode);
    }
    \Drupal::messenger()->addStatus(t('The update has been performed.'));
  }
}

/**
 * Updates individual comments when fewer than 10 are queued.
 *
 * @param \Drupal\comment\CommentInterface $comment
 *   A comment to update.
 * @param array $updates
 *   Associative array of updates.
 * @param string $langcode
 *   (optional) The language updates should be applied to. If none is specified
 *   all available languages are processed.
 *
 * @return \Drupal\comment\CommentInterface
 *   An updated comment object.
 *
 * @see comment_mass_update()
 */
function _comment_mass_update_helper(CommentInterface $comment, array $updates, $langcode = NULL) {
  $langcodes = isset($langcode) ? [$langcode] : array_keys($comment->getTranslationLanguages());
  // For efficiency manually save the original
  // comment before applying any changes.
  $comment->original = clone $comment;
  foreach ($langcodes as $langcode) {
    foreach ($updates as $name => $value) {
      $comment->getTranslation($langcode)->$name = $value;
    }
  }
  $comment->save();
  return $comment;
}

/**
 * Implements callback_batch_operation().
 *
 * Executes a batch operation for comment_mass_update().
 *
 * @param array $comments
 *   An array of comment IDs.
 * @param array $updates
 *   Associative array of updates.
 * @param string $langcode
 *   The language updates should be applied to. If none is specified all
 *   available languages are processed.
 * @param bool $load
 *   TRUE if $comments contains an array of comment IDs to be loaded,
 *   FALSE if it contains fully loaded comments.
 * @param array|\ArrayAccess $context
 *   An array of contextual key/values.
 */
function _comment_mass_update_batch_process(array $comments, array $updates, $langcode, $load, &$context) {
  if (!isset($context['sandbox']['progress'])) {
    $context['sandbox']['progress'] = 0;
    $context['sandbox']['max'] = count($comments);
    $context['sandbox']['comments'] = $comments;
  }

  // Process comments by groups of 5.
  $storage = \Drupal::entityTypeManager()->getStorage('comment');
  $count = min(5, count($context['sandbox']['comments']));
  for ($i = 1; $i <= $count; $i++) {
    // For each nid, load the comment, reset the values, and save it.
    $comment = array_shift($context['sandbox']['comments']);
    if ($load) {
      $comment = $storage->load($comment);
    }
    $comment = _comment_mass_update_helper($comment, $updates, $langcode);

    // Store result for post-processing in the finished callback.
    $context['results'][] = \Drupal::service('link_generator')->generate($comment->label(), $comment->urlInfo());

    // Update our progress information.
    $context['sandbox']['progress']++;
  }

  // Inform the batch engine that we are not finished,
  // and provide an estimation of the completion level we reached.
  if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
    $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
  }
}

/**
 * Implements callback_batch_finished().
 *
 * Reports the 'finished' status of batch operation for comment_mass_update().
 *
 * @param bool $success
 *   A boolean indicating whether the batch mass update operation successfully
 *   concluded.
 * @param string[] $results
 *   An array of rendered links to comments updated via the batch mode process.
 * @param array $operations
 *   An array of function calls (not used in this function).
 *
 * @see _comment_mass_update_batch_process()
 */
function _comment_mass_update_batch_finished($success, array $results, array $operations) {
  if ($success) {
    \Drupal::messenger()->addStatus(t('The update has been performed.'));
  }
  else {
    \Drupal::messenger()->addError(t('An error occurred and processing did not complete.'));
    $message = \Drupal::translation()->formatPlural(count($results), '1 item successfully processed:', '@count items successfully processed:');
    $item_list = [
      '#theme' => 'item_list',
      '#items' => $results,
    ];
    $message .= \Drupal::service('renderer')->render($item_list);
    \Drupal::messenger()->addStatus($message);
  }
}
