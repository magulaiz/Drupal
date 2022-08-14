<?php

/**
 * @file
 * Code required only when comparing available updates to existing data.
 */

use Drupal\Core\Extension\ExtensionVersion;
use Drupal\update\ProjectStatusCalculator;
use Drupal\update\UpdateFetcherInterface;
use Drupal\update\UpdateManagerInterface;
use Drupal\update\ProjectCoreCompatibility;
use Drupal\update\UpdateServerProjectInfo;

/**
 * Determines version and type information for currently installed projects.
 *
 * Processes the list of projects on the system to figure out the currently
 * installed versions, and other information that is required before we can
 * compare against the available releases to produce the status report.
 *
 * @param $projects
 *   Array of project information from
 *   \Drupal\update\UpdateManager::getProjects().
 */
function update_process_project_info(&$projects) {
  foreach ($projects as $key => $project) {
    // Assume an official release until we see otherwise.
    $install_type = 'official';

    $info = $project['info'];

    if (isset($info['version'])) {
      // Check for development snapshots
      if (preg_match('@(dev|HEAD)@', $info['version'])) {
        $install_type = 'dev';
      }

      // Figure out what the currently installed major version is. We need
      // to handle both contribution (e.g. "5.x-1.3", major = 1) and core
      // (e.g. "5.1", major = 5) version strings.
      $matches = [];
      if (preg_match('/^(\d+\.x-)?(\d+)\..*$/', $info['version'], $matches)) {
        $info['major'] = $matches[2];
      }
      else {
        // This would only happen for version strings that don't follow the
        // drupal.org convention.
        $info['major'] = -1;
      }
    }
    else {
      // No version info available at all.
      $install_type = 'unknown';
      $info['version'] = t('Unknown');
      $info['major'] = -1;
    }

    // Finally, save the results we care about into the $projects array.
    $projects[$key]['existing_version'] = $info['version'];
    $projects[$key]['existing_major'] = $info['major'];
    $projects[$key]['install_type'] = $install_type;
  }
}

/**
 * Calculates the current update status of all projects on the site.
 *
 * The results of this function are expensive to compute, especially on sites
 * with lots of modules or themes, since it involves a lot of comparisons and
 * other operations. Therefore, we store the results. However, since this is not
 * the data about available updates fetched from the network, it is ok to
 * invalidate it somewhat quickly. If we keep this data for very long, site
 * administrators are more likely to see incorrect results if they upgrade to a
 * newer version of a module or theme but do not visit certain pages that
 * automatically clear this.
 *
 * @param array $available
 *   Data about available project releases.
 *
 * @return
 *   An array of installed projects with current update status information.
 *
 * @see update_get_available()
 * @see \Drupal\update\UpdateManager::getProjects()
 * @see update_process_project_info()
 * @see \Drupal\update\UpdateManagerInterface::projectStorage()
 * @see \Drupal\update\ProjectCoreCompatibility::setReleaseMessage()
 */
function update_calculate_project_data($available) {
  // Retrieve the projects from storage, if present.
  $projects = \Drupal::service('update.manager')->projectStorage('update_project_data');
  // If $projects is empty, then the data must be rebuilt.
  // Otherwise, return the data and skip the rest of the function.
  if (!empty($projects)) {
    return $projects;
  }
  $projects = \Drupal::service('update.manager')->getProjects();
  update_process_project_info($projects);
  if (isset($projects['drupal']) && !empty($available['drupal'])) {
    // Calculate core status first so that it is complete before
    // \Drupal\update\ProjectCoreCompatibility::setReleaseMessage() is called
    // for each module below.
    update_calculate_project_update_status($projects['drupal'], $available['drupal']);
    if (isset($available['drupal']['releases'])) {
      $project_core_compatibility = new ProjectCoreCompatibility($projects['drupal'], $available['drupal']['releases']);
    }
  }

  foreach ($projects as $project => $project_info) {
    if (isset($available[$project])) {
      if ($project === 'drupal') {
        continue;
      }
      update_calculate_project_update_status($projects[$project], $available[$project]);
      // Inject the list of compatible core versions to show administrator(s)
      // which versions of core a given available update can be installed with.
      // Since individual releases of a project can be compatible with different
      // versions of core, and even multiple major versions of core (for
      // example, 8.9.x and 9.0.x), this list will hopefully help
      // administrator(s) know which available updates they can upgrade a given
      // project to.
      if (isset($project_core_compatibility)) {
        $project_core_compatibility->setReleaseMessage($projects[$project]);
      }
    }
    else {
      $projects[$project]['status'] = UpdateFetcherInterface::UNKNOWN;
      $projects[$project]['reason'] = t('No available releases found');
    }
  }
  // Give other modules a chance to alter the status (for example, to allow a
  // contrib module to provide fine-grained settings to ignore specific
  // projects or releases).
  \Drupal::moduleHandler()->alter('update_status', $projects);

  // Store the site's update status for at most 1 hour.
  \Drupal::keyValueExpirable('update')->setWithExpire('update_project_data', $projects, 3600);
  return $projects;
}

