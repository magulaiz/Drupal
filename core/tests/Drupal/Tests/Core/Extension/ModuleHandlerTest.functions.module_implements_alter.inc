<?php

/**
 * @file
 * Functions for ModuleHandlerTest.
 */

use PHPUnit\Framework\Assert;

/**
 * Implements hook_module_implements_alter().
 */
function module_handler_test_module_implements_alter(array &$implementations, string $hook): void {
  switch ($hook) {
    case 'module_implements_alter':
      Assert::fail('The hook_module_implements_alter() must not be called for itself');
  }
}

/**
 * Implements hook_module_implements_alter().
 */
function module_handler_test_last_module_implements_alter(array &$implementations, string $hook): void {
  if (str_contains($hook, 'unaltered')) {
    return;
  }
  // Let 'module_handler_test' module run last, no matter which hook.
  $group = $implementations['module_handler_test'] ?? NULL;
  if ($group !== NULL) {
    unset($implementations['module_handler_test']);
    $implementations['module_handler_test'] = $group;
  }
}

/**
 * Implements hook_module_implements_alter().
 */
function module_handler_test1_last_module_implements_alter(array &$implementations, string $hook): void {
  if (str_contains($hook, 'unaltered')) {
    return;
  }
  // Let 'module_handler_test' module run last, no matter which hook.
  $group = $implementations['module_handler_test1'] ?? NULL;
  if ($group !== NULL) {
    unset($implementations['module_handler_test1']);
    $implementations['module_handler_test1'] = $group;
  }
}

/**
 * Implements hook_module_implements_alter().
 */
function module_handler_test_between_module_implements_alter(array &$implementations, string $hook): void {
  if (str_contains($hook, 'unaltered')) {
    return;
  }
  // Let 'module_handler_test' module run after 'module_handler_test1'.
  $group = $implementations['module_handler_test'] ?? NULL;
  $group3 = $implementations['module_handler_test1'] ?? NULL;
  if ($group !== NULL && $group3 !== NULL) {
    unset($implementations['module_handler_test']);
    $offset = array_search('module_handler_test1', array_keys($implementations)) + 1;
    $implementations = array_slice($implementations, 0, $offset, TRUE)
      + ['module_handler_test' => $group]
      + array_slice($implementations, $offset, NULL, TRUE);
  }
}

/**
 * Implements hook_module_implements_alter().
 */
function module_handler_test1_first_module_implements_alter(array &$implementations, string $hook): void {
  if (str_contains($hook, 'unaltered')) {
    return;
  }
  // Let 'module_handler_test1' module run first, no matter which hook.
  $group = $implementations['module_handler_test1'] ?? NULL;
  if ($group !== NULL) {
    $implementations = ['module_handler_test1' => $group] + $implementations;
  }
}
