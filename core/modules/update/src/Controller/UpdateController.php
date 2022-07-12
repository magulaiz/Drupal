<?php

namespace Drupal\update\Controller;

use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Batch\BatchProcessorInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\RendererInterface;
use Drupal\update\UpdateFetcherInterface;
use Drupal\update\UpdateManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller routines for update routes.
 */
class UpdateController extends ControllerBase {

  /**
   * Update manager service.
   *
   * @var \Drupal\update\UpdateManagerInterface
   */
  protected $updateManager;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Batch processor.
   *
   * @var \Drupal\Core\Batch\BatchProcessorInterface
   */
  protected BatchProcessorInterface $batchProcessor;

  /**
   * Constructs update status data.
   *
   * @param \Drupal\update\UpdateManagerInterface $update_manager
   *   Update Manager Service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Core\Batch\BatchProcessorInterface|null $batch_processor
   *   Batch processor.
   *
   * @see https://www.drupal.org/node/3229844
   */
  public function __construct(UpdateManagerInterface $update_manager, RendererInterface $renderer, BatchProcessorInterface $batch_processor = NULL) {
    $this->updateManager = $update_manager;
    $this->renderer = $renderer;
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
      $container->get('update.manager'),
      $container->get('renderer'),
      $container->get('batch.processor')
    );
  }

  /**
   * Returns a page about the update status of projects.
   *
   * @return array
   *   A build array with the update status of projects.
   */
  public function updateStatus() {
    $build = [
      '#theme' => 'update_report',
    ];
    if ($available = update_get_available(TRUE)) {
      $this->moduleHandler()->loadInclude('update', 'compare.inc');
      $build['#data'] = update_calculate_project_data($available);

      // @todo Consider using 'fetch_failures' from the 'update' collection
      // in the key_value_expire service for this?
      $fetch_failed = FALSE;
      foreach ($build['#data'] as $project) {
        if ($project['status'] === UpdateFetcherInterface::NOT_FETCHED) {
          $fetch_failed = TRUE;
          break;
        }
      }
      if ($fetch_failed) {
        $message = ['#theme' => 'update_fetch_error_message'];
        $this->messenger()->addError($this->renderer->renderPlain($message));
      }
    }
    return $build;
  }

  /**
   * Manually checks the update status without the use of cron.
   */
  public function updateStatusManually() {
    $this->updateManager->refreshUpdateData();
    $batch_builder = (new BatchBuilder())
      ->setTitle(t('Checking available update data'))
      ->addOperation([$this->updateManager, 'fetchDataBatch'], [])
      ->setProgressMessage(t('Trying to check available update data ...'))
      ->setErrorMessage(t('Error checking available update data.'))
      ->setFinishCallback('update_fetch_data_finished');
    $this->batchProcessor->queue($batch_builder->toArray());
    return $this->batchProcessor->process('admin/reports/updates');
  }

}