/**
 * Calculates the current update status of a specific project.
 *
 * This function is the heart of the update status feature. For each project it
 * is invoked with, it first checks if the project has been flagged with a
 * special status like "unsupported" or "insecure", or if the project node
 * itself has been unpublished. In any of those cases, the project is marked
 * with an error and the next project is considered.
 *
 * If the project itself is valid, the function decides what major release
 * series to consider. The project defines its currently supported branches in
 * its Drupal.org for the project, so the first step is to make sure the
 * development branch of the current version is still supported. If so, then the
 * major version of the current version is used. If the current version is not
 * in a supported branch, the next supported branch is used to determine the
 * major version to use. There's also a check to make sure that this function
 * never recommends an earlier release than the currently installed major
 * version.
 *
 * Given a target major version, the available releases are scanned looking for
 * the specific release to recommend (avoiding beta releases and development
 * snapshots if possible). For the target major version, the highest patch level
 * is found. If there is a release at that patch level with no extra ("beta",
 * etc.), then the release at that patch level with the most recent release date
 * is recommended. If every release at that patch level has extra (only betas),
 * then the latest release from the previous patch level is recommended. For
 * example:
 *
 * - 1.6-bugfix <-- recommended version because 1.6 already exists.
 * - 1.6
 *
 * or
 *
 * - 1.6-beta
 * - 1.5 <-- recommended version because no 1.6 exists.
 * - 1.4
 *
 * Also, the latest release from the same major version is looked for, even beta
 * releases, to display to the user as the "Latest version" option.
 * Additionally, the latest official release from any higher major versions that
 * have been released is searched for to provide a set of "Also available"
 * options.
 *
 * Finally, and most importantly, the release history continues to be scanned
 * until the currently installed release is reached, searching for anything
 * marked as a security update. If any security updates have been found between
 * the recommended release and the installed version, all of the releases that
 * included a security fix are recorded so that the site administrator can be
 * warned their site is insecure, and links pointing to the release notes for
 * each security update can be included (which, in turn, will link to the
 * official security announcements for each vulnerability).
 *
 * This function relies on the fact that the .xml release history data comes
 * sorted based on major version and patch level, then finally by release date
 * if there are multiple releases such as betas from the same major.patch
 * version (e.g., 5.x-1.5-beta1, 5.x-1.5-beta2, and 5.x-1.5). Development
 * snapshots for a given major version are always listed last.
 *
 * NOTE: This function *must* set a value for $project_data['status'] before
 * returning, or the rest of the Update Manager will break in unexpected ways.
 *
 * @param $project_data
 *   An array containing information about a specific project.
 * @param $available
 *   Data about available project releases of a specific project.
 */
