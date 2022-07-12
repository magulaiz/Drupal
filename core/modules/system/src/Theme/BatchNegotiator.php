<?php

namespace Drupal\system\Theme;

use Drupal\Core\Batch\BatchProcessorInterface;
use Drupal\Core\Batch\BatchStorageInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Sets the active theme for the batch page.
 */
class BatchNegotiator implements ThemeNegotiatorInterface {

  /**
   * The batch storage.
   *
   * @var \Drupal\Core\Batch\BatchStorageInterface
   */
  protected $batchStorage;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Batch processor.
   *
   * @var \Drupal\Core\Batch\BatchProcessorInterface
   */
  protected BatchProcessorInterface $batchProcessor;

  /**
   * Constructs a BatchNegotiator.
   *
   * @param \Drupal\Core\Batch\BatchStorageInterface $batch_storage
   *   The batch storage.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack used to retrieve the current request.
   * @param \Drupal\Core\Batch\BatchProcessorInterface|null $batch_processor
   *   Batch processor.
   *
   * @see https://www.drupal.org/node/3229844
   */
  public function __construct(BatchStorageInterface $batch_storage, RequestStack $request_stack, BatchProcessorInterface $batch_processor = NULL) {
    $this->batchStorage = $batch_storage;
    $this->requestStack = $request_stack;
    if ($batch_processor === NULL) {
      @trigger_error('Calling ' . __METHOD__ . ' without the $batch_processor argument is deprecated in drupal:10.1.0 and it will be required in drupal:11.0.0. See https://www.drupal.org/node/3229844', E_USER_DEPRECATED);
      $batch_processor = \Drupal::service('batch.processor');
    }
    $this->batchProcessor = $batch_processor;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    return $route_match->getRouteName() == 'system.batch_page';
  }

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    // Retrieve the current state of the batch.
    $request = $this->requestStack->getCurrentRequest();
    $batch = &$this->batchProcessor->getCurrentBatch();
    if (!$batch && $request->request->has('id')) {
      $batch = $this->batchStorage->load($request->request->get('id'));
    }
    // Use the same theme as the page that started the batch.
    if (!empty($batch['theme'])) {
      return $batch['theme'];
    }
  }

}
