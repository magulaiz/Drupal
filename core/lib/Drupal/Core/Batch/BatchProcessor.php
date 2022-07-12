<?php

namespace Drupal\Core\Batch;

use Drupal\Component\Utility\Timer;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormSubmitterInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Queue\Batch;
use Drupal\Core\Queue\BatchMemory;
use Drupal\Core\Queue\QueueInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Implements the default batch processor.
 */
class BatchProcessor implements BatchProcessorInterface {

  use StringTranslationTrait;

  /**
   * The app root.
   *
   * @var string
   */
  protected string $root;

  /**
   * The batch storage service.
   *
   * @var \Drupal\Core\Batch\BatchStorageInterface|null
   */
  protected ?BatchStorageInterface $batchStorage = NULL;

  /**
   * The date formatter used to calculate the needed time for the batch.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected DateFormatterInterface $dateFormatter;

  /**
   * The form submitter used to redirect at the end of the batch.
   *
   * @var \Drupal\Core\Form\FormSubmitterInterface
   */
  protected FormSubmitterInterface $formSubmitter;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected RequestStack $requestStack;

  /**
   * Path validator service.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface|null
   */
  protected ?PathValidatorInterface $pathValidator = NULL;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection|null
   */
  protected ?Connection $connection = NULL;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected ModuleHandlerInterface $moduleHandler;

  /**
   * Theme manager.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected ThemeManagerInterface $themeManager;

  /**
   * Route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected RouteMatchInterface $routeMatch;

  /**
   * In memory batch cache.
   *
   * @var array|null
   */
  protected ?array $batch = NULL;

  /**
   * Queue list storage.
   *
   * @var \Drupal\Core\Queue\QueueInterface[]
   */
  protected array $queues = [];

  /**
   * Creates a new BatchProcessor.
   *
   * @param string $root
   *   The app root.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter used to calculate the needed time for the batch.
   * @param \Drupal\Core\Form\FormSubmitterInterface $form_submitter
   *   The form submitter used to redirect at the end of the batch.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   The theme manager service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match service.
   */
  public function __construct(string $root, DateFormatterInterface $date_formatter, FormSubmitterInterface $form_submitter, RequestStack $request_stack, ModuleHandlerInterface $module_handler, ThemeManagerInterface $theme_manager, RouteMatchInterface $route_match) {
    $this->root = $root;
    $this->dateFormatter = $date_formatter;
    $this->formSubmitter = $form_submitter;
    $this->requestStack = $request_stack;
    $this->moduleHandler = $module_handler;
    $this->themeManager = $theme_manager;
    $this->routeMatch = $route_match;
  }

  /**
   * Getter for the path validator service.
   *
   * @return \Drupal\Core\Path\PathValidatorInterface|null
   *   The path validator service.
   */
  protected function getPathValidator() {
    if (!$this->pathValidator) {
      $this->pathValidator = \Drupal::service('path.validator');
    }
    return $this->pathValidator;
  }

  /**
   * Getter for the batch storage.
   *
   * @return \Drupal\Core\Batch\BatchStorageInterface|null
   *   The batch storage.
   */
  protected function getBatchStorage() {
    if (!$this->batchStorage) {
      $this->batchStorage = \Drupal::service('batch.storage');
    }
    return $this->batchStorage;
  }

  /**
   * Getter for the connection to the database.
   *
   * @return \Drupal\Core\Database\Connection
   *   The connection to the database.
   */
  protected function getConnection() {
    if (!$this->connection) {
      $this->connection = Database::getConnection();
    }
    return $this->connection;
  }

