<?php

/**
 * @file
 * Functions for ModuleHandlerTest.
 */

/**
 * Implements hook_testhook().
 */
function hooks_test_new_testhook(string|int $arg): string {
  return __FUNCTION__ . '(' . $arg . ')';
}

/**
 * Implements hook_testhook1().
 */
function hooks_test_testhook1(string|int $arg): string {
  return __FUNCTION__ . '(' . $arg . ')';
}

/**
 * Implements hook_custom_order().
 */
function hooks_test_custom_order(): string {
  return __FUNCTION__;
}

/**
 * Implements hook_custom_order().
 */
function hooks_test1_custom_order(): string {
  return __FUNCTION__;
}

/**
 * Implements hook_custom_order().
 */
function hooks_test2_custom_order(): string {
  return __FUNCTION__;
}

/**
 * Implements hook_custom_order().
 */
function _hooks_test_early_custom_order(): string {
  return __FUNCTION__;
}

/**
 * Implements hook_custom_order().
 */
function _hooks_test_between_custom_order(): mixed {
  return __FUNCTION__;
}

/**
 * Implements hook_custom_order().
 */
function _hooks_test_late_custom_order(): mixed {
  return __FUNCTION__;
}
