<?php

/**
 * @file
 * Content administration and module settings user interface.
 */

use Drupal\Core\Link;
use Drupal\media\MediaInterface;

/**
 * Updates all medias in the passed-in array with the passed-in field values.
 *
 * IMPORTANT NOTE: This function is intended to work when called from a form
 * submission handler. Calling it outside of the form submission process may not
 * work correctly.
 *
 * @param array $medias
 *   Array of media ids or medias to update.
 * @param array $updates
 *   Array of key/value pairs with media field names and the value to update that
 *   field to.
 * @param string $langcode
 *   (optional) The language updates should be applied to. If none is specified
 *   all available languages are processed.
 * @param bool $load
 *   (optional) TRUE if $medias contains an array of media IDs to be loaded, FALSE
 *   if it contains fully loaded medias. Defaults to FALSE.
 * @param bool $revisions
 *   (optional) TRUE if $medias contains an array of revision IDs instead of
 *   media IDs. Defaults to FALSE; will be ignored if $load is FALSE.
 */
function media_mass_update(array $medias, array $updates, $langcode = NULL, $load = FALSE, $revisions = FALSE) {
  // We use batch processing to prevent timeout when updating a large number
  // of medias.
  if (count($medias) > 10) {
    $batch = [
      'operations' => [
        ['_media_mass_update_batch_process', [$medias, $updates, $langcode, $load, $revisions]],
      ],
      'finished' => '_media_mass_update_batch_finished',
      'title' => t('Processing'),
      // We use a single multi-pass operation, so the default
      // 'Remaining x of y operations' message will be confusing here.
      'progress_message' => '',
      'error_message' => t('The update has encountered an error.'),
      // The operations do not live in the .module file, so we need to
      // tell the batch engine which file to load before calling them.
      'file' => drupal_get_path('module', 'media') . '/media.admin.inc',
    ];
    batch_set($batch);
  }
  else {
    $storage = \Drupal::entityTypeManager()->getStorage('media');
    if ($load && !$revisions) {
      $medias = $storage->loadMultiple($medias);
    }
    foreach ($medias as $media) {
      if ($load && $revisions) {
        $media = $storage->loadRevision($media);
      }
      _media_mass_update_helper($media, $updates, $langcode);
    }
    \Drupal::messenger()->addStatus(t('The update has been performed.'));
  }
}

/**
 * Updates individual medias when fewer than 10 are queued.
 *
 * @param \Drupal\media\MediaInterface $media
 *   A media to update.
 * @param array $updates
 *   Associative array of updates.
 * @param string $langcode
 *   (optional) The language updates should be applied to. If none is specified
 *   all available languages are processed.
 *
 * @return \Drupal\media\MediaInterface
 *   An updated media object.
 *
 * @see media_mass_update()
 */
function _media_mass_update_helper(MediaInterface $media, array $updates, $langcode = NULL) {
  $langcodes = isset($langcode) ? [$langcode] : array_keys($media->getTranslationLanguages());
  // For efficiency manually save the original media before applying any changes.
  $media->original = clone $media;
  foreach ($langcodes as $langcode) {
    foreach ($updates as $name => $value) {
      $media->getTranslation($langcode)->$name = $value;
    }
  }
  $media->save();
  return $media;
}

/**
 * Implements callback_batch_operation().
 *
 * Executes a batch operation for media_mass_update().
 *
 * @param array $medias
 *   An array of media IDs.
 * @param array $updates
 *   Associative array of updates.
 * @param string $langcode
 *   The language updates should be applied to. If none is specified all
 *   available languages are processed.
 * @param bool $load
 *   TRUE if $medias contains an array of media IDs to be loaded, FALSE if it
 *   contains fully loaded medias.
 * @param bool $revisions
 *   (optional) TRUE if $medias contains an array of revision IDs instead of
 *   media IDs. Defaults to FALSE; will be ignored if $load is FALSE.
 * @param array|\ArrayAccess $context
 *   An array of contextual key/values.
 */
function _media_mass_update_batch_process(array $medias, array $updates, $langcode, $load, $revisions, &$context) {
  if (!isset($context['sandbox']['progress'])) {
    $context['sandbox']['progress'] = 0;
    $context['sandbox']['max'] = count($medias);
    $context['sandbox']['medias'] = $medias;
  }

  // Process medias by groups of 5.
  $storage = \Drupal::entityTypeManager()->getStorage('media');
  $count = min(5, count($context['sandbox']['medias']));
  for ($i = 1; $i <= $count; $i++) {
    // For each nid, load the media, reset the values, and save it.
    $media = array_shift($context['sandbox']['medias']);
    if ($load) {
      $media = $revisions ?
        $storage->loadRevision($media) : $storage->load($media);
    }
    $media = _media_mass_update_helper($media, $updates, $langcode);

    // Store result for post-processing in the finished callback.
    $context['results'][] = Link::fromTextAndUrl($media->label(), $media->toUrl());

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
 * Reports the 'finished' status of batch operation for media_mass_update().
 *
 * @param bool $success
 *   A boolean indicating whether the batch mass update operation successfully
 *   concluded.
 * @param string[] $results
 *   An array of rendered links to medias updated via the batch mode process.
 * @param array $operations
 *   An array of function calls (not used in this function).
 *
 * @see _media_mass_update_batch_process()
 */
function _media_mass_update_batch_finished($success, $results, $operations) {
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
