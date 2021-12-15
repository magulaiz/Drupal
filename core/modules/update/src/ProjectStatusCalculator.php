<?php

namespace Drupal\update;

use Drupal\Core\Extension\ExtensionVersion;

/**
 * Calculates the update status of a project.
 */
class ProjectStatusCalculator {

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
   */
  protected $projectData;

  /**
   * The update server project information.
   *
   * @var \Drupal\update\UpdateServerProjectInfo
   */
  protected $updateServerProjectInfo;

  /**
   * Constructs a ProjectStatusCalculator object.
   *
   * @param array $project_data
   *   Project data from Drupal\update\UpdateManagerInterface::getProjects().
   * @param \Drupal\update\UpdateServerProjectInfo $projectInfo
   *   The update server project information.
   */
  private function __construct(array $project_data, UpdateServerProjectInfo $projectInfo) {
    $this->projectData = $project_data;
    $this->updateServerProjectInfo = $projectInfo;
  }

  /**
   * Creates a ProjectStatusCalculator object.
   *
   * @param array $projectData
   *   Project data from Drupal\update\UpdateManagerInterface::getProjects().
   * @param \Drupal\update\UpdateServerProjectInfo $projectInfo
   *   The update server project information.
   *
   * @return \Drupal\update\ProjectStatusCalculator
   *   The ProjectStatusCalculator instance.
   */
  public static function createFromProjectData(array $projectData, UpdateServerProjectInfo $projectInfo): ProjectStatusCalculator {
    return new ProjectStatusCalculator($projectData, $projectInfo);
  }

