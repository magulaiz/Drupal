<?php

/**
 * @file
 * Post update functions for Media.
 */

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\filter\Entity\FilterFormat;

/**
 * Implements hook_removed_post_updates().
 */
function media_removed_post_updates() {
  return [
    'media_post_update_collection_route' => '9.0.0',
    'media_post_update_storage_handler' => '9.0.0',
    'media_post_update_enable_standalone_url' => '9.0.0',
    'media_post_update_add_status_extra_filter' => '9.0.0',
    'media_post_update_modify_base_field_author_override' => '10.0.0',
    'media_post_update_oembed_loading_attribute' => '11.0.0',
    'media_post_update_set_blank_iframe_domain_to_null' => '11.0.0',
    'media_post_update_remove_mappings_targeting_source_field' => '11.0.0',
  ];
}

/**
 * Disable contextual links for media embeds in all text formats.
 */
function media_post_update_add_show_contextual_links_as_false(&$sandbox = NULL): TranslatableMarkup {
  // Initialize batch variables if this is the first run.
  if (!isset($sandbox['total'])) {
    $query = \Drupal::entityQuery('filter_format');
    $sandbox['ids'] = $query->execute();
    $sandbox['total'] = count($sandbox['ids']);
    $sandbox['progress'] = 0;

    // In case there are no entities to process.
    if ($sandbox['total'] == 0) {
      $sandbox['total'] = 1;
      $sandbox['progress'] = 1;
    }
  }
  // Load the filter formats in chunks of 50.
  $ids = array_splice($sandbox['ids'], 0, 50);
  $filter_formats = FilterFormat::loadMultiple($ids);
  /** @var \Drupal\filter\Entity\FilterFormat $filter_format */
  foreach ($filter_formats as $filter_format) {
    $filters = $filter_format->get('filters');
    if (isset($filters['media_embed'])) {
      $media_embed_settings = $filters['media_embed'];
      if (empty($media_embed_settings['settings']['show_contextual_links'])) {
        $media_embed_settings['settings']['show_contextual_links'] = FALSE;
        $filter_format->setFilterConfig('media_embed', $media_embed_settings);
        $filter_format->save();
      }
    }
    $sandbox['progress']++;
  }

  // Determine if the batch process is complete.
  $sandbox['#finished'] = ($sandbox['progress'] / $sandbox['total']);

  return new TranslatableMarkup('Processed Filter Formats (@count/@total)', [
    '@count' => $sandbox['progress'],
    '@total' => $sandbox['total'],
  ]);
}
