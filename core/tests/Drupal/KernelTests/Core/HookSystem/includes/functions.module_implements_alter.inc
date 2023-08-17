<?php

/**
 * @file
 * Functions for ModuleHandlerTest.
 */

use PHPUnit\Framework\Assert;

/**
 * Implements hook_module_implements_alter().
 */
function hooks_test_module_implements_alter(array &$implementations, string $hook): void {
  switch ($hook) {
    case 'module_implements_alter':
      Assert::fail('The hook_module_implements_alter() must not be called for itself');
  }
}

/**
 * Implements hook_module_implements_alter().
 */
function hooks_test_last_module_implements_alter(array &$implementations, string $hook): void {
  if (str_contains($hook, 'unaltered')) {
    return;
  }
  // Let 'hooks_test' module run last, no matter which hook.
  $group = $implementations['hooks_test'] ?? NULL;
  if ($group !== NULL) {
    unset($implementations['hooks_test']);
    $implementations['hooks_test'] = $group;
  }
}

/**
 * Implements hook_module_implements_alter().
 */
function hooks_test1_last_module_implements_alter(array &$implementations, string $hook): void {
  if (str_contains($hook, 'unaltered')) {
    return;
  }
  // Let 'hooks_test' module run last, no matter which hook.
  $group = $implementations['hooks_test1'] ?? NULL;
  if ($group !== NULL) {
    unset($implementations['hooks_test1']);
    $implementations['hooks_test1'] = $group;
  }
}

/**
 * Implements hook_module_implements_alter().
 */
function hooks_test_between_module_implements_alter(array &$implementations, string $hook): void {
  if (str_contains($hook, 'unaltered')) {
    return;
  }
  // Let 'hooks_test' module run after 'hooks_test1'.
  $group = $implementations['hooks_test'] ?? NULL;
  $group3 = $implementations['hooks_test1'] ?? NULL;
  if ($group !== NULL && $group3 !== NULL) {
    unset($implementations['hooks_test']);
    $offset = array_search('hooks_test1', array_keys($implementations)) + 1;
    $implementations = array_slice($implementations, 0, $offset, TRUE)
      + ['hooks_test' => $group]
      + array_slice($implementations, $offset, NULL, TRUE);
  }
}

/**
 * Implements hook_module_implements_alter().
 */
function hooks_test1_first_module_implements_alter(array &$implementations, string $hook): void {
  if (str_contains($hook, 'unaltered')) {
    return;
  }
  // Let 'hooks_test1' module run first, no matter which hook.
  $group = $implementations['hooks_test1'] ?? NULL;
  if ($group !== NULL) {
    $implementations = ['hooks_test1' => $group] + $implementations;
  }
}

/**
 * Implements hook_module_implements_alter().
 */
function hooks_test_fake_module_implements_alter(array &$implementations, string $hook): void {
  if (str_contains($hook, 'unaltered')) {
    return;
  }

  $offset = array_search('hooks_test1', array_keys($implementations));
  if ($offset !== FALSE) {
    $implementations = array_slice($implementations, 0, $offset + 1, TRUE)
      + ['_hooks_test_between' => FALSE]
      + array_slice($implementations, $offset + 1, NULL, TRUE);
  }

  $implementations = ['_hooks_test_early' => FALSE]
    + $implementations
    + ['_hooks_test_late' => FALSE];
}

/**
 * Implements hook_module_implements_alter().
 */
function hooks_test_remove_module_implements_alter(array &$implementations, string $hook): void {
  if (str_contains($hook, 'unaltered')) {
    return;
  }
  unset($implementations['hooks_test1']);
}
