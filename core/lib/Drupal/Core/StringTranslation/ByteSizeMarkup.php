<?php

namespace Drupal\Core\StringTranslation;

use Drupal\Component\Utility\Bytes;

/**
 * A class to generate translatable markup for the given byte count.
 */
final class ByteSizeMarkup {

  /**
   * This class should not be instantiated.
   */
  private function __construct() {
  }

  /**
   * Gets the TranslatableMarkup object for the provided size.
   *
   * @return \Drupal\Core\StringTranslation\PluralTranslatableMarkup|\Drupal\Core\StringTranslation\TranslatableMarkup
   *   The translatable markup.
   */
  public static function create($size, string $langcode = NULL, TranslationInterface $stringTranslation = NULL): TranslatableMarkup {
    $options = ['langcode' => $langcode];
    $absolute_size = abs($size);
    if ($absolute_size < Bytes::KILOBYTE) {
      $markup = new PluralTranslatableMarkup($size, '1 byte', '@count bytes', [], $options, $stringTranslation);
    }
    else {
      // Create a multiplier to preserve the sign of $size.
      $sign = $absolute_size / $size;
      foreach (['KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'] as $unit) {
        $absolute_size /= Bytes::KILOBYTE;
        $rounded_size = round($absolute_size, 2);
        if ($rounded_size < Bytes::KILOBYTE) {
          break;
        }
      }

      $args = ['@size' => $rounded_size * $sign];
      switch ($unit) {
        case 'KB':
          $markup = new TranslatableMarkup('@size KB', $args, $options, $stringTranslation);
          break;

        case 'MB':
          $markup = new TranslatableMarkup('@size MB', $args, $options, $stringTranslation);
          break;

        case 'GB':
          $markup = new TranslatableMarkup('@size GB', $args, $options, $stringTranslation);
          break;

        case 'TB':
          $markup = new TranslatableMarkup('@size TB', $args, $options, $stringTranslation);
          break;

        case 'PB':
          $markup = new TranslatableMarkup('@size PB', $args, $options, $stringTranslation);
          break;

        case 'EB':
          $markup = new TranslatableMarkup('@size EB', $args, $options, $stringTranslation);
          break;

        case 'ZB':
          $markup = new TranslatableMarkup('@size ZB', $args, $options, $stringTranslation);
          break;

        case 'YB':
          $markup = new TranslatableMarkup('@size YB', $args, $options, $stringTranslation);
          break;

      }
    }
    // At this point $markup must be set.
    return $markup;
  }

}
