<?php

/**
 * @file
 * Batch callbacks for the Batch API tests.
 */

use Drupal\Component\Utility\Html;
use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Form\FormState;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Implements callback_batch_operation().
 *
 * Performs a simple batch operation.
 */
function _batch_test_callback_1($id, $sleep, &$context) {
  // No-op, but ensure the batch takes a couple iterations.
  // Batch needs time to run for the test, so sleep a bit.
  usleep($sleep);
  // Track execution, and store some result for post-processing in the
  // 'finished' callback.
  \Drupal::service('batch_test.batch_test_stack')->batchTestStack("op 1 id $id");
  $context['results'][1][] = $id;
}

/**
 * Implements callback_batch_operation().
 *
 * Performs a multistep batch operation.
 */
function _batch_test_callback_2($start, $total, $sleep, &$context) {
  // Initialize context with progress information.
  if (!isset($context['sandbox']['current'])) {
    $context['sandbox']['current'] = $start;
    $context['sandbox']['count'] = 0;
  }

  // Process by groups of 5 (arbitrary value).
  $limit = 5;
  for ($i = 0; $i < $limit && $context['sandbox']['count'] < $total; $i++) {
    // No-op, but ensure the batch takes a couple iterations.
    // Batch needs time to run for the test, so sleep a bit.
    usleep($sleep);
    // Track execution, and store some result for post-processing in the
    // 'finished' callback.
    $id = $context['sandbox']['current'] + $i;
    \Drupal::service('batch_test.batch_test_stack')->batchTestStack("op 2 id $id");
    $context['results'][2][] = $id;

    // Update progress information.
    $context['sandbox']['count']++;
  }
  $context['sandbox']['current'] += $i;

  // Inform batch engine about progress.
  if ($context['sandbox']['count'] != $total) {
    $context['finished'] = $context['sandbox']['count'] / $total;
  }
}

/**
 * Implements callback_batch_operation().
 */
function _batch_test_callback_5($id, $sleep, &$context) {
  // No-op, but ensure the batch takes a couple iterations.
  // Batch needs time to run for the test, so sleep a bit.
  usleep($sleep);
  // Track execution, and store some result for post-processing in the
  // 'finished' callback.
  \Drupal::service('batch_test.batch_test_stack')->batchTestStack("op 5 id $id");
  $context['results'][5][] = $id;
  // This test is to test finished > 1
  $context['finished'] = 3.14;
}

/**
 * Implements callback_batch_operation().
 *
 * Performs a simple batch operation.
 */
function _batch_test_callback_6($id, $sleep, &$context) {
  // No-op, but ensure the batch takes a couple iterations.
  // Batch needs time to run for the test, so sleep a bit.
  usleep($sleep);
  // Track execution, and store some result for post-processing in the
  // 'finished' callback.
  \Drupal::service('batch_test.batch_test_stack')->batchTestStack("op 6 id $id");
  $context['results'][6][] = $id;
}

/**
 * Implements callback_batch_operation().
 *
 * Performs a simple batch operation.
 */
function _batch_test_callback_7($id, $sleep, &$context) {
  // No-op, but ensure the batch takes a couple iterations.
  // Batch needs time to run for the test, so sleep a bit.
  usleep($sleep);
  // Track execution, and store some result for post-processing in the
  // 'finished' callback.
  \Drupal::service('batch_test.batch_test_stack')->batchTestStack("op 7 id $id");
  $context['results'][7][] = $id;
}

/**
 * Implements callback_batch_operation().
 *
 * Performs a batch operation setting up its own batch(es).
 */
function _batch_test_nested_batch_callback(array $batches = []) {
  foreach ($batches as $batch) {
    \Drupal::service('batch_test.batch_test_stack')->batchTestStack("setting up batch $batch");
    $function = '_batch_test_batch_' . $batch;
    batch_set($function());
  }
  \Drupal::state()
    ->set('batch_test_nested_order_multiple_batches', batch_get());
}

/**
 * Provides a common 'finished' callback for batches 1 to 7.
 */
function _batch_test_finished_helper($batch_id, $success, $results, $operations, $elapsed) {
  if ($results) {
    foreach ($results as $op => $op_results) {
      $messages[] = 'op ' . Html::escape($op) . ': processed ' . count($op_results) . ' elements';
    }
  }
  else {
    $messages[] = 'none';
  }

  if (!$success) {
    // A fatal error occurred during the processing.
    $error_operation = reset($operations);
    $messages[] = t('An error occurred while processing @op with arguments:<br />@args', ['@op' => $error_operation[0], '@args' => print_r($error_operation[1], TRUE)]);
  }

  // Use item list template to render the messages.
  $error_message = [
    '#type' => 'inline_template',
    '#template' => 'results for batch {{ batch_id }} ({{ elapsed }}){{ errors }}',
    '#context' => [
      'batch_id' => $batch_id,
      'elapsed' => $elapsed,
      'errors' => [
        '#theme' => 'item_list',
        '#items' => $messages,
      ],
    ],
  ];

  \Drupal::messenger()->addStatus(\Drupal::service('renderer')->renderPlain($error_message));

  \Drupal::messenger()->addMessage('elapsed time: ' . $elapsed);
}

