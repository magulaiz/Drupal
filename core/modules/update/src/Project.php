<?php

namespace Drupal\update;

use Drupal\Core\Extension\ExtensionVersion;

/**
 * Provides a project release value object.
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
  public function isInSupportedBranch(string $version): bool {
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
   * @param array $release_info
   *   The release information as returned by
   *   \Drupal\update\UpdateServerProjectInfo::getReleases().
   *
   * @return bool
   *   TRUE if the release is installable, otherwise FALSE.
   */
  private function releaseIsInstallable(array $release_info): bool {
    if (!$this->isReleaseValid($release_info)) {
      return FALSE;
    }
    $release = ProjectRelease::createFromArray($release_info);
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
   */
  public function getInstallableReleases(): array {
    if (isset($this->installableReleases)) {
      return $this->installableReleases;
    }
    $this->installableReleases = [];
    foreach ($this->updateServerProjectInfo->getReleases() as $version => $release_info) {
      if ($version === $this->projectData['existing_version']) {
        break;
      }
      if ($this->releaseIsInstallable($release_info)) {
        $this->installableReleases[$version] = $release_info;
      }

    }
    return $this->installableReleases;
  }

  /**
   * Determines if a project release data is valid.
   *
   * @param array $release_info
   *   The release information as returned by
   *   \Drupal\update\UpdateServerProjectInfo::getReleases().
   *
   * @return bool
   *   TRUE if the project release data is valid, otherwise FALSE.
   */
  private function isReleaseValid(array $release_info): bool {
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
      return FALSE;
    }
    try {
      // Ensure the version number string validates.
      ExtensionVersion::createFromVersionString($release->getVersion());
    }
    catch (\UnexpectedValueException $exception) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Gets the existing release.
   *
   * @return array|null
   *   The existing release if any, otherwise NULL.
   */
  public function getExistingRelease(): ?array {
    if (!isset($this->projectData['existing_version'])) {
      return NULL;
    }
    $existing_version = $this->projectData['existing_version'];
    $releases = $this->updateServerProjectInfo->getReleases();
    if (isset($releases[$existing_version])) {
      $existing_release = $releases[$existing_version];
      if ($this->isReleaseValid($existing_release)) {
        return $existing_release;
      }
    }
    return NULL;
  }

}
