<?php

/**
 * @file
 * Builds placeholder replacement tokens for user-related data.
 */

use Drupal\Core\Hook\LegacyHook;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\user\Hook\UserTokensHooks;

/**
 * Implements hook_token_info().
 */
#[LegacyHook]
function user_token_info() {
  \Drupal::service(UserTokensHooks::CLASS)->userTokenInfo();
}

/**
 * Implements hook_tokens().
 */
#[LegacyHook]
function user_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  \Drupal::service(UserTokensHooks::CLASS)->userTokens($type, $tokens, $data, $options, $bubbleable_metadata);
}
