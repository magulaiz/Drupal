<?php

namespace Drupal\Core\Extension;

use Composer\Semver\Semver;
use Drupal\Component\Serialization\Exception\InvalidDataTypeException;
use Drupal\Core\Serialization\Yaml;

/**
 * Parses dynamic .info.yml files that might change during the page request.
 */
class InfoParserDynamic implements InfoParserInterface {

  /**
   * The root directory of the Drupal installation.
   *
   * @var string
   */
  protected $root;

  /**
   * InfoParserDynamic constructor.
   *
   * @param string|null $app_root
   *   The root directory of the Drupal installation.
   */
  public function __construct(string $app_root = NULL) {
    if ($app_root === NULL) {
      // @todo https://www.drupal.org/project/drupal/issues/3087975 Require
      //   $app_root argument.
      $app_root = \Drupal::hasService('kernel') ? \Drupal::root() : DRUPAL_ROOT;
    }
    $this->root = $app_root;
  }

  /**
   * {@inheritdoc}
   */
  public function parse($filename) {
    if (!file_exists($filename)) {
      $parsed_info = [];
    }
    else {
      try {
        $parsed_info = Yaml::decode(file_get_contents($filename));
      }
      catch (InvalidDataTypeException $e) {
        throw new InfoParserException("Unable to parse $filename " . $e->getMessage());
      }
      $missing_keys = array_diff($this->getRequiredKeys(), array_keys($parsed_info));
      if (!empty($missing_keys)) {
        throw new InfoParserException('Missing required keys (' . implode(', ', $missing_keys) . ') in ' . $filename);
      }
      if (!isset($parsed_info['core_version_requirement'])) {
        if (strpos($filename, 'core/') === 0 || strpos($filename, $this->root . '/core/') === 0) {
          // Core extensions do not need to specify core compatibility: they are
          // by definition compatible so a sensible default is used. Core
          // modules are allowed to provide these for testing purposes.
          $parsed_info['core_version_requirement'] = \Drupal::VERSION;
        }
        elseif (isset($parsed_info['package']) && $parsed_info['package'] === 'Testing') {
          // Modules in the testing package are exempt as well. This makes it
          // easier for contrib to use test modules.
          $parsed_info['core_version_requirement'] = \Drupal::VERSION;
        }
        else {
          // Non-core extensions must specify core compatibility.
          throw new InfoParserException("The 'core_version_requirement' key must be present in " . $filename);
        }
      }

      // Determine if the extension is compatible with the current version of
      // Drupal core.
      try {
        $parsed_info['core_incompatible'] = !Semver::satisfies(\Drupal::VERSION, $parsed_info['core_version_requirement']);
      }
      catch (\UnexpectedValueException $exception) {
        throw new InfoParserException("The 'core_version_requirement' constraint ({$parsed_info['core_version_requirement']}) is not a valid value in $filename");
      }
      if (isset($parsed_info['version']) && $parsed_info['version'] === 'VERSION') {
        $parsed_info['version'] = \Drupal::VERSION;
      }
      $parsed_info += [ExtensionLifecycle::LIFECYCLE_IDENTIFIER => ExtensionLifecycle::STABLE];
      $lifecycle = $parsed_info[ExtensionLifecycle::LIFECYCLE_IDENTIFIER];
      if (!ExtensionLifecycle::isValid($lifecycle)) {
        $valid_values = [
          ExtensionLifecycle::EXPERIMENTAL,
          ExtensionLifecycle::STABLE,
          ExtensionLifecycle::DEPRECATED,
          ExtensionLifecycle::OBSOLETE,
        ];
        throw new InfoParserException("'lifecycle: {$lifecycle}' is not valid in $filename. Valid values are: '" . implode("', '", $valid_values) . "'.");
      }
      if (in_array($lifecycle, [ExtensionLifecycle::DEPRECATED, ExtensionLifecycle::OBSOLETE], TRUE)) {
        if (empty($parsed_info[ExtensionLifecycle::LIFECYCLE_LINK_IDENTIFIER])) {
          throw new InfoParserException(sprintf("Extension %s (%s) has 'lifecycle: %s' but is missing a '%s' entry.", $parsed_info['name'], $filename, $lifecycle, ExtensionLifecycle::LIFECYCLE_LINK_IDENTIFIER));
        }
        if (!filter_var($parsed_info[ExtensionLifecycle::LIFECYCLE_LINK_IDENTIFIER], FILTER_VALIDATE_URL)) {
          throw new InfoParserException(sprintf("Extension %s (%s) has a '%s' entry that is not a valid URL.", $parsed_info['name'], $filename, ExtensionLifecycle::LIFECYCLE_LINK_IDENTIFIER));
        }
      }
    }
    return $parsed_info;
  }

  /**
   * Returns an array of keys required to exist in .info.yml file.
   *
   * @return array
   *   An array of required keys.
   */
  protected function getRequiredKeys() {
    return ['type', 'name'];
  }

  /**
   * Gets all the versions of Drupal 8 before a specific version.
   *
   * @param string $version
   *   The version to get versions before.
   *
   * @return array
   *   All of the applicable Drupal 8 releases.
   */
  protected static function getAllPreviousCoreVersions($version) {
    static $versions_lists = [];
    // Check if list of previous versions for the specified version has already
    // been created.
    if (empty($versions_lists[$version])) {
      // Loop through all minor versions including 8.7.
      foreach (range(0, 7) as $minor) {
        // The largest patch number in a release was 17 in 8.6.17. Use 27 to
        // leave room for future security releases.
        foreach (range(0, 27) as $patch) {
          $patch_version = "8.$minor.$patch";
          if ($patch_version === $version) {
            // Reverse the order of the versions so that they will be evaluated
            // from the most recent versions first.
            $versions_lists[$version] = array_reverse($versions_lists[$version]);
            return $versions_lists[$version];
          }
          if ($patch === 0) {
            // If this is a '0' patch release like '8.1.0' first create the
            // pre-release versions such as '8.1.0-alpha1' and '8.1.0-rc1'.
            foreach (['alpha', 'beta', 'rc'] as $prerelease) {
              // The largest prerelease number was  in 8.0.0-beta16.
              foreach (range(0, 16) as $prerelease_number) {
                $versions_lists[$version][] = "$patch_version-$prerelease$prerelease_number";
              }
            }
          }
          $versions_lists[$version][] = $patch_version;
        }
      }
    }
    return $versions_lists[$version];
  }

}
