<?php

namespace Drupal\update;

use Drupal\Core\Extension\ExtensionVersion;

/**
 * Calculates the update status of a project.
 */
final class ProjectStatusCalculator {

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
   * @return \Drupal\update\ProjectStatusCalculator
   *   The ProjectStatusCalculator instance.
   */
  public static function createFromProjectData(array $project_data, UpdateServerProjectInfo $project_info): ProjectStatusCalculator {
    return new ProjectStatusCalculator($project_data, $project_info);
  }

  /**
   * Gets the latest development release if any for a major version.
   *
   * Development release end with the string "-dev".
   *
   * @param string|null $major
   *   (optional) The major version to use. Defaults to NULL. If no major
   *   version is specified the target major will be used.
   *
   * @return array|null
   *   The development release if available, otherwise NULL.
   *
   * @see self::getTargetMajor()
   */
  public function getLatestDevReleaseForMajor(string $major = NULL): ?array {
    if (!$this->isProjectRecommendable()) {
      return NULL;
    }
    $major = $major ?? $this->getTargetMajor();
    foreach ($this->getInstallableReleases($major) as $version => $release_info) {
      $release_module_version = ExtensionVersion::createFromVersionString($version);
      if ($release_module_version->getVersionExtra() === 'dev') {
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
   *
   * @see self::getTargetMajor()
   */
  public function getRecommendReleaseForTargetMajor(): ?array {
    if (!$this->isProjectRecommendable()) {
      return NULL;
    }
    $target_major = $this->getTargetMajor();
    $recommended_version_without_extra = '';
    $first_installable_release_in_major = NULL;
    $releases = $this->getInstallableReleases($target_major);
    foreach ($releases as $version => $release_info) {
      $release_module_version = ExtensionVersion::createFromVersionString($version);
      if ($release_module_version->getVersionExtra()) {
        $release_version_without_extra = str_replace('-' . $release_module_version->getVersionExtra(), '', $version);
      }
      else {
        $release_version_without_extra = $version;
      }

      if ($recommended_version_without_extra !== $release_version_without_extra) {
        $recommended_version_without_extra = $release_version_without_extra;
        $first_installable_release_in_major = $release_info;
      }
      if ($release_module_version->getVersionExtra() === NULL) {
        // Once we have found the first version in this major that does not
        // have an extra version string return
        // $first_installable_release_in_major as the recommended release.
        // @see \Drupal\Core\Extension\ExtensionVersion::getVersionExtra()
        return $first_installable_release_in_major;
      }
    }
    // We didn't find a release.
    return array_shift($releases);
  }

  /**
   * Gets the existing version, if any.
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
   * series to consider. The first step is to make sure the development branch
   * of the current version is still supported. If so, then the major version of
   * the current version is used. If the current version is not in a supported
   * branch, the next supported branch is used to determine the major version to
   * use. There's also a check to make sure that this function never recommends
   * an earlier release than the currently installed major version.
   *
   * @return string|null
   *   The target major if available, otherwise NULL.
   *
   * @see \Drupal\update\UpdateServerProjectInfo::getSupportBranches()
   */
  public function getTargetMajor(): ?string {
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
  private function isInSupportedBranch(string $version): bool {
    foreach ($this->updateServerProjectInfo->getSupportBranches() as $supported_branch) {
      if (strpos($version, $supported_branch) === 0) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Gets the supported majors for a project.
   *
   * @return string[]
   *   The supported majors.
   */
  public function getSupportedMajors(): array {
    $supported_branches = $this->updateServerProjectInfo->getSupportBranches();
    $supported_majors = [];
    foreach ($supported_branches as $supported_branch) {
      $branch_version = ExtensionVersion::createFromSupportBranch($supported_branch);
      $supported_majors[] = $branch_version->getMajorVersion();
    }
    return array_unique($supported_majors);
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
      // older than what we've got.
      if ($this->projectData['install_type'] === 'dev') {
        if ($this->isReleaseDateLessThanProjectDate($release)) {
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
   * @param string|null $major
   *   (optional) The major version to return releases for.
   *
   * @return array[]
   *   The releases.
   *
   * @todo Right now this returns the current version regardless of whether this
   *   passes ::releaseIsInstallable(). This is because currently
   *   update_calculate_project_update_status() will consider the current
   *   version regardless of whether it passes this condition.
   */
  public function getInstallableReleases(string $major = NULL): array {
    $major_key = $major ?? 'all';
    if (isset($this->installableReleases[$major_key])) {
      return $this->installableReleases[$major_key];
    }
    $installable_releases = [];
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
        $extension_version = ExtensionVersion::createFromVersionString($release->getVersion());
      }
      catch (\UnexpectedValueException $exception) {
        continue;
      }

      if ((!$major || $extension_version->getMajorVersion() === $major) && ($this->releaseIsInstallable($release) || $version === $this->projectData['existing_version'])) {
        $installable_releases[$version] = $release_info;
      }
      if ($version === $this->projectData['existing_version']) {
        break;
      }
    }
    $this->installableReleases[$major_key] = $installable_releases;
    return $this->installableReleases[$major_key];
  }

  /**
   * Determines if we can recommend any release in the project.
   *
   * @return bool
   *   TRUE if the project can have any release recommended, otherwise false.
   */
  private function isProjectRecommendable(): bool {
    $unusable_project_statuses = [
      'insecure',
      'unpublished',
      'revoked',
      'unsupported',
      'not-fetched',
    ];
    if (in_array($this->updateServerProjectInfo->getStatus(), $unusable_project_statuses, TRUE)) {
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
   * @todo Maybe, unify this with the logic in
   *   update_calculate_project_update_status() determines status from the
   *   $available['project_status']. This will be tricky because the status from
   *   there could mean we don't go look at actual releases. The means for
   *   example we need to know if UpdateManagerInterface::NOT_SECURE came from
   *   $available['project_status'].
   *
   * @return int|null
   *   The status of the project if it can be determined, otherwise, NULL. The
   *   status will either of the constants on
   *   \Drupal\update\UpdateManagerInterface or
   *   \Drupal\update\UpdateFetcherInterface.
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
        $releases = $this->getInstallableReleases($this->getTargetMajor());
        $latest_release_info = array_shift($releases);
        if ($existing_release_info && ($existing_release_info['version'] === $recommended_release_info['version'] || $existing_release_info['version'] === $latest_release_info['version'])) {
          $status = UpdateManagerInterface::CURRENT;
        }
        else {
          $status = UpdateManagerInterface::NOT_CURRENT;
        }
        break;

      case 'dev':
        $latest_dev_release = ProjectRelease::createFromArray($this->getLatestDevForMajor());
        if (empty($this->projectData['datestamp'])) {
          $status = UpdateFetcherInterface::NOT_CHECKED;
        }
        elseif ($this->isReleaseDateLessThanProjectDate($latest_dev_release)) {
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

  /**
   * Gets the existing release, if any.
   *
   * @return array|null
   *   The existing release if available, otherwise NULL.
   */
  private function getExistingRelease(): ?array {
    if (isset($this->projectData['existing_version'])) {
      $releases = $this->updateServerProjectInfo->getReleases();
      return $releases[$this->projectData['existing_version']] ?? NULL;
    }
    return NULL;
  }

  /**
   * Gets the latest development release for a major.
   *
   * @param string|null $major
   *   (optional) The major version to use. Defaults to NULL. If no major
   *   version is specified the target major will be used.
   *
   * @return array|null
   *   The latest develop release if available otherwise NULL.
   */
  public function getLatestDevForMajor(string $major = NULL): ?array {
    $dev_release_info = $this->getLatestDevReleaseForMajor($major);
    $releases = $this->getInstallableReleases($major);
    $latest_release_info = array_shift($releases);
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

  /**
   * Determines if a release's date is less than the project date.
   *
   * @param \Drupal\update\ProjectRelease $release
   *   The release.
   *
   * @return bool
   *   TRUE if the release date is less than the project date, otherwise FALSE.
   */
  private function isReleaseDateLessThanProjectDate(ProjectRelease $release): bool {
    // Allow 100 seconds of leeway to handle differences between the datestamp
    // in the .info.yml file and the timestamp of the tarball itself (which are
    // usually off by 1 or 2 seconds) so that we don't flag that as a new
    // release.
    return empty($this->projectData['datestamp']) || ($release->getDate() && $this->projectData['datestamp'] + 100 > $release->getDate());
  }

}
