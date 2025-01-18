<?php

declare(strict_types=1);

namespace Drupal\Tests\locale\Functional;

/**
 * Tests installing in a different language with a dev version string.
 *
 * @group locale
 */
class LocaleNonInteractiveDevInstallTest extends LocaleNonInteractiveInstallTest {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function getVersionStringToTest(): string {
    // Split the Drupal version string into parts.
    $version_parts = explode('.', \Drupal::VERSION);
    $major = $version_parts[0];
    $minor = $version_parts[1] ?? '0';
    // Return the major and minor version followed by '.x'.
    return $major . '.' . $minor . '.x';
  }

}