  /**
   * {@inheritdoc}
   */
  public function queue(array $batch_definition): void {
    if ($batch_definition) {
      // Initialize the batch if needed.
      if (empty($this->batch)) {
        $this->batch = [
          'sets' => [],
          'has_form_submits' => FALSE,
        ];
      }

      // Base and default properties for the batch set.
      $init = [
        'sandbox' => [],
        'results' => [],
        'success' => FALSE,
        'start' => 0,
        'elapsed' => 0,
      ];
      $defaults = [
        'title' => t('Processing'),
        'init_message' => t('Initializing.'),
        'progress_message' => t('Completed @current of @total.'),
        'error_message' => t('An error has occurred.'),
      ];
      $batch_set = $init + $batch_definition + $defaults;

      // Tweak init_message to avoid the bottom of the page flickering down
      // after init phase.
      $batch_set['init_message'] .= '<br/>&nbsp;';

      // The non-concurrent workflow of batch execution allows us to save
      // numberOfItems() queries by handling our own counter.
      $batch_set['total'] = count($batch_set['operations']);
      $batch_set['count'] = $batch_set['total'];

      // Add the set to the batch.
      if (empty($this->batch['id'])) {
        // The batch is not running yet. Simply add the new set.
        $this->batch['sets'][] = $batch_set;
      }
      else {
        // The set is being added while the batch is running.
        $this->appendSet($this->batch, $batch_set);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function appendSet(array &$batch, array $batch_set): void {
    $append_after_index = $batch['current_set'];
    $reached_current_set = FALSE;
    foreach ($batch['sets'] as $index => $set) {
      // As the indexes are not ordered numerically we need to first reach the
      // index of the current set and then search for the proper place to append
      // the new batch set.
      if (!$reached_current_set) {
        if ($index === $batch['current_set']) {
          $reached_current_set = TRUE;
        }
        continue;
      }
      if ($index > $append_after_index) {
        if (isset($set['appended_after_index'])) {
          $append_after_index = $index;
        }
        else {
          break;
        }
      }
    }
    $batch_set['appended_after_index'] = $append_after_index;

    // Iterate by reference over the existing batch sets and assign them by
    // reference in the new batch sets array in order not to break a retrieved
    // reference to the current set. Among other places a reference to the
    // current set is being retrieved in _batch_process(). Additionally, we have
    // to preserve the original indexes, as they are used to generate the queue
    // name of each batch set, otherwise the operations of the new batch set
    // will be queued in the queue of a previous batch set.
    // @see _batch_populate_queue().
    $new_sets = [];
    foreach ($batch['sets'] as $index => &$set) {
      $new_sets[$index] = &$set;
      if ($index === $append_after_index) {
        $new_set_index = count($batch['sets']);
        $new_sets[$new_set_index] = $batch_set;
      }
    }

    $batch['sets'] = $new_sets;
    $this->queuePopulate($batch, $new_set_index);
  }

  /**
   * {@inheritdoc}
   */
  public function queuePopulate(array &$batch, string $set_id): void {
    $batch_set = &$batch['sets'][$set_id];

    if (isset($batch_set['operations'])) {
      $batch_set += [
        'queue' => [
          'name' => 'drupal_batch:' . $batch['id'] . ':' . $set_id,
          'class' => $batch['progressive'] ? Batch::class : BatchMemory::class,
        ],
      ];

      $queue = $this->getQueue($batch_set);
      $queue->createQueue();
      foreach ($batch_set['operations'] as $operation) {
        $queue->createItem($operation);
      }

      unset($batch_set['operations']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getQueue(array $batch_set): ?QueueInterface {
    if (isset($batch_set['queue'])) {
      $name = $batch_set['queue']['name'];
      $class = $batch_set['queue']['class'];

      if (!isset($this->queues[$class][$name])) {
        $this->queues[$class][$name] = new $class($name, $this->getConnection());
      }
      return $this->queues[$class][$name];
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function process(Url|string $redirect = NULL, Url $url = NULL, string $redirect_callback = NULL): ?RedirectResponse {
    if ($this->batch) {
      // Add process information.
      $process_info = [
        'current_set' => 0,
        'progressive' => TRUE,
        'url' => $url ?? Url::fromRoute('system.batch_page.html'),
        'source_url' => Url::fromRouteMatch($this->routeMatch)->mergeOptions(['query' => $this->requestStack->getCurrentRequest()->query->all()]),
        'batch_redirect' => $redirect,
        'theme' => $this->themeManager->getActiveTheme()->getName(),
        'redirect_callback' => $redirect_callback,
      ];
      $this->batch += $process_info;

      // The batch is now completely built. Allow other modules to make changes
      // to the batch so that it is easier to reuse batch processes in other
      // environments.
      $this->moduleHandler->alter('batch', $this->batch);

      // Assign an arbitrary id: don't rely on a serial column in the 'batch'
      // table, since non-progressive batches skip database storage completely.
      $this->batch['id'] = $this->getConnection()->nextId();

      // Move operations to a job queue. Non-progressive batches will use a
      // memory-based queue.
      foreach ($this->batch['sets'] as $key => $batch_set) {
        $this->queuePopulate($this->batch, $key);
      }

      // Initiate processing.
      if ($this->batch['progressive']) {
        // Now that we have a batch id, we can generate the redirection link in
        // the generic error message.
        /** @var \Drupal\Core\Url $batch_url */
        $batch_url = $this->batch['url'];
        $error_url = clone $batch_url;
        $query_options = $error_url->getOption('query');
        $query_options['id'] = $this->batch['id'];
        $query_options['op'] = 'finished';
        $error_url->setOption('query', $query_options);

        $this->batch['error_message'] = $this->t('Please continue to <a href=":error_url">the error page</a>', [':error_url' => $error_url->toString(TRUE)->getGeneratedUrl()]);

        // Clear the way for the redirection to the batch processing page, by
        // saving and unsetting the 'destination', if there is any.
        $request = $this->requestStack->getCurrentRequest();
        if ($request && $request->query->has('destination')) {
          $this->batch['destination'] = $request->query->get('destination');
          $request->query->remove('destination');
        }

        // Store the batch.
        $this->getBatchStorage()?->create($this->batch);

        // Set the batch number in the session to guarantee that it will stay
        // alive.
        $_SESSION['batches'][$this->batch['id']] = TRUE;

        // Redirect for processing.
        $query_options = $error_url->getOption('query');
        $query_options['op'] = 'start';
        $query_options['id'] = $this->batch['id'];
        $batch_url->setOption('query', $query_options);
        if (($function = $this->batch['redirect_callback']) && function_exists($function)) {
          $function($batch_url->toString(), ['query' => $query_options]);
        }
        else {
          return new RedirectResponse($batch_url->setAbsolute()->toString(TRUE)->getGeneratedUrl());
        }
      }
      else {
        // Non-progressive execution: bypass the whole progressbar workflow
        // and execute the batch in one pass.
        $this->processQueue();
      }
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function &getCurrentBatch(): ?array {
    return $this->batch;
  }

  /**
   * {@inheritdoc}
   */
  public function processQueue(): array|NULL|RedirectResponse {
    $current_set = &$this->getCurrentSet();
    // Indicate that this batch set needs to be initialized.
    $set_changed = TRUE;

    // If this batch was marked for progressive execution (e.g. forms submitted
    // by \Drupal::formBuilder()->submitForm(), initialize a timer to determine
    // whether we need to proceed with the same batch phase when a processing
    // time of 1 second has been exceeded.
    if ($this->batch['progressive']) {
      Timer::start('batch_processing');
    }

    if (empty($current_set['start'])) {
      $current_set['start'] = microtime(TRUE);
    }

    $queue = $this->getQueue($current_set);
    // Set the completion level to 1 by default.
    $finished = 1;
    // Initialize $old_set.
    $old_set = $current_set;
    $task_message = '';
    while (!$current_set['success']) {
      // If this is the first time we iterate this batch set in the current
      // request, we check if it requires an additional file for functions
      // definitions.
      if ($set_changed && isset($current_set['file']) && is_file($current_set['file'])) {
        include_once $this->root . '/' . $current_set['file'];
      }

      $task_message = '';
      // Assume a single pass operation and set the completion level to 1 by
      // default.
      $finished = 1;

      if ($item = $queue->claimItem()) {
        [$callback, $args] = $item->data;

        // Build the 'context' array and execute the function call.
        $batch_context = [
          'sandbox'  => &$current_set['sandbox'],
          'results'  => &$current_set['results'],
          'finished' => &$finished,
          'message'  => &$task_message,
        ];
        call_user_func_array($callback, array_merge($args, [&$batch_context]));

        if ($finished >= 1) {
          // Make sure this step is not counted twice when computing $current.
          $finished = 0;
          // Remove the processed operation and clear the sandbox.
          $queue->deleteItem($item);
          $current_set['count']--;
          $current_set['sandbox'] = [];
        }
      }

      // When all operations in the current batch set are completed, browse
      // through the remaining sets, marking them 'successfully processed'
      // along the way, until we find a set that contains operations.
      // _batch_next_set() executes form submit handlers stored in 'control'
      // sets (see \Drupal::service('form_submitter')), which can in turn add
      // new sets to the batch.
      $set_changed = FALSE;
      $old_set = $current_set;
      while (empty($current_set['count']) && ($current_set['success'] = TRUE) && $this->nextSet()) {
        $current_set = &$this->getCurrentSet();
        $current_set['start'] = microtime(TRUE);
        $set_changed = TRUE;
      }

      // At this point, either $current_set contains operations that need to be
      // processed or all sets have been completed.
      $queue = $this->getQueue($current_set);

      // If we are in progressive mode, break processing after 1 second.
      if ($this->batch['progressive'] && Timer::read('batch_processing') > 1000) {
        // Record elapsed wall clock time.
        $current_set['elapsed'] = round((microtime(TRUE) - $current_set['start']) * 1000, 2);
        break;
      }
    }

    if ($this->batch['progressive']) {
      // Gather progress information.
      // Reporting 100% progress will cause the whole batch to be considered
      // processed. If processing was paused right after moving to a new set,
      // we have to use the info from the new (unprocessed) set.
      if ($set_changed && isset($current_set['queue'])) {
        // Processing will continue with a fresh batch set.
        $remaining        = $current_set['count'];
        $total            = $current_set['total'];
        $progress_message = $current_set['init_message'];
        $task_message     = '';
      }
      else {
        // Processing will continue with the current batch set.
        $remaining        = $old_set['count'];
        $total            = $old_set['total'];
        $progress_message = $old_set['progress_message'];
      }

      // Total progress is the number of operations that have fully run plus the
      // completion level of the current operation.
      $current    = $total - $remaining + $finished;
      $percentage = Percentage::format($total, $current);
      $elapsed    = $current_set['elapsed'] ?? 0;
      $values     = [
        '@remaining'  => $remaining,
        '@total'      => $total,
        '@current'    => floor($current),
        '@percentage' => $percentage,
        '@elapsed'    => $this->dateFormatter->formatInterval((int) ($elapsed / 1000)),
        // If possible, estimate remaining processing time.
        '@estimate'   => ($current > 0) ? $this->dateFormatter->formatInterval((int) (($elapsed * ($total - $current) / $current) / 1000)) : '-',
      ];
      $message    = strtr($progress_message, $values);

      return [$percentage, $message, $task_message];
    }
    // If we are not in progressive mode, the entire batch has been processed.
    return $this->finishedProcessing();
  }

  /**
   * {@inheritdoc}
   */
  public function &getCurrentSet(): array {
    return $this->batch['sets'][$this->batch['current_set']];
  }

  /**
   * {@inheritdoc}
   */
  public function nextSet(): bool {
    $set_indexes = array_keys($this->batch['sets']);
    $current_set_index_key = array_search($this->batch['current_set'], $set_indexes);
    if (isset($set_indexes[$current_set_index_key + 1])) {
      $this->batch['current_set'] = $set_indexes[$current_set_index_key + 1];
      $current_set = &$this->getCurrentSet();
      if (isset($current_set['form_submit']) && ($callback = $current_set['form_submit']) && is_callable($callback)) {
        // We use our stored copies of $form and $form_state to account for
        // possible alterations by previous form submit handlers.
        $complete_form = &$this->batch['form_state']->getCompleteForm();
        call_user_func_array($callback, [
          &$complete_form,
          &$this->batch['form_state'],
        ]);
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function finishedProcessing(): ?RedirectResponse {
    $batch_finished_redirect = NULL;

    // Execute the 'finished' callbacks for each batch set, if defined.
    foreach ($this->batch['sets'] as $batch_set) {
      if (isset($batch_set['finished'])) {
        // Check if the set requires an additional file for function
        // definitions.
        if (isset($batch_set['file']) && is_file($batch_set['file'])) {
          include_once $this->root . '/' . $batch_set['file'];
        }
        if (is_callable($batch_set['finished'])) {
          $queue = $this->getQueue($batch_set);
          $operations = $queue->getAllItems();
          $batch_set_result = call_user_func_array($batch_set['finished'], [
            $batch_set['success'],
            $batch_set['results'],
            $operations,
            $this->dateFormatter
              ->formatInterval((int) ($batch_set['elapsed'] / 1000)),
          ]);
          // If a batch 'finished' callback requested a redirect after the batch
          // is complete, save that for later use. If more than one batch set
          // returned a redirect, the last one is used.
          if ($batch_set_result instanceof RedirectResponse) {
            $batch_finished_redirect = $batch_set_result;
          }
        }
      }
    }

    // Clean up the batch table and unset the static $this->batch variable.
    if ($this->batch['progressive']) {
      $this->getBatchStorage()?->delete($this->batch['id']);
      foreach ($this->batch['sets'] as $batch_set) {
        if ($queue = $this->getQueue($batch_set)) {
          $queue->deleteQueue();
        }
      }
      // Clean-up the session. Not needed for CLI updates.
      if (isset($_SESSION)) {
        unset($_SESSION['batches'][$this->batch['id']]);
        if (empty($_SESSION['batches'])) {
          unset($_SESSION['batches']);
        }
      }
    }
    $_batch = $this->batch;
    $this->batch = NULL;

    // Redirect if needed.
    if ($_batch['progressive']) {
      // Revert the 'destination' that was saved in batch_process().
      if (isset($_batch['destination'])) {
        $this->requestStack->getCurrentRequest()->query->set('destination', $_batch['destination']);
      }

      // Determine the target path to redirect to. If a batch 'finished'
      // callback returned a redirect response object, use that. Otherwise, fall
      // back on the form redirection.
      if (isset($batch_finished_redirect)) {
        return $batch_finished_redirect;
      }
      $_batch['form_state'] ??= new FormState();
      if ($_batch['form_state']->getRedirect() === NULL) {
        $redirect = $_batch['batch_redirect'] ?: $_batch['source_url'];
        // Any path with a scheme does not correspond to a route.
        if (!$redirect instanceof Url) {
          $options = UrlHelper::parse($redirect);
          if (parse_url($options['path'], PHP_URL_SCHEME)) {
            $redirect = Url::fromUri($options['path'], $options);
          }
          else {
            $redirect = $this->getPathValidator()?->getUrlIfValid($options['path']);
            if (!$redirect) {
              // Stay on the same page if the redirect was invalid.
              $redirect = Url::fromRoute('<current>');
            }
            $redirect->setOptions($options);
          }
        }
        $_batch['form_state']->setRedirectUrl($redirect);
      }

      // Use \Drupal\Core\Form\FormSubmitterInterface::redirectForm() to handle
      // the redirection logic.
      $redirect = $this->formSubmitter->redirectForm($_batch['form_state']);
      if (is_object($redirect)) {
        return $redirect;
      }

      // If no redirection happened, redirect to the originating page. In case
      // the form needs to be rebuilt, save the final $form_state for
      // \Drupal\Core\Form\FormBuilderInterface::buildForm().
      if ($_batch['form_state']->isRebuilding()) {
        $_SESSION['batch_form_state'] = $_batch['form_state'];
      }
      $callback = $_batch['redirect_callback'];
      $_batch['source_url']->mergeOptions(['query' => ['op' => 'finish', 'id' => $_batch['id']]]);
      if (is_callable($callback)) {
        $callback($_batch['source_url'], $_batch['source_url']->getOption('query'));
      }
      elseif ($callback === NULL) {
        // Default to RedirectResponse objects when nothing specified.
        return new RedirectResponse($_batch['source_url']->setAbsolute()->toString());
      }
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getQueueForBatch($batch): ?QueueInterface {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function shutdown(): void {
    if (($this->batch) && _batch_needs_update()) {
      $this->getBatchStorage()->update($this->batch);
    }
  }

}