/**
 * Implements callback_batch_finished().
 *
 * Triggers 'finished' callback for batch 0.
 */
function _batch_test_finished_0($success, $results, $operations, $elapsed) {
  _batch_test_finished_helper(0, $success, $results, $operations, $elapsed);
}

/**
 * Implements callback_batch_finished().
 *
 * Triggers 'finished' callback for batch 1.
 */
function _batch_test_finished_1($success, $results, $operations, $elapsed) {
  _batch_test_finished_helper(1, $success, $results, $operations, $elapsed);
}

/**
 * Implements callback_batch_finished().
 *
 * Triggers 'finished' callback for batch 1.
 */
function _batch_test_finished_1_finished($success, $results, $operations, $elapsed) {
  _batch_test_finished_helper(1, $success, $results, $operations, $elapsed);
  return new RedirectResponse(Url::fromRoute('test_page_test.test_page', [], ['absolute' => TRUE])->toString());
}

/**
 * Implements callback_batch_finished().
 *
 * Triggers 'finished' callback for batch 2.
 */
function _batch_test_finished_2($success, $results, $operations, $elapsed) {
  _batch_test_finished_helper(2, $success, $results, $operations, $elapsed);
}

/**
 * Implements callback_batch_finished().
 *
 * Triggers 'finished' callback for batch 3.
 */
function _batch_test_finished_3($success, $results, $operations, $elapsed) {
  _batch_test_finished_helper(3, $success, $results, $operations, $elapsed);
}

/**
 * Implements callback_batch_finished().
 *
 * Triggers 'finished' callback for batch 4.
 */
function _batch_test_finished_4($success, $results, $operations, $elapsed) {
  _batch_test_finished_helper(4, $success, $results, $operations, $elapsed);
}

/**
 * Implements callback_batch_finished().
 *
 * Triggers 'finished' callback for batch 5.
 */
function _batch_test_finished_5($success, $results, $operations, $elapsed) {
  _batch_test_finished_helper(5, $success, $results, $operations, $elapsed);
}

/**
 * Implements callback_batch_finished().
 *
 * Triggers 'finished' callback for batch 6.
 */
function _batch_test_finished_6($success, $results, $operations, $elapsed) {
  _batch_test_finished_helper(6, $success, $results, $operations, $elapsed);
}

/**
 * Implements callback_batch_finished().
 *
 * Triggers 'finished' callback for batch 7.
 */
function _batch_test_finished_7($success, $results, $operations, $elapsed) {
  _batch_test_finished_helper(7, $success, $results, $operations, $elapsed);
}

/**
 * Batch operation: Submits form_test_mock_form().
 */
function _batch_test_nested_drupal_form_submit_callback($value) {
  $form_state = (new FormState())
    ->setValue('test_value', $value);
  \Drupal::formBuilder()->submitForm('Drupal\batch_test\Form\BatchTestMockForm', $form_state);
}

/**
 * Batch 1: Repeats a simple operation.
 *
 * Operations: op 1 from 1 to 10.
 */
function _batch_test_batch_1() {
  // Ensure the batch takes at least two iterations.
  $total = 10;
  $sleep = (1000000 / $total) * 2;

  $batch_builder = (new BatchBuilder())
    ->setFile(\Drupal::service('extension.list.module')->getPath('batch_test') . '/batch_test.callbacks.inc')
    ->setFinishCallback('_batch_test_finished_1');

  for ($i = 1; $i <= $total; $i++) {
    $batch_builder->addOperation('_batch_test_callback_1', [$i, $sleep]);
  }

  return $batch_builder->toArray() + ['batch_test_id' => 'batch_1'];
}

/**
 * Batch 5: Repeats a simple operation.
 *
 * Operations: op 1 from 1 to 10.
 */
function _batch_test_batch_5() {
  // Ensure the batch takes at least two iterations.
  $total = 10;
  $sleep = (1000000 / $total) * 2;

  $batch_builder = (new BatchBuilder())
    ->setFile(\Drupal::service('extension.list.module')->getPath('batch_test') . '/batch_test.callbacks.inc')
    ->setFinishCallback('_batch_test_finished_5');
  for ($i = 1; $i <= $total; $i++) {
    $batch_builder->addOperation('_batch_test_callback_5', [$i, $sleep]);
  }

  return $batch_builder->toArray() + ['batch_test_id' => 'batch_5'];
}
