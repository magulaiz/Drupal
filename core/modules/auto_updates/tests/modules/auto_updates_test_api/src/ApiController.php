<?php

declare(strict_types=1);

namespace Drupal\auto_updates_test_api;

use Drupal\auto_updates\UpdateStage;
use Drupal\package_manager\PathLocator;
use Drupal\package_manager_test_api\ApiController as PackageManagerApiController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends PackageManagerApiController {

  /**
   * {@inheritdoc}
   */
  protected string $finishedRoute = 'auto_updates_test_api.finish';

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get(UpdateStage::class),
      $container->get(PathLocator::class),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function createAndApplyStage(Request $request): string {
    $id = $this->stage->begin($request->get('projects', []));
    $this->stage->stage();
    $this->stage->apply();
    return $id;
  }

  /**
   * Deletes last cron run time, so Automated Cron will run during this request.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response.
   */
  public function resetCron(): Response {
    \Drupal::state()->delete('system.cron_last');
    return new Response('cron reset');
  }

}
