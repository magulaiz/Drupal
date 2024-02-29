<?php

declare(strict_types=1);

namespace Drupal\filter;

enum FilterType {
  case MarkupLanguage;
  case HtmlRestrictor;
  case TransformReversible;
  case TransformIrreversible;

  /**
   * Backwards compatibility with legacy integer values.
   *
   * @param int $legacyInt
   *   The legacy integer value from \Drupal\filter\Plugin\FilterInterface.
   */
  public static function fromLegacyInt(int $legacyInt): self {
    // @todo trigger deprecation warning.
    return match ($legacyInt) {
      0 => self::MarkupLanguage,
      1 => self::HtmlRestrictor,
      2 => self::TransformReversible,
      3 => self::TransformIrreversible,
      default => throw new \InvalidArgumentException("Invalid legacy integer value: $legacyInt"),
    };
  }

}
