<?php

namespace Drupal\package_manager\Hook;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\package_manager\ComposerInspector;
use Drupal\package_manager\Exception\StageFailureMarkerException;
use Drupal\package_manager\FailureMarker;
use PhpTuf\ComposerStager\API\Exception\ExceptionInterface;
use PhpTuf\ComposerStager\API\Finder\Service\ExecutableFinderInterface;

/**
 * Hook implementations for media.
 */
class PackageManagerRequirementsHooks {

  use StringTranslationTrait;

  public function __construct(
    protected readonly ComposerInspector $composerInspector,
    protected ExecutableFinderInterface $executableFinder,
  ) {}

  /**
   * Implements hook_runtime_requirements().
   */
  #[Hook('runtime_requirements')]
  public function runtime(): array {
    $requirements = $this->checkFailure();

    // Report the Composer version in use, as well as its path.
    $title = $this->t('Composer version');
    try {
      $requirements['package_manager_composer'] = [
        'title' => $title,
        'description' => $this->t('@version (<code>@path</code>)', [
          '@version' => $this->composerInspector->getVersion(),
          '@path' => $this->executableFinder->find('composer'),
        ]),
        'severity' => REQUIREMENT_INFO,
      ];
    }
    catch (\Throwable $e) {
      // All Composer Stager exceptions are translatable.
      $message = $e instanceof ExceptionInterface
        ? $e->getTranslatableMessage()
        : $e->getMessage();

      $requirements['package_manager_composer'] = [
        'title' => $title,
        'description' => $this->t('Composer was not found. The error message was: @message', [
          '@message' => $message,
        ]),
        'severity' => REQUIREMENT_ERROR,
      ];
    }

    return $requirements;
  }

  /**
   * Implements hook_update_requirements().
   */
  #[Hook('update_requirements')]
  public function update(): array {
    return $this->checkFailure();
  }

  public function checkFailure(): array {
    $requirements = [];
    // If we're able to check for the presence of the failure marker at all, do it
    // irrespective of the current run phase. If the failure marker is there, the
    // site is in an indeterminate state and should be restored from backup ASAP.
    $service_id = FailureMarker::class;
    if (\Drupal::hasService($service_id)) {
      try {
        \Drupal::service($service_id)->assertNotExists(NULL);
      }
      catch (StageFailureMarkerException $exception) {
        $requirements['package_manager_failure_marker'] = [
          'title' => $this->t('Failed Package Manager update detected'),
          'description' => $exception->getMessage(),
          'severity' => REQUIREMENT_ERROR,
        ];
      }
    }

    return $requirements;
  }

}
