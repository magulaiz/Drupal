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
   * The earliest Drupal version that supports 'composer.json' for dependencies.
   *
   * @todo unused constant.
   */
  const FIRST_COMPOSER_JSON_SUPPORTED_VERSION = '9.1.0';

  /**
   * The key where you'll find Composer dependencies in *.info.yml files.
   */
  const COMPOSER_DEPENDENCIES = 'composer_dependencies';

  /**
   * InfoParserDynamic constructor.
   *
   * @param string $root
   *   The root directory of the Drupal installation.
   */
  public function __construct(protected string $root) {
  }

  /**
   * {@inheritdoc}
   */
  public function parse($filename) {
    if (!file_exists($filename)) {
      throw new InfoParserException("Unable to parse $filename as it does not exist");
    }

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
    $composer_filename = dirname($filename) . '/composer.json';
    $has_composer_file = file_exists($composer_filename);
    $is_testing_module = isset($parsed_info['package']) && $parsed_info['package'] === 'Testing';
    if (!isset($parsed_info['core']) && !isset($parsed_info['core_version_requirement'])) {
      if ((strpos($filename, 'core/') === 0 || strpos($filename, $this->root . '/core/') === 0)) {
        if (!($has_composer_file && $is_testing_module)) {
          // Core extensions do not need to specify core compatibility: they
          // are by definition compatible so a sensible default is used. Core
          // modules are allowed to provide these for testing purposes.
          $parsed_info['core_version_requirement'] = \Drupal::VERSION;
        }
      }
      elseif ($is_testing_module) {
        // Modules in the testing package are exempt as well. This makes it
        // easier for contrib to use test modules.
        $parsed_info['core_version_requirement'] = \Drupal::VERSION;
      }
      else {
        // @todo recheck the logic here after https://git.drupalcode.org/project/drupal/-/commit/718fa096fd1cab5a05543b67b1cd177d8e9dc769.
        // Non-core extensions must specify core compatibility.
        if (isset($parsed_info['dependencies'])) {
          throw new InfoParserException("If the 'dependencies' key is used, the 'core' or 'core_version_requirement' key is required in $filename");
        }
        elseif (!$has_composer_file) {
          throw new InfoParserException("If the 'core' or 'core_version_requirement' key is not provided, a composer.json file is required in $filename");
        }
        throw new InfoParserException("The 'core_version_requirement' key must be present in " . $filename);
      }

      // @todo recheck the logic here after https://git.drupalcode.org/project/drupal/-/commit/718fa096fd1cab5a05543b67b1cd177d8e9dc769.
      if ($has_composer_file) {
        $parsed_info += $this->parseComposerFile($composer_filename);
      }

      if (isset($parsed_info['core']) && !preg_match("/^\d\.x$/", $parsed_info['core'])) {
        throw new InfoParserException("Invalid 'core' value \"{$parsed_info['core']}\" in " . $filename);
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
   * Parses a composer.json file and checks for core_version_requirement.
   *
   * @param string $file_path
   *   Full path to the composer.json file.
   *
   * @return array
   *   Parsed composer.json file data.
   *
   * @throws \Drupal\Core\Extension\InfoParserException
   *   Thrown when the file cannot be parsed, or when the parsed require key
   *   does not include the drupal/core package.
   */
  protected function parseComposerFile($file_path) {
    if (!$parsed_info = json_decode(file_get_contents($file_path), TRUE)) {
      throw new InfoParserException("Unable to parse $file_path " . json_last_error_msg());
    }

    $require = $parsed_info['require'];
    foreach ($require as $project => $constraint) {
      [$namespace, $name] = explode('/', $project);
      if ($namespace !== 'drupal') {
        continue;
      }
      if ($name === 'core') {
        $parsed_info['core_version_requirement'] = $constraint;
        continue;
      }
      $parsed_info[static::COMPOSER_DEPENDENCIES][$name] = $constraint;
    }
    if (empty($parsed_info['core_version_requirement'])) {
      throw new InfoParserException("The 'require' key must at least specify a 'drupal/core' version in $file_path");
    }
    return $parsed_info;
  }

}
