<?php

declare(strict_types=1);

namespace Drupal\update;

use Drupal\Core\Extension\ExtensionVersion;
use Drupal\Core\Utility\Error;

/**
 * Provides a project release value object.
 */
final class Project {

  /**
   * The existing version of project on the site.
   *
   * @var string
   */
  protected $existingVersion;

  /**
   * The releases of this project that can be installed safely.
   *
   * @var array
   */
  private $installableReleases;

  /**
   * The update server project information.
   *
   * @var \Drupal\update\UpdateServerProjectInfo
   */
  private $updateServerProjectInfo;

  /**
   * Constructs a ProjectStatusCalculator object.
   *
   * @param \Drupal\update\UpdateServerProjectInfo $project_info
   *   The update server project information.
   * @param string|null $existing_version
   *   The existing version of project on the site, if any.
   */
  private function __construct(UpdateServerProjectInfo $project_info, string $existing_version = NULL) {
    $this->existingVersion = $existing_version;
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
    return new Project($project_info, $project_data['existing_version'] ?? NULL);
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
      if (str_starts_with($version, $supported_branch)) {
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
  private function isInstallableRelease(array $release_info): bool {
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
   * Gets all the installable releases.
   *
   * @return array[]
   *   The releases.
   *
   * @see self::isInstallableRelease()
   */
  public function getInstallableReleases(): array {
    if (isset($this->installableReleases)) {
      return $this->installableReleases;
    }
    $this->installableReleases = [];
    if (!$this->updateServerProjectInfo->isProjectRecommendable()) {
      // If the project is not recommendable do not consider any releases.
      // return $this->installableReleases;
    }

    foreach ($this->updateServerProjectInfo->getReleases() as $version => $release_info) {
      if ($version === $this->existingVersion) {
        // Because \Drupal\update\UpdateServerProjectInfo::getReleases() returns
        // releases ordered by descending version number we can exit after we
        // find the existing version as everything after this would be a
        // downgrade.
        break;
      }
      if ($this->isInstallableRelease($release_info)) {
        $this->installableReleases[$version] = $release_info;
      }
    }
    return $this->installableReleases;
  }

  /**
   * Determines if a project release is valid.
   *
   * @param array $release_info
   *   The release information as returned by
   *   \Drupal\update\UpdateServerProjectInfo::getReleases().
   *
   * @return bool
   *   TRUE if the project release data is valid, otherwise FALSE.
   */
  private function isReleaseValid(array $release_info): bool {
    $release = NULL;
    try {
      $release = ProjectRelease::createFromArray($release_info);
      // Ensure the version number string validates.
      ExtensionVersion::createFromVersionString($release->getVersion());
    }
    catch (\UnexpectedValueException $exception) {
      // Ignore releases that are in an invalid format. Although this is
      // highly unlikely we should still process releases in the correct
      // format.
      if ($release) {
        $message = 'Invalid version string : @version';
        $placeholders = ['@version' => $release->getVersion()];
      }
      else {
        $message = 'Invalid project format: @release';
        $placeholders = ['@release' => print_r($release_info, TRUE)];
      }
      Error::logException(
        \Drupal::logger('update'),
        $exception,
        $message,
        $placeholders
      );
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
    if (!$this->existingVersion) {
      return NULL;
    }
    $releases = $this->updateServerProjectInfo->getReleases();
    if (isset($releases[$this->existingVersion])) {
      $existing_release = $releases[$this->existingVersion];
      if ($this->isReleaseValid($existing_release)) {
        return $existing_release;
      }
    }
    return NULL;
  }

}
