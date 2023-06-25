<?php

/**
 * @file
 * Functions for ModuleHandlerTest.
 */

/**
 * Implements hook_hook().
 */
function module_handler_test_new_hook(mixed $arg): mixed {
  return $arg;
}

/**
 * Implements hook_hook1().
 */
function module_handler_test_hook1(mixed $arg): mixed {
  return $arg;
}
