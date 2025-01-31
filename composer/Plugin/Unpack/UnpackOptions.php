<?php

namespace Drupal\Composer\Plugin\Unpack;

/**
 * Per-project options from the 'extras' section of the composer.json file.
 *
 * Projects that implement dependency unpacking plugin can further configure it.
 * This data is pulled from the 'drupal-unpack' portion of the extras section.
 *
 * @code
 *  "extras": {
 *    "drupal-unpack": {
 *      "remove-self": true,
 *      "ignore": ["drupal/core"]
 *    }
 *  }
 * @endcode
 *
 * Supported options:
 * - `remove-self` (boolean):
 *   Specifies whether to remove the package being unpacked from root composer.
 * - `ignore` (array):
 *   Specifies packages to exclude from unpacking into the root composer.json.
 *
 * @internal
 */
final class UnpackOptions {

  /**
   * The ID of the extra section in the top-level composer.json file.
   *
   * @var string
   */
  const ID = 'drupal-unpack';

  /**
   * The raw data from the 'extras' section of the top-level composer.json file.
   *
   * @var array
   */
  public readonly array $options;

  private function __construct(array $options) {
    $this->options = $options + [
      'remove-self' => TRUE,
      'ignore' => [],
    ];
  }

  /**
   * Checks if a package should be ignored.
   *
   * @param string $name
   *   The package name.
   *
   * @return bool
   *   True if the package should be ignored.
   */
  public function ignorePackage(string $name): bool {
    return in_array($name, $this->options['ignore']);
  }

  /**
   * Creates an unpack options object.
   *
   * @param array $extras
   *   The contents of the 'extras' section.
   *
   * @return self
   *   The unpack options object representing the provided unpack options
   */
  public static function create(array $extras): self {
    $options = $extras[self::ID] ?? [];
    return new self($options);
  }

}