  /**
   * Gets the develop release if any for the target major.
   *
   * @return array|null
   *   The development release if available, otherwise NULL.
   */
  public function getDevReleaseForTargetMajor(): ?array {
    if (!$this->isProjectRecommendable()) {
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

  /**
   * Gets the latest release if any for the target major.
   *
   * @return array|null
   *   The latest release if available, otherwise NULL.
   */
  public function getLatestReleaseForTargetMajor(): ?array {
    if (!$this->isProjectRecommendable()) {
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

  /**
   * Gets the recommended release for target major.
   *
   * @return array|null
   *   The recommended release if available, otherwise NULL.
   */
  public function getRecommendReleaseForTargetMajor(): ?array {
    if (!$this->isProjectRecommendable()) {
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
   * Gets releases that are majors greater than the target major.
   *
   * @return array[]
   *   An array of releases keyed by major version.
   */
  public function getReleasesInMajorsGreaterThanTarget(): array {
    if (!$this->isProjectRecommendable()) {
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

  /**
   * Gets the existing version if any.
   *
   * @return \Drupal\Core\Extension\ExtensionVersion|null
   *   The existing version if available, otherwise NULL.
   */
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

  /**
   * Gets the target major.
   *
   * If the project itself is valid, the function decides what major release
   * series to consider. The project defines its currently supported branches in
   * its Drupal.org for the project, so the first step is to make sure the
   * development branch of the current version is still supported. If so, then
   * the major version of the current version is used. If the current version is
   * not in a supported branch, the next supported branch is used to determine
   * the major version to use. There's also a check to make sure that this
   * function never recommends an earlier release than the currently installed
   * major version.
   *
   * @return string|null
   *   The target major if available, otherwise NULL.
   */
  private function getTargetMajor(): ?string {
    $existing_version = $this->getExistingVersion();
    if (!$existing_version) {
      return NULL;
    }
    $existing_major = $existing_version->getMajorVersion();
    if (!empty($this->projectData['existing_version'])) {
      if ($this->isInSupportedBranch($this->projectData['existing_version'])) {
        return $existing_major;
      }
    }
    foreach ($this->updateServerProjectInfo->getSupportBranches() as $supported_branch) {
      try {
        $target_major = ExtensionVersion::createFromSupportBranch($supported_branch)->getMajorVersion();
        // We should `break;` here but this is a bug see
        // https://www.drupal.org/project/i/3227518.
      }
      catch (\UnexpectedValueException $exception) {
        continue;
      }
    }
    // Never target a lower major.
    return max($existing_major, $target_major ?? 0);
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
  private function isInSupportedBranch(string $version) {
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
   * Gets the security releases.
   *
   * @return array[]
   *   An array where the keys are version numbers and values are arrays of
   *   release information.
   */
  public function getSecurityReleases(): array {
    if (!$this->isProjectRecommendable()) {
      return [];
    }
    $target_major = $this->getTargetMajor();
    $releases = [];
    foreach ($this->getInstallableReleases() as $version => $release_info) {
      $release_major_version = ExtensionVersion::createFromVersionString($version)->getMajorVersion();
      $release = ProjectRelease::createFromArray($release_info);
      if ($release_major_version > $target_major) {
        // Original note about why we don't care about security releases in
        // later majors:
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
   * Gets all the installable releases up to and including the existing version.
   *
   * @todo Right now this returns the current version regardless of whether this
   *   passes ::releaseIsInstallable(). This is because currently
   *   update_calculate_project_update_status() will consider the current
   *   version regardless of whether it passes this condition.
   *
   * @return array[]
   *   The releases.
   */
  private function getInstallableReleases(): array {
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

  /**
   * Determines if we can recommend any release in the project.
   *
   * @return bool
   *   TRUE if the project can have any release recommended, otherwise false.
   */
  private function isProjectRecommendable() {
    $unusable_project_statuses = [
      'insecure',
      'unpublished',
      'revoked',
      'unsupported',
      'not-fetched',
    ];
    if (in_array($this->updateServerProjectInfo->getStatus(), $unusable_project_statuses)) {
      return FALSE;
    }
    $existing_version = $this->getExistingVersion();
    if (!$existing_version) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Gets the project status.
   *
   * @todo Unify this with the logic in update_calculate_project_update_status
   *   determines status from the $available['project_status']. This will be
   *   tricky because the status from there could mean we don't go look at actual
   *   releases. The means for example we need to know if UpdateManagerInterface::NOT_SECURE
   *   came from $available['project_status'].
   *
   * @return int|null
   */
  public function getStatus(): ?int {
    $status = NULL;
    // @todo confirm the order of statuses checked.
    if (isset($this->projectData['existing_version']) && !$this->isInSupportedBranch($this->projectData['existing_version'])) {
      $status = UpdateManagerInterface::NOT_SUPPORTED;
    }
    // The status determined by the release information directly should override
    // the status set above.
    if ($existing_release_info = $this->getExistingRelease()) {
      $existing_release = ProjectRelease::createFromArray($existing_release_info);
      if ($existing_release->isInsecure()) {
        $status = UpdateManagerInterface::NOT_SECURE;
      }
      elseif (!$existing_release->isPublished()) {
        $status = UpdateManagerInterface::REVOKED;
      }
      elseif ($existing_release->isUnsupported()) {
        $status = UpdateManagerInterface::NOT_SUPPORTED;
      }
    }

    if ($status) {
      return $status;
    }

    $recommended_release_info = $this->getRecommendReleaseForTargetMajor();
    if (!$recommended_release_info) {
      return UpdateFetcherInterface::UNKNOWN;
    }

    switch ($this->getInstallType()) {
      case 'official':
        $latest_release_info = $this->getLatestReleaseForTargetMajor();
        if ($existing_release_info && ($existing_release_info['version'] === $recommended_release_info['version'] || $existing_release_info['version'] === $latest_release_info['version'])) {
          $status = UpdateManagerInterface::CURRENT;
        }
        else {
          $status = UpdateManagerInterface::NOT_CURRENT;
        }
        break;

      case 'dev':
        $latest_dev_release_info = ProjectRelease::createFromArray($this->getLatestDev());
        if (empty($this->projectData['datestamp'])) {
          $status = UpdateFetcherInterface::NOT_CHECKED;
        }
        elseif ($this->projectData['datestamp'] + 100 > $latest_dev_release_info->getDate()) {
          $status = UpdateManagerInterface::CURRENT;
        }
        else {
          $status = UpdateManagerInterface::NOT_CURRENT;
        }
        break;

      default:
        $status = UpdateFetcherInterface::UNKNOWN;
    }
    return $status;
  }

  private function getExistingRelease() {
    if (isset($this->projectData['existing_version'])) {
      $releases = $this->updateServerProjectInfo->getReleases();
      return $releases[$this->projectData['existing_version']] ?? NULL;
    }
    return NULL;
  }

  /**
   * Gets the latest development release.
   *
   * @return array|null
   */
  public function getLatestDev(): ?array {
    // If we're running a dev snapshot, compare the date of the dev snapshot
    // with the latest official version, and record the absolute latest in
    // 'latest_dev' so we can correctly decide if there's a newer release
    // than our current snapshot.
    if ($this->getInstallType() === 'dev') {
      $dev_release_info = $this->getDevReleaseForTargetMajor();
      $latest_release_info = $this->getLatestReleaseForTargetMajor();
      if ($dev_release_info && $latest_release_info) {
        $dev_release = ProjectRelease::createFromArray($dev_release_info);
        $latest_release = ProjectRelease::createFromArray($latest_release_info);
        $dev_date = $dev_release->getDate();
        $latest_date = $latest_release->getDate();
        if ($dev_date && $latest_date && $dev_date > $latest_date) {
          return $dev_release_info;
        }
      }
      else {
        return $latest_release_info;
      }
    }
    return NULL;
  }

  /**
   * Gets the install type, either 'official', 'dev', or 'unknown'.
   *
   * @return string
   *   The install type.
   */
  public function getInstallType(): string {
    return $this->projectData['install_type'];
  }

}
