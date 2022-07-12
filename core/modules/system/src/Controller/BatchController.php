<?php

namespace Drupal\system\Controller;

use Drupal\Core\Batch\BatchProcessorInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Controller routines for batch routes.
 */
class BatchController implements ContainerInjectionInterface {

  /**
   * The app root.
   *
   * @var string
   */
  protected $root;

  /**
   * Batch processor.
   *
   * @var \Drupal\Core\Batch\BatchProcessorInterface
   */
  protected BatchProcessorInterface $batchProcessor;

  /**
   * Constructs a new BatchController.
   *
   * @param string $root
   *   The app root.
   * @param \Drupal\Core\Batch\BatchProcessorInterface|null $batch_processor
   *   Batch processor.
   *
   * @see https://www.drupal.org/node/3229844
   */
  public function __construct($root, BatchProcessorInterface $batch_processor = NULL) {
    $this->root = $root;
    if ($batch_processor === NULL) {
      @trigger_error('Calling ' . __METHOD__ . ' without the $batch_processor argument is deprecated in drupal:10.1.0 and it will be required in drupal:11.0.0. See https://www.drupal.org/node/3229844', E_USER_DEPRECATED);
      $batch_processor = \Drupal::service('batch.processor');
    }
    $this->batchProcessor = $batch_processor;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->getParameter('app.root'),
      $container->get('batch.processor')
    );
  }

  /**
   * Returns a system batch page.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object.
   *
   * @return \Symfony\Component\HttpFoundation\Response|array
   *   A \Symfony\Component\HttpFoundation\Response object or render array.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   */
  public function batchPage(Request $request) {
    require_once $this->root . '/core/includes/batch.inc';
    $output = _batch_page($request);

    if ($output === FALSE) {
      throw new AccessDeniedHttpException();
    }
    elseif ($output instanceof Response) {
      return $output;
    }
    elseif (isset($output)) {
      $title = $output['#title'] ?? NULL;
      $page = [
        '#type' => 'page',
        '#title' => $title,
        '#show_messages' => FALSE,
        'content' => $output,
      ];

      // Also inject title as a page header (if available).
      if ($title) {
        $page['header'] = [
          '#type' => 'page_title',
          '#title' => $title,
        ];
      }

      return $page;
    }
  }

  /**
   * The _title_callback for the system.batch_page.normal route.
   *
   * @return string
   *   The page title.
   */
  public function batchPageTitle() {
    $current_set = $this->batchProcessor->getCurrentSet();
    return !empty($current_set['title']) ? $current_set['title'] : '';
  }

}
