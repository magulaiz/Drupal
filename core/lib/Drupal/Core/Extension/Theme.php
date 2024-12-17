<?php

namespace Drupal\Core\Extension;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * The Theme extension object.
 *
 * @see \Drupal\Core\Extension\ThemeHandlerInterface::rebuildThemeData()
 *
 * @todo https://www.drupal.org/project/drupal/issues/3026232 Replace public
 *   properties with methods.
 */
class Theme extends Extension {

  /**
   * Whether the theme is installed or not.
   *
   * @var int
   */
  public int $status;

  /**
   * The info array based on the theme's .info.yml file.
   *
   * @var array
   */
  public array $info = [
    'engine' => 'twig',
    'regions' => [
      'sidebar_first' => 'Left sidebar',
      'sidebar_second' => 'Right sidebar',
      'content' => 'Content',
      'header' => 'Header',
      'primary_menu' => 'Primary menu',
      'secondary_menu' => 'Secondary menu',
      'footer' => 'Footer',
      'highlighted' => 'Highlighted',
      'help' => 'Help',
      'page_top' => 'Page top',
      'page_bottom' => 'Page bottom',
      'breadcrumb' => 'Breadcrumb',
    ],
    'description' => '',
    // The following array should be kept inline with
    // _system_default_theme_features().
    'features' => [
      'favicon',
      'logo',
      'node_user_picture',
      'comment_user_picture',
      'comment_user_verification',
    ],
    'screenshot' => 'screenshot.png',
    'version' => NULL,
    'php' => \Drupal::MINIMUM_PHP,
    'libraries' => [],
    'libraries_extend' => [],
    'libraries_override' => [],
    'dependencies' => [],
  ];

  /**
   * The relative path to the theme engine extension file.
   *
   * @var string
   */
  public string $owner;

  /**
   * The internal name of the theme engine extension.
   *
   * @var string
   */
  public string $prefix;

  /**
   * Constructs a new Theme object.
   *
   * @param string $root
   *   The app root.
   * @param string $pathname
   *   The relative path and filename of the extension's info file; e.g.,
   *   'core/modules/node/node.info.yml'.
   * @param array $info
   *   The info array based on the theme's .info.yml file.
   * @param int $status
   *   The theme's installation status.
   * @param string|null $filename
   *   (optional) The filename of the main extension file; e.g., 'node.module'.
   */
  public function __construct(string $root, string $pathname, array $info, int $status, string $filename = NULL) {
    parent::__construct($root, 'theme', $pathname, $filename);
    $this->info = $info + $this->info;
    if (!isset($info['base theme'])) {
      throw new InfoParserException(sprintf('Missing required key ("base theme") in %s, see https://www.drupal.org/node/3066038', $pathname));
    }
    // Remove the default Stable base theme when 'base theme: false' is set in
    // a theme .info.yml file.
    if ($this->info['base theme'] === FALSE) {
      unset($this->info['base theme']);
    }
    else {
      // Add the base theme as a proper dependency.
      $this->info['dependencies'][] = $this->info['base theme'];
    }

    // Add the info file modification time, so it becomes available for
    // contributed modules to use for ordering theme lists.
    $this->info['mtime'] = $this->getFileInfo()->getMTime();

    $this->status = $status;
  }

  /**
   * Lists all the theme's regions.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup[]
   *   An array of regions in the form $region['name'] = 'description'.
   */
  public function listAllRegions(): array {
    return array_map(static function ($label) {
      return new TranslatableMarkup($label);
    }, $this->info['regions']);
  }

  /**
   * Lists all the theme's visible regions.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup[]
   *   An array of regions in the form $region['name'] = 'description'.
   */
  public function listVisibleRegions(): array {
    // List only regions that do not appear in the 'regions_hidden' key.
    return array_diff_key(
      $this->listAllRegions(),
      array_flip($this->info['regions_hidden'])
    );
  }

  /**
   * Gets the name of the default region for the theme.
   *
   * @return string
   *   A string that is the region name.
   */
  public function getDefaultRegion(): string {
    return (string) key($this->listVisibleRegions());
  }

}