function update_calculate_project_update_status(&$project_data, $available) {
  foreach (['title', 'link'] as $attribute) {
    if (!isset($project_data[$attribute]) && isset($available[$attribute])) {
      $project_data[$attribute] = $available[$attribute];
    }
  }

  // If the project status is marked as something bad, there's nothing else
  // to consider.
  $server_project_info = UpdateServerProjectInfo::createFromArray($available);
  $server_project_status = $server_project_info->getStatus();
  switch ($server_project_status) {
    case 'insecure':
      $project_data['status'] = UpdateManagerInterface::NOT_SECURE;
      if (empty($project_data['extra'])) {
        $project_data['extra'] = [];
      }
      $project_data['extra'][] = [
        'label' => t('Project not secure'),
        'data' => t('This project has been labeled insecure by the Drupal security team, and is no longer available for download. Immediately disabling everything included by this project is strongly recommended!'),
      ];
      break;

    case 'unpublished':
    case 'revoked':
      $project_data['status'] = UpdateManagerInterface::REVOKED;
      if (empty($project_data['extra'])) {
        $project_data['extra'] = [];
      }
      $project_data['extra'][] = [
        'label' => t('Project revoked'),
        'data' => t('This project has been revoked, and is no longer available for download. Disabling everything included by this project is strongly recommended!'),
      ];
      break;

    case 'unsupported':
      $project_data['status'] = UpdateManagerInterface::NOT_SUPPORTED;
      if (empty($project_data['extra'])) {
        $project_data['extra'] = [];
      }
      $project_data['extra'][] = [
        'label' => t('Project not supported'),
        'data' => t('This project is no longer supported, and is no longer available for download. Disabling everything included by this project is strongly recommended!'),
      ];
      break;

    case 'not-fetched':
      $project_data['status'] = UpdateFetcherInterface::NOT_FETCHED;
      $project_data['reason'] = t('Failed to get available update data.');
      break;

    default:
      // Assume anything else (e.g. 'published') is valid and we should
      // perform the rest of the logic in this function.
      break;
  }

  if (!empty($project_data['status'])) {
    // We already know the status for this project, so there's nothing else to
    // compute. Record the project status into $project_data and we're done.
    $project_data['project_status'] = $server_project_status;
    return;
  }

  // Figure out the target major version.
  // Off Drupal.org, '0' could be a valid version string, so don't use empty().
  if (!isset($project_data['existing_version']) || $project_data['existing_version'] === '') {
    $project_data['status'] = UpdateFetcherInterface::UNKNOWN;
    $project_data['reason'] = t('Empty version');
    return;
  }
  try {
    ExtensionVersion::createFromVersionString($project_data['existing_version']);
  }
  catch (UnexpectedValueException $exception) {
    // If the version has an unexpected value we can't determine updates.
    $project_data['status'] = UpdateFetcherInterface::UNKNOWN;
    $project_data['reason'] = t('Invalid version: @existing_version', ['@existing_version' => $project_data['existing_version']]);
    return;
  }

  // If the project is marked as UpdateFetcherInterface::FETCH_PENDING, it
  // means that the data we currently have (if any) is stale, and we've got a
  // task queued up to (re)fetch the data. In that case, we mark it as such,
  // merge in whatever data we have (e.g. project title and link), and move on.
  if (!empty($available['fetch_status']) && $available['fetch_status'] == UpdateFetcherInterface::FETCH_PENDING) {
    $project_data['status'] = UpdateFetcherInterface::FETCH_PENDING;
    $project_data['reason'] = t('No available update data');
    $project_data['fetch_status'] = $available['fetch_status'];
    return;
  }

  // Defend ourselves from XML history files that contain no releases.
  if (empty($available['releases'])) {
    $project_data['status'] = UpdateFetcherInterface::UNKNOWN;
    $project_data['reason'] = t('No available releases found');
    return;
  }

  $status_calculator = ProjectStatusCalculator::createFromProjectData($project_data, $server_project_info);
  if ($recommended_release = $status_calculator->getRecommendReleaseForTargetMajor()) {
    $project_data['recommended'] = $recommended_release['version'];
    $project_data['releases'][$recommended_release['version']] = $recommended_release;
  }
  $target_major = $status_calculator->getTargetMajor();
  $releases = $status_calculator->getInstallableReleases($target_major);
  if ($releases) {
    $latest_release = array_shift($releases);
    $project_data['latest_version'] = $latest_release['version'];
    $project_data['releases'][$latest_release['version']] = $latest_release;
  }
  if ($dev_release = $status_calculator->getLatestDevReleaseForMajor()) {
    $project_data['dev_version'] = $dev_release['version'];
    $project_data['releases'][$dev_release['version']] = $latest_release;
  }
  if ($security_releases = $status_calculator->getSecurityReleases()) {
    $project_data['security updates'] = array_values($security_releases);
  }

  foreach ($status_calculator->getSupportedMajors() as $supported_major) {
    if ($supported_major > $target_major) {
      $major_releases = $status_calculator->getInstallableReleases($supported_major);
      if ($major_releases) {
        $supported_major_release = array_shift($major_releases);
        $project_data['also'][$supported_major] = $supported_major_release['version'];
        $project_data['releases'][$supported_major_release['version']] = $supported_major_release;
      }
    }
  }

  $project_data['status'] = $status_calculator->getStatus();
  if ($project_data['status'] === UpdateManagerInterface::REVOKED) {
    if (empty($project_data['extra'])) {
      $project_data['extra'] = [];
    }
    $project_data['extra'][] = [
      'class' => ['release-revoked'],
      'label' => t('Release revoked'),
      'data' => t('Your currently installed release has been revoked, and is no longer available for download. Disabling everything included in this release or upgrading is strongly recommended!'),
    ];
  }
  elseif ($project_data['status'] === UpdateManagerInterface::NOT_SUPPORTED) {
    if (empty($project_data['extra'])) {
      $project_data['extra'] = [];
    }
    $project_data['extra'][] = [
      'class' => ['release-not-supported'],
      'label' => t('Release not supported'),
      'data' => t('Your currently installed release is now unsupported, and is no longer available for download. Disabling everything included in this release or upgrading is strongly recommended!'),
    ];
  }

  if (isset($project_data['status'])) {
    // If we already know the status, we're done.
    return;
  }

  // If we don't know what to recommend, there's nothing we can report.
  // Bail out early.
  if (!isset($project_data['recommended'])) {
    $project_data['reason'] = t('No available releases found');
    return;
  }

  // If we're running a dev snapshot record the absolute latest version in
  // 'latest_dev' so we can correctly decide if there's a newer release than our
  // current snapshot.
  if ($status_calculator->getInstallType() === 'dev' && $latest_dev_release_info = $status_calculator->getLatestDevForMajor()) {
    $project_data['latest_dev'] = $latest_dev_release_info['version'];
  }

  // Figure out the reason, based on what we've seen and the install type.
  $project_install_type = $status_calculator->getInstallType();
  if ($project_install_type === 'dev' && $project_data['status'] === UpdateFetcherInterface::NOT_CHECKED) {
    $project_data['reason'] = t('Unknown release date');
  }
  elseif ($project_install_type !== 'official') {
    $project_data['reason'] = t('Invalid info');
  }
}
