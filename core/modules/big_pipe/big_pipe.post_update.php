<?php

/**
 * @file
 * Post update functions for Big Pipe.
 */

declare(strict_types=1);

/**
 * Implements hook_removed_post_updates().
 */
function big_pipe_removed_post_updates(): array {
  return [
    'big_pipe_post_update_html5_placeholders' => '11.0.0',
  ];
}
