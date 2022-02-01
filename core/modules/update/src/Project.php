<?php

namespace Drupal\update;

use Drupal\Core\Extension\ExtensionVersion;

/**
 * Calculates the update status of a project.
 */
final class Project {

  /**
   * The releases for the project that can be installed safely.
   *
   * @var array
   */
  private $installableReleases;

  /**
   * Project data from Drupal\update\UpdateManagerInterface::getProjects().
   *
   * @var array
   *
   * @see \Drupal\update\UpdateManagerInterface::getProjects()
   */
  private $projectData;

  /**
   * The update server project information.
   *
   * @var \Drupal\update\UpdateServerProjectInfo
   */
  private $updateServerProjectInfo;

  /**
   * Constructs a ProjectStatusCalculator object.
   *
   * @param array $project_data
   *   Project data from Drupal\update\UpdateManagerInterface::getProjects().
   * @param \Drupal\update\UpdateServerProjectInfo $project_info
   *   The update server project information.
   */
  private function __construct(array $project_data, UpdateServerProjectInfo $project_info) {
    $this->projectData = $project_data;
    $this->updateServerProjectInfo = $project_info;
  }

  /**
   * Creates a ProjectStatusCalculator object.
   *
   * @param array $project_data
   *   Data for a project as returned by
   *   Drupal\update\UpdateManagerInterface::getProjects().
   * @param \Drupal\update\UpdateServerProjectInfo $project_info
   *   The update server project information.
   *
   * @return \Drupal\update\Project
   *   The ProjectStatusCalculator instance.
   */
  public static function createFromProjectData(array $project_data, UpdateServerProjectInfo $project_info): Project {
    return new Project($project_data, $project_info);
  }

  /**
   * Determines if a version is a supported branch.
   *
   * @param string $version
   *   The version.
   *
   * @return bool
   *   TRUE if the version is supported branch of the project. otherwise false.
   */
  private function isInSupportedBranch(string $version): bool {
    foreach ($this->updateServerProjectInfo->getSupportBranches() as $supported_branch) {
      if (strpos($version, $supported_branch) === 0) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Determines if a release is installable.
   *
   * A release is considered installable if it is published, in a supported
   * branch, is supported itself and is not insecure.
   *
   * @param \Drupal\update\ProjectRelease $release
   *   The project release.
   *
   * @return bool
   *   TRUE if the release is installable, otherwise FALSE.
   */
  private function releaseIsInstallable(ProjectRelease $release): bool {
    return $release->isPublished() &&
      $this->isInSupportedBranch($release->getVersion()) &&
      !$release->isUnsupported() &&
      !$release->isInsecure();
  }

  /**
   * Gets all the installable releases up to and including the existing version.
   *
   * @return array[]
   *   The releases.
   *
   * @todo Right now this returns the current version regardless of whether this
   *   passes ::releaseIsInstallable(). This is because currently
   *   update_calculate_project_update_status() will consider the current
   *   version regardless of whether it passes this condition.
   */
  public function getInstallableReleases(): array {
    if (isset($this->installableReleases)) {
      return $this->installableReleases;
    }
    $this->installableReleases = [];
    foreach ($this->updateServerProjectInfo->getReleases() as $version => $release_info) {
      try {
        $release = ProjectRelease::createFromArray($release_info);
      }
      catch (\UnexpectedValueException $exception) {
        // Ignore releases that are in an invalid format. Although this is
        // highly unlikely we should still process releases in the correct
        // format.
        watchdog_exception(
          'update',
          $exception,
          'Invalid project format: @release',
          ['@release' => print_r($release_info, TRUE)]
        );
        continue;
      }
      try {
        // Ensure the version number string validates.
        ExtensionVersion::createFromVersionString($release->getVersion());
      }
      catch (\UnexpectedValueException $exception) {
        continue;
      }

      if ($this->releaseIsInstallable($release) || $version === $this->projectData['existing_version']) {
        $this->installableReleases[$version] = $release_info;
      }
      if ($version === $this->projectData['existing_version']) {
        break;
      }
    }
    return $this->installableReleases;
  }

}
