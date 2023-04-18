<?php

namespace Drupal\Core\Theme;

use Drupal\Component\Serialization\Exception\InvalidDataTypeException;
use Drupal\Core\Serialization\Yaml;

/**
 * Parses an optional theme THEME.colors.yml file.
 *
 * This provides a generic method for retrieving information about which colors
 * can be changed in a theme. Extensions can use this information to build a
 * user interface. If the theme uses CSS variables for color definitions, such
 * extensions can easily inject (conditionally) changed colors with below code:
 * @code
 * function HOOK_preprocess_html(&$variables) {
 *   // Overrides the colors for CSS variable '--color--primary-color' and
 *   // '--color--secondary-color'.
 *   $variables['html_attributes']
 *     ->setAttribute('style', "--color--primary-color:$primary;--color--secondary-color:$secondary");
 * }
 * @endcode
 */
class ThemeColorsParser {

  /**
   * Parses optional Drupal theme THEME.colors.yml files.
   *
   * Information stored in a theme THEME.colors.yml file:
   * - colors: List of CSS variable names and their human names.
   * - schemes: Color schemes; An array of color schemes keyed by their scheme name.
   *
   * See olivero.colors.yml for an example of a theme THEME.colors.yml file.
   *
   * @param string $filename
   *   The file we are parsing. Accepts file with relative or absolute path.
   *
   * @return array
   *   The colors definitions array with a list of colors and schemes.
   *
   * @throws \Drupal\Core\Extension\InfoParserException
   *   Exception thrown if there is a parsing error.
   */
  public static function parse(string $filename): array {
    $defaults = [
      'colors' => [],
      'schemes' => [],
    ];

    $basename = pathinfo($filename)['basename'];

    if (!file_exists($filename) || !str_ends_with($basename, 'colors.yml')) {
      return $defaults;
    }
    try {
      $parsed_info = Yaml::decode(file_get_contents($filename)) + $defaults;
    }
    catch (InvalidDataTypeException $e) {
      throw new \RuntimeException("Unable to parse $filename " . $e->getMessage());
    }
    // Check if the colors map contains at least one configurable color.
    if (empty($parsed_info['colors'])) {
      throw new \RuntimeException('At least one configurable color is required.');
    }
    // Check if all scheme colors have a corresponding configurable color.
    foreach ($parsed_info['schemes'] as $scheme => $content) {
      if (!empty(array_diff_key($parsed_info['colors'], $content['colors']))) {
        throw new \RuntimeException("The colors in scheme '$scheme' do not match with the configurable colors.");
      }
    }
    // Check if all scheme colors have a correct color format.
    foreach ($parsed_info['colors'] as $color_field => $color_name) {
      if (!ThemeColorsParser::validate($color_field)) {
        throw new \RuntimeException("The colors in scheme '$scheme' must be 7-character string specifying a color hexadecimal format.");
      }
    }
    return $parsed_info;
  }

  /**
   * Validates color value.
   *
   * @param string $colorValue
   *   Color value as a string.
   *
   * @return bool
   *   True if validates against color pattern.
   */
  public static function validate(string $colorValue): bool {
    return preg_match('/^#[a-fA-F0-9]{6}$/', $colorValue);
  }

}
