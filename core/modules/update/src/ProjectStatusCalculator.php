<?php

namespace Drupal\update;

use Drupal\Core\Extension\ExtensionVersion;

/**
 * Calculates the update status of a project.
 */
class ProjectStatusCalculator {


  /**
   * @var array
   */
  protected $projectData;

  /**
   * @var \Drupal\update\UpdateServerProjectInfo
   */
  protected $updateServerProjectInfo;

  /**
   * @param array $project_data
   * @param \Drupal\update\UpdateServerProjectInfo $projectInfo
   */
  private function __construct(array $project_data, UpdateServerProjectInfo $projectInfo) {
    $this->projectData = $project_data;
    $this->updateServerProjectInfo = $projectInfo;
  }

  /**
   * Creates a ProjectStatusCalculator object.
   *
   * @param array $projectData
   * @param \Drupal\update\UpdateServerProjectInfo $projectInfo
   *
   * @return \Drupal\update\ProjectStatusCalculator
   */
  public static function createFromProjectData(array $projectData, UpdateServerProjectInfo $projectInfo): ProjectStatusCalculator {
    return new static($projectData, $projectInfo);
  }

  public function getStatus(): int {
    $server_status = $this->updateServerProjectInfo->getProjectStatus();
  }

  public function getDevReleaseForTargetMajor(): ?array {
    if (!$this->isProjectUsable()) {
      return NULL;
    }
    $target_major = $this->getTargetMajor();
    foreach ($this->getInstallableReleases() as $version => $release_info) {
      $release_module_version = ExtensionVersion::createFromVersionString($version);
      if ($target_major === $release_module_version->getMajorVersion()
        && $release_module_version->getVersionExtra() === 'dev') {
        return $release_info;
      }
    }
    return NULL;
  }

  public function getLatestReleaseForTargetMajor(): ?array {
    if (!$this->isProjectUsable()) {
      return NULL;
    }
    $target_major = $this->getTargetMajor();
    foreach ($this->getInstallableReleases() as $version => $release_info) {
      if ($target_major === ExtensionVersion::createFromVersionString($version)->getMajorVersion()) {
        return $release_info;
      }
    }
    return NULL;
  }

  public function getRecommendReleaseForTargetMajor(): ?array {
    if (!$this->isProjectUsable()) {
      return NULL;
    }
    $target_major = $this->getTargetMajor();
    $recommended_version_without_extra = '';
    $recommended_release = NULL;
    foreach ($this->getInstallableReleases() as $version => $release_info) {
      $release_module_version = ExtensionVersion::createFromVersionString($version);
      if ($release_module_version->getVersionExtra()) {
        $release_version_without_extra = str_replace('-' . $release_module_version->getVersionExtra(), '', $version);
      }
      else {
        $release_version_without_extra = $version;
      }

      if ($release_module_version->getMajorVersion() == $target_major) {
        if ($recommended_version_without_extra !== $release_version_without_extra) {
          $recommended_version_without_extra = $release_version_without_extra;
          $recommended_release = $release_info;
        }
        if ($release_module_version->getVersionExtra() === NULL) {
          // @todo we explicitly NOT returning $release_info but instead
          //   $recommended_version_without_extra research again why this is
          //   and comment or change.
          return $recommended_release;
        }
      }
    }
    // We didn't find a release.
    $latest_release = $this->getLatestReleaseForTargetMajor();
    if ($latest_release) {
      return $latest_release;
    }
    return NULL;
  }

  /**
   * @return array[]
   *   An array of release key by major version.
   */
  public function getReleasesInMajorsGreaterThanTarget(): array {
    if (!$this->isProjectUsable()) {
      return [];
    }
    $target_major = $this->getTargetMajor();
    $releases = [];
    foreach ($this->getInstallableReleases() as $version => $release_info) {
      $release_major_version = ExtensionVersion::createFromVersionString($version)->getMajorVersion();
      if ($release_major_version > $target_major && !isset($releases[$release_major_version])) {
        $releases[$release_major_version] = $release_info;
      }
    }
    return $releases;
  }

  private function getExistingVersion(): ?ExtensionVersion {
    if (isset($this->projectData['existing_version'])) {
      try {
        return ExtensionVersion::createFromVersionString($this->projectData['existing_version']);
      }
      catch (\UnexpectedValueException $exception) {
        return NULL;
      }
    }
    return NULL;
  }

