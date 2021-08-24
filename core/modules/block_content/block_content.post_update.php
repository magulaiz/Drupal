<?php

/**
 * @file
 * Post update functions for Custom Block.
 */

/**
 * Implements hook_removed_post_updates().
 */
function block_content_removed_post_updates() {
  return [
    'block_content_post_update_add_views_reusable_filter' => '9.0.0',
  ];
}

/**
 * Set a default author to block content.
 */
function block_content_post_update_set_default_author(&$sandbox = NULL) {
  $block_content_storage = \Drupal::entityTypeManager()
    ->getStorage('block_content');

  if (!isset($sandbox['total'])) {
    $sandbox['total'] = $block_content_storage
      ->getQuery()
      ->condition('uid', NULL, 'IS NULL')
      ->count()
      ->execute();
    $sandbox['current'] = 0;
    $sandbox['skipped_bids'] = [];

    // Handle the case of 0 block to process.
    if ($sandbox['total'] == 0) {
      $sandbox['total'] = 1;
      $sandbox['current'] = 1;
    }
  }

  $query = $block_content_storage
    ->getQuery()
    ->condition('uid', NULL, 'IS NULL')
    ->range(0, 50);
  if (!empty($sandbox['skipped_bids'])) {
    $query->condition('id', $sandbox['skipped_bids'], 'NOT IN');
  }
  $bids = $query->execute();

  foreach ($bids as $bid) {
    /** @var \Drupal\block_content\BlockContentInterface $block_content */
    $block_content = $block_content_storage->load($bid);
    $block_content->setOwnerId(0);

    try {
      $block_content->save();
    }
    catch (EntityStorageException $e) {
      $sandbox['skipped_bids'][] = $bid;
    }
    $sandbox['current'] += 1;
  }

  $sandbox['#finished'] = ($sandbox['current'] / $sandbox['total']);
  return t('Default author set to block content');
}
