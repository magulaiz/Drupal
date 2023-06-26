<?php

/**
 * @file
 * Functions for ModuleHandlerTest.
 */

/**
 * Implements hook_type_alter().
 */
function module_handler_test_type_alter(array &$value): void {
  $value[] = __FUNCTION__;
}

/**
 * Implements hook_type_alter().
 */
function module_handler_test1_type_alter(array &$value): void {
  $value[] = __FUNCTION__;
}

/**
 * Implements hook_type_alter().
 */
function module_handler_test2_type_alter(array &$value): void {
  $value[] = __FUNCTION__;
}

/**
 * Implements hook_subtype_alter().
 */
function module_handler_test_subtype_alter(array &$value): void {
  $value[] = __FUNCTION__;
}

/**
 * Implements hook_subtype_alter().
 */
function module_handler_test1_subtype_alter(array &$value): void {
  $value[] = __FUNCTION__;
}

/**
 * Implements hook_subtype_alter().
 */
function module_handler_test2_subtype_alter(array &$value): void {
  $value[] = __FUNCTION__;
}

/**
 * Implements hook_subtype_alter().
 */
function module_handler_test_unaltered_alter(array &$value): void {
  $value[] = __FUNCTION__;
}

/**
 * Implements hook_subtype_alter().
 */
function module_handler_test1_unaltered_alter(array &$value): void {
  $value[] = __FUNCTION__;
}

/**
 * Implements hook_subtype_alter().
 */
function module_handler_test2_unaltered_alter(array &$value): void {
  $value[] = __FUNCTION__;
}
