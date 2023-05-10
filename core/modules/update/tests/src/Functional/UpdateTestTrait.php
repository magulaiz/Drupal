<?php

namespace Drupal\Tests\update\Functional;

/**
 * Provides a trait to set system info and XML mappings.
 *
 * @see update_test_system_info_alter
 * @see \Drupal\update_test\Controller\UpdateTestController::updateTest
 */
trait UpdateTestTrait {

  /**
   * Sets information about installed extensions.
   *
   * @param string[][] $installed_extensions
   *   An array containing mocked installed extensions info. Keys are
   *   extension names, values are arrays containing key-value pairs that would
   *   be present in extensions' *.info.yml files.
   *   For a list of accepted keys, see InfoParserInterface. Key-value pairs not
   *   present here will be inherited from $default_info.
   *   For example:
   *   @code
   *   'drupal' => [
   *     'project' => 'drupal',
   *     'version' => '8.0.0',
   *     'hidden' => FALSE,
   *   ]
   *   @endcode
   * @param string[] $default_info
   *   (optional) The *.info.yml key-value pairs to be mocked across all
   *   extensions. Hence, these can be seen as default/fallback values.
   *
   * @see \Drupal\Core\Extension\InfoParserInterface
   * @see update_test_system_info_alter()
   * @see \Drupal\Core\Extension\ExtensionList::doList()
   */
  public function mockInstalledExtensions(array $installed_extensions, array $default_info = []): void {
    if (!empty($default_info)) {
      $installed_extensions = array_merge(['#all' => $default_info], $installed_extensions);
    }
    $this->config('update_test.settings')->set('system_info', $installed_extensions)->save();
  }

  /**
   * Sets available release metadata.
   *
   * @param string[] $release_metadata
   *   The available release metadata. In the format as the key to be the
   *   extension name and available release as its value,
   *   for example:
   *   @code 'drupal' => 'sec.0.2' @endcode , which matches the release history
   *   xml file named drupal.sec.0.2.xml.
   *
   * @see \Drupal\update_test\Controller\UpdateTestController::updateTest
   */
  public function setAvailableReleasesMetadata(array $release_metadata): void {
    $this->config('update_test.settings')->set('xml_map', $release_metadata)->save();
  }

}
