<?php

/**
 * @file
 * Builds placeholder replacement tokens for node-related data.
 */

use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\user\Entity\User;

/**
 * Implements hook_token_info().
 */
function node_token_info() {
  $type = [
    'name' => t('Nodes'),
    'description' => t('Tokens related to individual content items, or "nodes".'),
    'needs-data' => 'node',
  ];

  // Core tokens for nodes.
  $node['nid'] = [
    'name' => t("Content ID"),
    'description' => t('The unique ID of the content item, or "node".'),
  ];
  $node['vid'] = [
    'name' => t("Revision ID"),
    'description' => t("The unique ID of the node's latest revision."),
  ];
  $node['type'] = [
    'name' => t("Content type"),
  ];
  $node['type-name'] = [
    'name' => t("Content type name"),
    'description' => t("The human-readable name of the node type."),
  ];
  $node['title'] = [
    'name' => t("Title"),
  ];
  $node['body'] = [
    'name' => t("Body"),
    'description' => t("The main body text of the node."),
  ];
  $node['summary'] = [
    'name' => t("Summary"),
    'description' => t("The summary of the node's main body text."),
  ];
  $node['langcode'] = [
    'name' => t('Language code'),
    'description' => t('The language code of the language the node is written in.'),
  ];
  $node['url'] = [
    'name' => t("URL"),
    'description' => t("The URL of the node."),
  ];
  $node['edit-url'] = [
    'name' => t("Edit URL"),
    'description' => t("The URL of the node's edit page."),
  ];

  // Chained tokens for nodes.
  $node['created'] = [
    'name' => t("Date created"),
    'type' => 'date',
  ];
  $node['changed'] = [
    'name' => t("Date changed"),
    'description' => t("The date the node was most recently updated."),
    'type' => 'date',
  ];
  $node['author'] = [
    'name' => t("Author"),
    'type' => 'user',
  ];

  return [
    'types' => ['node' => $type],
    'tokens' => ['node' => $node],
  ];
}

/**
 * Implements hook_tokens().
 */
function node_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $token_service = \Drupal::token();

  $url_options = ['absolute' => TRUE];
  if (isset($options['langcode'])) {
    $url_options['language'] = \Drupal::languageManager()->getLanguage($options['langcode']);
    $langcode = $options['langcode'];
  }
  else {
    $langcode = NULL;
  }
  $replacements = [];

  if ($type == 'node' && !empty($data['node'])) {
    /** @var \Drupal\node\NodeInterface $node */
    $node = \Drupal::service('entity.repository')->getTranslationFromContext($data['node'], $langcode, ['operation' => 'node_tokens']);

    foreach ($tokens as $name => $original) {
      switch ($name) {
        // Simple key values on the node.
        case 'nid':
          $replacements[$original] = $node->id();
          break;

        case 'vid':
          $replacements[$original] = $node->getRevisionId();
          break;

        case 'type':
          $replacements[$original] = $node->getType();
          break;

        case 'type-name':
          $type_name = node_get_type_label($node);
          $replacements[$original] = $type_name;
          break;

        case 'title':
          $replacements[$original] = $node->getTitle();
          break;

        case 'body':
        case 'summary':
          if ($node->hasField('body') && ($items = $node->get('body')) && !$items->isEmpty()) {
            $item = $items[0];
            // If the summary was requested and is not empty, use it.
            if ($name == 'summary' && !empty($item->summary)) {
              $output = $item->summary_processed;
            }
            // Attempt to provide a suitable version of the 'body' field.
            else {
              $output = $item->processed;
              // A summary was requested.
              if ($name == 'summary') {
                // Generate an optionally trimmed summary of the body field.

                // Get the 'trim_length' size used for the 'teaser' mode, if
                // present, or use the default trim_length size.
                $display_options = \Drupal::service('entity_display.repository')
                  ->getViewDisplay('node', $node->getType(), 'teaser')
                  ->getComponent('body');
                if (isset($display_options['settings']['trim_length'])) {
                  $length = $display_options['settings']['trim_length'];
                }
                else {
                  $settings = \Drupal::service('plugin.manager.field.formatter')->getDefaultSettings('text_summary_or_trimmed');
                  $length = $settings['trim_length'];
                }

                $output = text_summary($output, $item->format, $length);
              }
            }
            // "processed" returns a \Drupal\Component\Render\MarkupInterface
            // via check_markup().
            $replacements[$original] = $output;
          }
          break;

        case 'langcode':
          $replacements[$original] = $node->language()->getId();
          break;

        case 'url':
          $replacements[$original] = $node->toUrl('canonical', $url_options)->toString();
          break;

        case 'edit-url':
          $replacements[$original] = $node->toUrl('edit-form', $url_options)->toString();
          break;

        // Default values for the chained tokens handled below.
        case 'author':
          $account = $node->getOwner() ? $node->getOwner() : User::load(0);
          $bubbleable_metadata->addCacheableDependency($account);
          $replacements[$original] = $account->label();
          break;

        case 'created':

        case 'changed':
          /** @var \Drupal\Core\Datetime\Entity\DateFormat $date_format */
          $date_format = DateFormat::load('medium');
          $date_raw = ($name === 'created' ? $node->getCreatedTime() : $node->getChangedTime());
          $date_formatted = \Drupal::service('date.formatter')->format($date_raw, $date_format->id(), '', NULL, $langcode);
          $bubbleable_metadata->addCacheableDependency($date_format);
          $replacements[$original] = $date_formatted;
          break;
      }
    }

    if ($author_tokens = $token_service->findWithPrefix($tokens, 'author')) {
      $replacements += $token_service->generate('user', $author_tokens, ['user' => $node->getOwner()], $options, $bubbleable_metadata);
    }

    if ($created_tokens = $token_service->findWithPrefix($tokens, 'created')) {
      $replacements += $token_service->generate('date', $created_tokens, ['date' => $node->getCreatedTime()], $options, $bubbleable_metadata);
    }

    if ($changed_tokens = $token_service->findWithPrefix($tokens, 'changed')) {
      $replacements += $token_service->generate('date', $changed_tokens, ['date' => $node->getChangedTime()], $options, $bubbleable_metadata);
    }
  }

  return $replacements;
}