  private function getTargetMajor(): ?string  {
    $existing_version = $this->getExistingVersion();
    if (!$existing_version) {
      return NULL;
    }
    $existing_major = $existing_version->getMajorVersion();
    if (!empty($this->projectData['existing_version'])) {
      if ($this->isInsupportedBranch($this->projectData['existing_version'])) {
        return $existing_major;
      }
    }
    foreach ($this->updateServerProjectInfo->getSupportBranches() as $supported_branch) {
      try {
        $target_major = ExtensionVersion::createFromSupportBranch($supported_branch)->getMajorVersion();
        break;
      }
      catch (\UnexpectedValueException $exception) {
        continue;
      }
    }
    if (!isset($target_major) || $target_major < $existing_major) {
      return $existing_major;
    }
    return NULL;
  }

  private function isInsupportedBranch(string $version) {
    foreach ($this->updateServerProjectInfo->getSupportBranches() as $supported_branch) {
      if (strpos($version, $supported_branch) === 0) {
        return TRUE;
      }
    }
    return FALSE;
  }

  private function releaseIsInstallable(ProjectRelease $release): bool {
    return $release->isPublished() &&
      $this->isInsupportedBranch($release->getVersion()) &&
      !$release->isUnsupported() &&
      !$release->isInsecure();
  }

  public function getSecurityReleases(): array {
    if (!$this->isProjectUsable()) {
      return [];
    }
    $target_major = $this->getTargetMajor();
    $releases = [];
    foreach ($this->getInstallableReleases() as $version => $release_info) {
      $release_major_version = ExtensionVersion::createFromVersionString($version)->getMajorVersion();
      $release = ProjectRelease::createFromArray($release_info);
      if ($release_major_version > $target_major) {
        // *** Original note about why we don't care about security releases in later majors.
        // Otherwise, this release can't matter to us, since it's neither
        // from the release series we're currently using nor the recommended
        // release. We don't even care about security updates for this
        // branch, since if a project maintainer puts out a security release
        // at a higher major version and not at the lower major version,
        // they must remove the lower version from the supported major
        // versions at the same time, in which case we won't hit this code.
        continue;
      }
      if ($version === $this->projectData['existing_version']) {
        break;
      }
      // If we're running a dev snapshot and have a timestamp, stop
      // searching for security updates once we hit an official release
      // older than what we've got. Allow 100 seconds of leeway to handle
      // differences between the datestamp in the .info.yml file and the
      // timestamp of the tarball itself (which are usually off by 1 or 2
      // seconds) so that we don't flag that as a new release.
      if ($this->projectData['install_type'] === 'dev') {
        if (empty($project_data['datestamp'])) {
          // We don't have current timestamp info, so we can't know.
          continue;
        }
        elseif ($release->getDate() && $project_data['datestamp'] + 100 > $release->getDate()) {
          // We're newer than this, so we can skip it.
          continue;
        }
      }

      if ($release->isSecurityRelease()) {
        $releases[$version] = $release_info;
      }
    }
    return $releases;
  }

  /**
   * @param bool $until_existing
   * @return mixed[]
   */
  private function getInstallableReleases(): array {
    $releases = [];
    foreach ($this->updateServerProjectInfo->getReleases() as $version => $release_info) {
      try {
        $release = ProjectRelease::createFromArray($release_info);
      }
      catch (\UnexpectedValueException $exception) {
        // Ignore releases that are in an invalid format. Although this is highly
        // unlikely we should still process releases in the correct format.
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
      if ($this->releaseIsInstallable($release)) {
        $releases[$version] = $release_info;
      }
      if ($version === $this->projectData['existing_version']) {
        break;
      }
    }
    return $releases;
  }

  /**
   * Determines if we can recommend any thing in the project.
   *
   * @return bool
   */
  private function isProjectUsable() {
    $unusable_project_statuses = [
      'insecure',
      'unpublished',
      'revoked',
      'unsupported',
      'not-fetched',
    ];
    if (in_array($this->updateServerProjectInfo->getProjectStatus(), $unusable_project_statuses)) {
      return FALSE;
    }
    $existing_version = $this->getExistingVersion();
    if (!$existing_version) {
      return FALSE;
    }
    return TRUE;
  }


}
