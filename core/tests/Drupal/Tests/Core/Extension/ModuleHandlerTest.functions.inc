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

/**
 * Implements hook_custom_order().
 */
function module_handler_test_custom_order(): mixed {
  return __FUNCTION__;
}

/**
 * Implements hook_custom_order().
 */
function module_handler_test1_custom_order(): mixed {
  return __FUNCTION__;
}

/**
 * Implements hook_custom_order().
 */
function module_handler_test2_custom_order(): mixed {
  return __FUNCTION__;
}
